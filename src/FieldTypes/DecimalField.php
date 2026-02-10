<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;

class DecimalField extends FieldType
{
    public function name(): string
    {
        return 'decimal';
    }

    public function label(): string
    {
        return 'Decimal Number';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'number';
    }

    public function description(): string
    {
        return 'A numeric input that supports decimal values.';
    }

    public function baseRule(): array
    {
        return ['numeric'];
    }

    public function allowedRules(): array
    {
        return [
            new FieldValidationRule(new MinRule, ['numeric']),
            new FieldValidationRule(new MaxRule, ['numeric']),
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.decimal';
    }
}
