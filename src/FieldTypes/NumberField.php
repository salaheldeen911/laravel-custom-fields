<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;

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

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlType(): string
    {
        return 'number';
    }

    public function description(): string
    {
        return 'A field for entering numeric values.';
    }

    public function baseRule(): array
    {
        return ['numeric'];
    }

    public function allowedRules(): array
    {
        return [
            new FieldValidationRule(new MinRule(), ['numeric', 'min:0']),
            new FieldValidationRule(new MaxRule(), ['numeric', 'min:0']),
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.number';
    }
}
