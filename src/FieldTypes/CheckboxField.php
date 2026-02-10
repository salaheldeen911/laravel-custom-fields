<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;

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

    public function htmlTag(): string
    {
        return 'checkbox';
    }

    public function htmlAttribute(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'A group of checkboxes allowing multiple selections.';
    }

    public function hasOptions(): bool
    {
        return true;
    }

    public function baseRule(): array
    {
        return ['array'];
    }

    public function allowedRules(): array
    {
        return [
            new FieldValidationRule(new MinRule, ['integer', 'min:0']),
            new FieldValidationRule(new MaxRule, ['integer', 'min:0']),
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
