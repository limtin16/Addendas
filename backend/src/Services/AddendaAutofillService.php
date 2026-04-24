<?php

namespace App\Services;

require_once dirname(__DIR__) . '/Services/CFDIParserService.php';
require_once dirname(__DIR__) . '/Services/CfdiValueResolver.php';

use App\Services\CFDIParserService;
use App\Services\CfdiValueResolver;
use DOMDocument;
use DOMXPath;

class AddendaAutofillService
{
    public function fill(string $addendaXml, string $cfdiXml, array $structure): string
    {
        $parser = new CFDIParserService();
        $cfdiData = $parser->parse($cfdiXml);
        $resolver = new CfdiValueResolver($cfdiData);

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($addendaXml);
        $xpath = new DOMXPath($doc);

        foreach ($structure['root']['children'] as $node) {
            if ($node['type'] === 'field') {
                $this->fillSimpleField($doc, $node, $resolver);
            }

            if ($node['type'] === 'group') {
                $this->fillGroup($doc, $xpath, $node, $resolver);
            }
        }

        $xml = $doc->saveXML($doc->documentElement);
        return preg_replace('/\{\{[^}]+\}\}/', '', $xml);
    }

    /* =====================================================
       ROOT FIELDS
       ===================================================== */
    private function fillSimpleField(
        DOMDocument $doc,
        array $field,
        CfdiValueResolver $resolver
    ): void {

        $value = null;

        if (isset($field['value'])) {
            $value = $field['value'];
        }
        elseif (isset($field['source'])) {
            $value = $resolver->resolve($field['source']);
        }
        elseif (isset($field['calculation'])) {
            $value = $this->evaluateCalculation(
                $field['calculation'],
                $resolver,
                null
            );
        }

        if ($value === null || $value === '') {
            return;
        }

        $doc->documentElement->setAttribute(
            $field['name'],
            (string)$value
        );
    }

    /* =====================================================
       GROUPS
       ===================================================== */
    private function fillGroup(
        DOMDocument $doc,
        DOMXPath $xpath,
        array $group,
        CfdiValueResolver $resolver
    ): void {

        $conceptos = $resolver->getConceptos();
        if (empty($conceptos)) {
            return;
        }

        foreach ($xpath->query("//*[local-name()='{$group['name']}']") as $groupEl) {

            while ($groupEl->firstChild) {
                $groupEl->removeChild($groupEl->firstChild);
            }

            foreach ($conceptos as $index => $concepto) {

                $item = $doc->createElementNS(
                    $groupEl->namespaceURI,
                    $groupEl->prefix . ':' . $group['itemName']
                );

                foreach ($group['children'] as $field) {

                    $value = null;

                    if (isset($field['value'])) {
                        $value = $field['value'];
                    }
                    elseif (isset($field['source'])) {
                        $value = $resolver->resolve(
                            $field['source'],
                            $concepto,
                            $index
                        );
                    }
                    elseif (isset($field['calculation'])) {
                        $value = $this->evaluateCalculation(
                            $field['calculation'],
                            $resolver,
                            $concepto
                        );
                    }

                    if ($value !== null && $value !== '') {
                        $item->setAttribute(
                            $field['name'],
                            (string)$value
                        );
                    }
                }

                $groupEl->appendChild($item);
            }
        }
    }

    /* =====================================================
       SAFE CALCULATION ENGINE
       ===================================================== */
    private function evaluateCalculation(
        string $expression,
        CfdiValueResolver $resolver,
        ?array $concepto
    ): float {

        preg_match_all('/[a-zA-Z_][a-zA-Z0-9_]*/', $expression, $matches);
        $variables = array_unique($matches[0]);

        $resolved = [];

        foreach ($variables as $var) {
            $value = $resolver->resolve(
                'cfdi.' . strtolower($var),
                $concepto
            );

            if ($value === null) {
                throw new \RuntimeException(
                    "No se pudo resolver '{$var}' en cálculo"
                );
            }

            if (!is_numeric($value)) {
                throw new \RuntimeException(
                    "La variable '{$var}' no es numérica"
                );
            }

            $resolved[$var] = (float) $value;
        }

        $safeExpr = $expression;
        foreach ($resolved as $var => $num) {
            $safeExpr = preg_replace(
                '/\b' . preg_quote($var, '/') . '\b/',
                $num,
                $safeExpr
            );
        }

        if (!preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $safeExpr)) {
            throw new \RuntimeException('Expresión de cálculo inválida');
        }

        try {
            $result = eval('return ' . $safeExpr . ';');
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error evaluando cálculo');
        }

        return round((float)$result, 2);
    }
}