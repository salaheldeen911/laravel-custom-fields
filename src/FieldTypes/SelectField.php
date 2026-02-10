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

    public function htmlTag(): string
    {
        return 'select';
    }

    public function htmlAttribute(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'A dropdown menu allowing selection of a single option.';
    }

    public function hasOptions(): bool
    {
        return true;
    }

    public function baseRule(): array
    {
        return ['string'];
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
