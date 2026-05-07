<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;

class XsdAddendaParserService
{
    private DOMXPath $xpath;
    private DOMDocument $xsdDom;
    private DOMDocument $xmlDom;

    // ✅ Namespace default definido por el XSD
    private ?string $targetNamespace = null;

    /**
     * Punto de entrada principal.
     * Recibe el XSD como texto y devuelve XML vacío válido.
     */
    public function parse(string $xsdContent): string
    {
        $this->loadXsd($xsdContent);
        $this->initXml();

        $rootElement = $this->getRootElement();
        if (!$rootElement) {
            throw new RuntimeException('No se encontró elemento raíz en XSD');
        }

        $xmlRoot = $this->parseElement($rootElement);
        $this->xmlDom->appendChild($xmlRoot);

        // ✅ Importante: devolver SOLO el nodo raíz (sin header XML)
        return $this->xmlDom->saveXML($xmlRoot);
    }

    /* =====================================================
       Cargar XSD
       ===================================================== */

    private function loadXsd(string $content): void
    {
        $this->xsdDom = new DOMDocument();
        if (!$this->xsdDom->loadXML($content)) {
            throw new RuntimeException('No se pudo cargar el XSD');
        }

        $this->xpath = new DOMXPath($this->xsdDom);
        $this->xpath->registerNamespace(
            'xs',
            'http://www.w3.org/2001/XMLSchema'
        );

        // ✅ Leer targetNamespace del schema
        $schema = $this->xpath->query('/xs:schema')->item(0);
        if ($schema instanceof DOMElement) {
            $this->targetNamespace = $schema->getAttribute('targetNamespace') ?: null;
        }
    }

    /* =====================================================
       Inicializar XML destino
       ===================================================== */

    private function initXml(): void
    {
        $this->xmlDom = new DOMDocument('1.0', 'UTF-8');
        $this->xmlDom->formatOutput = true;
    }

    /* =====================================================
       Obtener elemento raíz del XSD
       ===================================================== */

    private function getRootElement(): ?DOMElement
    {
        return $this->xpath
            ->query('/xs:schema/xs:element')
            ->item(0);
    }

    /* =====================================================
       Parseo recursivo de elementos
       ===================================================== */

    private function parseElement(DOMElement $xsdElement): DOMElement
    {
        $name = $xsdElement->getAttribute('name');
        if (!$name) {
            throw new RuntimeException('Elemento sin nombre');
        }

        // ✅ Crear elemento XML con namespace DEFAULT
        $xmlElement = $this->createXmlElement($name);

        // Atributos definidos directamente en el element
        $this->parseAttributes($xsdElement, $xmlElement);

        // ComplexType
        $complexType = $this->xpath
            ->query('xs:complexType', $xsdElement)
            ->item(0);

        if ($complexType instanceof DOMElement) {
            $this->parseComplexType($complexType, $xmlElement);
        }

        return $xmlElement;
    }

    /* =====================================================
       Parse complexType
       ===================================================== */

    private function parseComplexType(
        DOMElement $complexType,
        DOMElement $parentXml
    ): void {
        // Secuencia
        $sequence = $this->xpath
            ->query('xs:sequence', $complexType)
            ->item(0);

        if ($sequence instanceof DOMElement) {
            foreach ($this->xpath->query('xs:element', $sequence) as $child) {
                if (!$child instanceof DOMElement) {
                    continue;
                }

                // ✅ Solo una instancia (aunque maxOccurs > 1)
                $childXml = $this->parseElement($child);
                $parentXml->appendChild($childXml);
            }
        }

        // Atributos del complexType
        $this->parseAttributes($complexType, $parentXml);
    }

    /* =====================================================
       Parse atributos
       ===================================================== */

    private function parseAttributes(
        DOMElement $xsdNode,
        DOMElement $xmlNode
    ): void {
        foreach ($this->xpath->query('xs:attribute', $xsdNode) as $attr) {
            if (!$attr instanceof DOMElement) {
                continue;
            }

            $name = $attr->getAttribute('name');
            if ($name) {
                $xmlNode->setAttribute($name, '');
            }
        }
    }

    /* =====================================================
       Crear elemento XML con namespace default del XSD
       ===================================================== */

    private function createXmlElement(string $name): DOMElement
    {
        if ($this->targetNamespace) {
            $element = $this->xmlDom->createElement($name);

            // ⚠️ Forzar namespace default como atributo EXPLÍCITO
            if ($this->targetNamespace) {
                $element->setAttribute(
                    'xmlns',
                    $this->targetNamespace
                );
            }

            return $element;
        }

        return $this->xmlDom->createElement($name);
    }
}