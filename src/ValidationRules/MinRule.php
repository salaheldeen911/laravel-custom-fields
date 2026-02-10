<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class MinRule extends ValidationRule
{
    public function name(): string
    {
        return 'min';
    }

    public function label(): string
    {
        return 'Minimum Value';
    }

    public function baseRule(): array
    {
        return ['integer'];
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'number';
    }

    public function placeholder(): string
    {
        return 'Minimum allowed value';
    }

    public function description(): string
    {
        return 'Restricts the input to a minimum numeric value.';
    }

    public function apply($value): string
    {
        return "min:{$value}";
    }
}
