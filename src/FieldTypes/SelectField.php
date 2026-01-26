<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class SelectField extends FieldType
{
    public function name(): string
    {
        return 'select';
    }

    public function label(): string
    {
        return 'Dropdown (Select)';
    }

    public function hasOptions(): bool
    {
        return true;
    }

    public function baseRule(): string
    {
        return 'string';
    }

    public function allowedRules(): array
    {
        return [];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.select';
    }
}
