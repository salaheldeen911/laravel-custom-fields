<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class EmailRule extends ValidationRule
{
    public function name(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return 'Email Address';
    }

    public function baseRule(): array
    {
        return ['boolean'];
    }

    public function inputType(): string
    {
        return 'checkbox';
    }

    public function placeholder(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'Validates that the input is a valid email address.';
    }

    public function apply($value): string
    {
        return 'email';
    }
}
