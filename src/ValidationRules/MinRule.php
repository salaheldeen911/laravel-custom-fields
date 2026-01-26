<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class MinRule implements ValidationRule
{
    public function name(): string
    {
        return 'min';
    }

    public function label(): string
    {
        return 'Minimum Value';
    }

    public function configRule()
    {
        return ['integer', 'min:0'];
    }

    public function inputType(): string
    {
        return 'number';
    }

    public function placeholder(): ?string
    {
        return 'Minimum allowed value';
    }

    public function description(): ?string
    {
        return 'Restricts the input to a minimum numeric value.';
    }

    public function options(): array
    {
        return [];
    }

    public function apply($value): string
    {
        return "min:{$value}";
    }
}
