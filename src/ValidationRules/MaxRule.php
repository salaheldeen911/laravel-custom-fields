<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class MaxRule extends ValidationRule
{
    public function name(): string
    {
        return 'max';
    }

    public function label(): string
    {
        return 'Maximum Value';
    }

    public function baseRule(): array
    {
        return ['required', 'integer'];
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlType(): string
    {
        return 'number';
    }

    public function placeholder(): string
    {
        return 'Maximum allowed value';
    }

    public function description(): string
    {
        return 'Restricts the input to a maximum numeric value.';
    }

    public function apply($value): string
    {
        return "max:{$value}";
    }
}
