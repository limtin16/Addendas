<?php

namespace App\DTO;

class Template
{
    public ?string $id = null;
    public ?string $name = null;
    public ?string $location = null;

    /**
     * Puede ser null durante la hidratación.
     * El sistema siempre debe tratarla como array usando ?? []
     */
    public ?array $structure = null;

    public ?string $createdAt = null;

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'location'  => $this->location,
            'structure' => $this->structure ?? [],
            'createdAt' => $this->createdAt,
        ];
    }
}