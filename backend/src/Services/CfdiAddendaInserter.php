<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;

class CfdiAddendaInserter
{
    /**
     * Inserta la addenda ya generada (y autofilled) dentro del CFDI.
     * Opción A: el namespace del cliente vive en <LGM:Factura>,
     * cfdi:Addenda solo tiene namespace SAT.
     */
    public function insert(string $cfdiXml, string $addendaXml): string
    {
        // 1. Cargar CFDI
        $cfdiDoc = new DOMDocument('1.0', 'UTF-8');
        $cfdiDoc->preserveWhiteSpace = false;
        $cfdiDoc->formatOutput = true;
        $cfdiDoc->loadXML($cfdiXml);

        // 2. Detectar versión CFDI y namespace SAT
        $comprobante = $cfdiDoc->documentElement;
        $version = $comprobante->getAttribute('Version');

        $cfdiNamespace = match ($version) {
            '3.3' => 'http://www.sat.gob.mx/cfd/3',
            '4.0' => 'http://www.sat.gob.mx/cfd/4',
            default => 'http://www.sat.gob.mx/cfd/4',
        };

        $xpath = new DOMXPath($cfdiDoc);
        $xpath->registerNamespace('cfdi', $cfdiNamespace);

        // 3. Eliminar Addenda previa si existe
        $existing = $xpath->query('cfdi:Addenda', $comprobante);
        if ($existing->length > 0) {
            $comprobante->removeChild($existing->item(0));
        }

        // 4. Crear cfdi:Addenda (SOLO namespace SAT)
        $addendaEl = $cfdiDoc->createElementNS(
            $cfdiNamespace,
            'cfdi:Addenda'
        );

        // 5. Cargar XML de la addenda (root = <LGM:Factura>)
        $addendaDoc = new DOMDocument('1.0', 'UTF-8');
        $addendaDoc->loadXML($addendaXml);

        $facturaNode = $addendaDoc->documentElement;

        // 6. Importar SOLO <LGM:Factura>
        $addendaEl->appendChild(
            $cfdiDoc->importNode($facturaNode, true)
        );

        // 7. Insertar Addenda en el CFDI
        $comprobante->appendChild($addendaEl);

        // 8. Devolver CFDI final
        return $cfdiDoc->saveXML();
    }
}