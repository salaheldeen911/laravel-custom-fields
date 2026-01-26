<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class RequiredRule implements ValidationRule
{
    public function name(): string
    {
        return 'required';
    }

    public function label(): string
    {
        return 'Required';
    }

    public function configRule()
    {
        return 'boolean';
    }

    public function inputType(): string
    {
        return 'checkbox';
    }

    public function placeholder(): ?string
    {
        return null;
    }

    public function description(): ?string
    {
        return 'Makes the field mandatory.';
    }

    public function options(): array
    {
        return [];
    }

    public function apply($value): string
    {
        return 'required';
    }
}
