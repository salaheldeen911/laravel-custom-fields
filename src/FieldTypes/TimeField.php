<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class TimeField extends FieldType
{
    public function name(): string
    {
        return 'time';
    }

    public function label(): string
    {
        return 'Time Picker';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'time';
    }

    public function description(): string
    {
        return 'A field to select a specific time.';
    }

    public function baseRule(): array
    {
        return ['string']; // time format validation can be added as a custom rule if needed
    }

    public function allowedRules(): array
    {
        return [];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.time';
    }
}
