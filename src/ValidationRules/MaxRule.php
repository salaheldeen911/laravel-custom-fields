<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class MaxRule implements ValidationRule
{
    public function name(): string
    {
        return 'max';
    }

    public function label(): string
    {
        return 'Maximum Value';
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
        return 'Maximum allowed value';
    }

    public function description(): ?string
    {
        return 'Restricts the input to a maximum numeric value.';
    }

    public function options(): array
    {
        return [];
    }

    public function apply($value): string
    {
        return "max:{$value}";
    }
}
