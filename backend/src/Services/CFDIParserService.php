<?php

namespace App\Services;

// ✅ Cargar DTO
require_once dirname(__DIR__) . '/DTO/CfdiMap.php';

use App\DTO\CfdiMap;
use DOMDocument;
use DOMXPath;

class CFDIParserService
{
    public function parse(string $cfdiXml): CfdiMap
    {
        if (trim($cfdiXml) === '') {
            throw new \InvalidArgumentException('CFDI XML vacío');
        }

        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($cfdiXml); // ✅ USAR LA VARIABLE CORRECTA

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/4');

        $map = new CfdiMap();

        // ✅ Ejemplo mínimo de parse (puede ser más completo en tu caso)
        $comprobante = $xpath->query('/cfdi:Comprobante')->item(0);

        if ($comprobante instanceof \DOMElement) {

			$comprobanteData = [
				'folio'             => $comprobante->getAttribute('Folio'),
				'fecha'             => $comprobante->getAttribute('Fecha'),
				'moneda'            => $comprobante->getAttribute('Moneda'),
				'serie'             => $comprobante->getAttribute('Serie'),
				'tipodecomprobante' => $comprobante->getAttribute('TipoDeComprobante'),
				'lugarexpedicion'   => $comprobante->getAttribute('LugarExpedicion'),
			];

			// ✅ Mantener propiedades existentes (compatibilidad)
			$map->folio  = $comprobanteData['folio'];
			$map->fecha  = $comprobanteData['fecha'];
			$map->moneda = $comprobanteData['moneda'];

			// ✅ NUEVO: exponer comprobante como array
			$map->comprobante = $comprobanteData;
		}

        // ✅ Conceptos
        $conceptos = [];
        foreach ($xpath->query('//cfdi:Concepto') as $concepto) {
            $conceptos[] = [
                'cantidad'         => $concepto->getAttribute('Cantidad'),
                'valorunitario'    => $concepto->getAttribute('ValorUnitario'),
                'claveunidad'      => $concepto->getAttribute('ClaveUnidad'),
                'noidentificacion' => $concepto->getAttribute('NoIdentificacion'),
            ];
        }

        $map->conceptos = $conceptos;

        return $map;
    }
}