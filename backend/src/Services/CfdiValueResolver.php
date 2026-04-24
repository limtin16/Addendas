<?php

namespace App\Services;

use App\DTO\CfdiMap;

class CfdiValueResolver
{
    private CfdiMap $cfdiMap;

    public function __construct(CfdiMap $cfdiMap)
    {
        $this->cfdiMap = $cfdiMap;
    }

    /**
     * Resuelve valores desde el CFDI.
     *
     * Soporta:
     *   cfdi.folio
     *   cfdi.fecha
     *   cfdi.moneda
     *
     * Y también, en contexto de grupo:
     *   cfdi.cantidad
     *   cfdi.valorunitario
     */
    public function resolve(
        string $path,
        ?array $concepto = null,
        ?int $index = null
    ) {
        $parts = explode('.', $path);

        if ($parts[0] !== 'cfdi') {
            return null;
        }

        // Quitamos 'cfdi'
        array_shift($parts);

        // ✅ CASO 1: estamos dentro de un grupo (concepto actual)
        if ($concepto !== null && count($parts) === 1) {
            $key = strtolower($parts[0]);

            return $concepto[$key] ?? null;
        }

        // ✅ CASO 2: navegación normal sobre CfdiMap
        $current = $this->cfdiMap;

        foreach ($parts as $key) {

            // Propiedad del objeto CfdiMap
            if (is_object($current) && property_exists($current, $key)) {
                $current = $current->$key;
                continue;
            }

            // Array (emisor, receptor, etc.)
            if (is_array($current) && array_key_exists($key, $current)) {
                $current = $current[$key];
                continue;
            }

            return null;
        }

        return $current;
    }

    /**
     * Devuelve los conceptos del CFDI (para grupos)
     */
    public function getConceptos(): array
    {
        return $this->cfdiMap->conceptos ?? [];
    }
}