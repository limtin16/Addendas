<?php

namespace App\Services;

use DOMDocument;
use DOMElement;

class AddendaXmlBuilder
{
    /**
     * Construye el XML de la Addenda a partir de la estructura del template
     * DEVUELVE SOLO EL NODO RAÍZ (sin <?xml … ?>)
     */
    public function build(array $structure): string
    {
        if (!isset($structure['root'])) {
            throw new \InvalidArgumentException('Estructura inválida: no existe root');
        }

        $rootDef = $structure['root'];

        $name      = $rootDef['name'] ?? null;
        $prefix    = $rootDef['prefix'] ?? null;
        $namespace = $rootDef['namespace'] ?? null;
        $children  = $rootDef['children'] ?? [];

        if (!$name || !$prefix || !$namespace) {
            throw new \InvalidArgumentException('Root incompleto: name, prefix o namespace faltante');
        }

        // Documento
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        // ROOT con namespace
        $root = $doc->createElementNS(
            $namespace,
            $prefix . ':' . $name
        );

        $doc->appendChild($root);

        // Hijos
        foreach ($children as $child) {
            $this->buildNode($doc, $root, $child, $prefix, $namespace);
        }

        // ✅ DEVOLVER SOLO EL ELEMENTO RAÍZ (sin XML declaration)
        return $doc->saveXML($doc->documentElement);
    }

    /**
     * Construye nodos (field / group)
     */
    private function buildNode(
        DOMDocument $doc,
        DOMElement $parent,
        array $definition,
        string $prefix,
        string $namespace
    ): void {
        $type = $definition['type'] ?? 'field';

        if ($type === 'field') {
            $this->buildField($doc, $parent, $definition, $prefix, $namespace);
            return;
        }

        if ($type === 'group') {
            $this->buildGroup($doc, $parent, $definition, $prefix, $namespace);
            return;
        }

        throw new \InvalidArgumentException("Tipo de nodo desconocido: {$type}");
    }

    /**
     * Campo simple
     */
    private function buildField(
        DOMDocument $doc,
        DOMElement $parent,
        array $field,
        string $prefix,
        string $namespace
    ): void {
        $name = $field['name'] ?? null;
        if (!$name) {
            return;
        }

        $representation = $field['representation'] ?? 'node';
        $placeholder    = '{{' . $name . '}}';

        // Atributo
        if ($representation === 'attribute') {
            $parent->setAttribute($name, $placeholder);
            return;
        }

        // Nodo correctamente namespaced
        $el = $doc->createElementNS(
            $namespace,
            $prefix . ':' . $name,
            $placeholder
        );

        $parent->appendChild($el);
    }

    /**
     * Grupo (Conceptos)
     */
    private function buildGroup(
        DOMDocument $doc,
        DOMElement $parent,
        array $group,
        string $prefix,
        string $namespace
    ): void {
        $groupName = $group['name'] ?? null;
        $itemName  = $group['itemName'] ?? 'Item';
        $children  = $group['children'] ?? [];

        if (!$groupName) {
            return;
        }

        // Grupo
        $groupEl = $doc->createElementNS(
            $namespace,
            $prefix . ':' . $groupName
        );

        // Item base
        $itemEl = $doc->createElementNS(
            $namespace,
            $prefix . ':' . $itemName
        );

        foreach ($children as $childDef) {
            $this->buildNode($doc, $itemEl, $childDef, $prefix, $namespace);
        }

        $groupEl->appendChild($itemEl);
        $parent->appendChild($groupEl);
    }
}