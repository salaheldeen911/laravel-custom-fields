<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class ColorField extends FieldType
{
    public function name(): string
    {
        return 'color';
    }

    public function label(): string
    {
        return 'Color Picker';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'color';
    }

    public function description(): string
    {
        return 'A color selection input.';
    }

    public function baseRule(): array
    {
        return ['string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];
    }

    public function allowedRules(): array
    {
        return [];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.color';
    }
}
