<?php

namespace App\DTO;

class CfdiMap
{
    public string $version;
    public string $uuid;
    public string $serie;
    public string $folio;
    public string $fecha;
    public string $moneda;
    public float $subtotal;
    public float $total;
	
    public array $comprobante = [];
    public array $emisor = [];
    public array $receptor = [];
    public array $conceptos = [];

    public function toArray(): array
    {
        return [
            'version'   => $this->version,
            'uuid'      => $this->uuid,
            'serie'     => $this->serie,
            'folio'     => $this->folio,
            'fecha'     => $this->fecha,
            'moneda'    => $this->moneda,
            'subtotal'  => $this->subtotal,
            'total'     => $this->total,
            'emisor'    => $this->emisor,
            'receptor'  => $this->receptor,
            'conceptos' => $this->conceptos,
        ];
    }
}