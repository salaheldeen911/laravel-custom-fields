<?php

namespace CustomFields\LaravelCustomFields;

use Salah\LaravelCustomFields\FieldTypes\FieldType;

class FieldTypeRegistry
{
    protected array $types = [];

    public function register(FieldType $type): void
    {
        $this->types[$type->name()] = $type;
    }

    public function get(string $name): ?FieldType
    {
        return $this->types[$name] ?? null;
    }

    /**
     * @return FieldType[]
     */
    public function all(): array
    {
        return $this->types;
    }
}
