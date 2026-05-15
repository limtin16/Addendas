<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class XsdToArrayConverter
{
    private string $namespace = '';

    public function convert(string $xsdXml): array
    {
        $dom = new DOMDocument();
        $dom->loadXML($xsdXml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('xs', 'http://www.w3.org/2001/XMLSchema');

        // ✅ namespace real del XSD
        $schema = $dom->documentElement;
        $this->namespace = $schema->getAttribute('targetNamespace') ?: '';

        // ✅ root element
        $root = $xpath->query('//xs:element')->item(0);

        return [
            'type' => 'node',
            'name' => $root->getAttribute('name'), // ✅ SIN prefijo
            'namespace' => $this->namespace,
            'children' => $this->parseElementChildren($root, $xpath),
        ];
    }

    private function parseElementChildren($el, $xpath): array
    {
        $children = [];

        // ===============================
        // ✅ ATRIBUTOS → fields
        // ===============================
        foreach ($xpath->query('./xs:complexType/xs:attribute', $el) as $attr) {

            $field = [
                'type' => 'field',
                'name' => $attr->getAttribute('name'),
                'type_data' => 'string'
            ];

            // ===============================
            // ✅ ENUMS
            // ===============================
            $restriction = $xpath->query('.//xs:restriction', $attr)->item(0);

            if ($restriction) {

                $options = [];

                foreach ($xpath->query('.//xs:enumeration', $restriction) as $enum) {
                    $options[] = $enum->getAttribute('value');
                }

                if (!empty($options)) {
                    $field['type_data'] = 'enum';
                    $field['options'] = $options;
                }
            }

            $children[] = $field;
        }

        // ===============================
        // ✅ ELEMENTOS HIJOS
        // ===============================
        foreach ($xpath->query('./xs:complexType/xs:sequence/xs:element', $el) as $child) {

            $children[] = [
                'type' => 'node',
                'name' => $child->getAttribute('name'), // ✅ SIN prefijo
                'children' => $this->parseElementChildren($child, $xpath)
            ];
        }

        return $children;
    }
}