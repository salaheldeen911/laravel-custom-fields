<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class CheckboxField extends FieldType
{
    public function name(): string
    {
        return 'checkbox';
    }

    public function label(): string
    {
        return 'Checkbox Group';
    }

    public function hasOptions(): bool
    {
        return true;
    }

    public function baseRule(): string
    {
        return 'array';
    }

    public function allowedRules(): array
    {
        return [
            'min' => 'integer',
            'max' => 'integer',
        ];
    }

    public function formatValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public function view(): string
    {
        return 'custom-fields::components.types.checkbox';
    }
}
