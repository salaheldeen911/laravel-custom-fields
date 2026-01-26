<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class NumberField extends FieldType
{
    public function name(): string
    {
        return 'number';
    }

    public function label(): string
    {
        return 'Number Field';
    }

    public function baseRule(): string
    {
        return 'numeric';
    }

    public function allowedRules(): array
    {
        return [
            'min' => 'numeric',
            'max' => 'numeric',
            'between' => 'string', // min,max
            'gt' => 'numeric',
            'lt' => 'numeric',
            'integer' => 'boolean',
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.number';
    }
}
