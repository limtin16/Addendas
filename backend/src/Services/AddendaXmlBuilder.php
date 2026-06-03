<?php

namespace App\Services;

use DOMDocument;
use DOMElement;

class AddendaXmlBuilder
{
    public function build(array $structure): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // =======================================
        // ✅ MODO XSD (detectar correctamente)
        // =======================================
        if (
            isset($structure['children']) &&
            isset($structure['children'][0]['type']) &&
            $structure['children'][0]['type'] === 'node'
        ) {
            // ✅ CONTENIDO XSD (root real)
            $rootNode = $structure['children'][0];
            $rootElement = $this->buildNodeFromXsd($doc, $rootNode);

            // ✅ usarlo como root del documento
            $doc->appendChild($rootElement);

            return $doc->saveXML($doc->documentElement);
        }

        // =======================================
        // ✅ MODO LEGACY (wizard)
        // =======================================
        if (!isset($structure['root'])) {
            throw new \InvalidArgumentException('Estructura inválida');
        }

        $rootDef = $structure['root'];

        $name      = $rootDef['name'];
        $prefix    = $rootDef['prefix'];
        $namespace = $rootDef['namespace'];
        $children  = $rootDef['children'] ?? [];

       $qualifiedName = $prefix
        ? $prefix . ':' . $name
        : $name;

        $root = $doc->createElementNS(
            $namespace ?: null,
            $qualifiedName
        );

        // ✅ AHORA SÍ LO AGREGAS
        $doc->appendChild($root);

        foreach ($children as $child) {
            $this->buildNode($doc, $root, $child, $prefix, $namespace);
        }

        return $doc->saveXML($doc->documentElement);
    }

    private function cleanName(string $name): string
{
    // Si viene con prefix tipo ADD:Factura → dejar solo Factura
    if (strpos($name, ':') !== false) {
        return explode(':', $name, 2)[1];
    }

    return $name;
}


    // =======================================
    // ✅ XSD BUILDER
    // =======================================
    private function buildNodeFromXsd(DOMDocument $doc, array $node): DOMElement
    {
        $name = $this->cleanName($node['name']);
        $namespace = $node['namespace'] ?? null;

        if ($namespace) {
            $name = $this->cleanName($node['name']);
            $prefix = $node['prefix'] ?? '';
            $namespace = $node['namespace'] ?? null;

            $qualifiedName = $prefix
                ? $prefix . ':' . $name
                : $name;

            $element = $doc->createElementNS(
                $namespace ?: null,
                $qualifiedName
            );
        } else {
            $element = $doc->createElement($name);
        }

        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {

                if ($child['type'] === 'field') {
                    $element->setAttribute($this->cleanName($child['name']), '');
                }

                if ($child['type'] === 'node') {
                    $childEl = $this->buildNodeFromXsd($doc, $child);
                    $element->appendChild($childEl);
                }
            }
        }

        return $element;
    }

private function buildNode(
    DOMDocument $doc,
    DOMElement $parent,
    array $definition,
    string $prefix,
    string $namespace
): void {

    $type = $definition['type'] ?? '';

    // =========================
    // ✅ FIELD (atributo)
    // =========================
    if ($type === 'field') {
        $this->buildField($doc, $parent, $definition, $prefix, $namespace);
        return;
    }

    // =========================
// ✅ GROUP (🔥 FIX FINAL)
// =========================
if ($type === 'group') {

    $name = $definition['name'] ?? '';
    if (!$name) return;

    // limpiar nombre
    if (strpos($name, ':') !== false) {
        $name = explode(':', $name, 2)[1];
    }

    $qualifiedName = $prefix
        ? $prefix . ':' . $name
        : $name;

    $groupElement = $doc->createElementNS(
        $namespace ?: null,
        $qualifiedName
    );

    $parent->appendChild($groupElement);

    // ✅ crear item
    $itemName = $definition['item_name'] ?? 'Item';

    if (strpos($itemName, ':') !== false) {
        $itemName = explode(':', $itemName, 2)[1];
    }

    $itemQName = $prefix
        ? $prefix . ':' . $itemName
        : $itemName;

    $itemElement = $doc->createElementNS(
        $namespace ?: null,
        $itemQName
    );

    $groupElement->appendChild($itemElement);

    // ✅ hijos del item
    foreach ($definition['children'] ?? [] as $child) {
        $this->buildNode($doc, $itemElement, $child, $prefix, $namespace);
    }

    return;
}

    // =========================
    // ✅ NODE (elemento hijo)
    // =========================
    if ($type === 'node') {

        $name = $definition['name'] ?? '';
        if (!$name) return;

        // limpiar name
        if (strpos($name, ':') !== false) {
            $name = explode(':', $name, 2)[1];
        }

        $qualifiedName = $prefix
            ? $prefix . ':' . $name
            : $name;

        $element = $doc->createElementNS(
            $namespace ?: null,
            $qualifiedName
        );

        // agregar al padre
        $parent->appendChild($element);

        // recursividad
        foreach ($definition['children'] ?? [] as $child) {
            $this->buildNode($doc, $element, $child, $prefix, $namespace);
        }
    }
}

private function buildField(
    DOMDocument $doc,
    DOMElement $parent,
    array $field,
    string $prefix,
    string $namespace
): void {
    $name = $field['name'] ?? null;

    if (!$name) return;

    // ✅ limpiar nombre (remover @)
    if (strpos($name, '@') === 0) {
        $name = substr($name, 1);
    }

    // ✅ limpiar prefix accidental tipo cfdi:xxx
    if (strpos($name, ':') !== false) {
        $name = explode(':', $name, 2)[1];
    }

    $parent->setAttribute($name, '');
}
}
