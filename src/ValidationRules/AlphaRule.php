<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class AlphaRule extends ValidationRule
{
    public function name(): string
    {
        return 'alpha';
    }

    public function label(): string
    {
        return 'Alphabetic (Letters only)';
    }

    public function baseRule(): array
    {
        return ['boolean'];
    }

    public function htmlTag(): string
    {
        return 'checkbox';
    }

    public function htmlType(): string
    {
        return '';
    }

    public function placeholder(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'Validates that the input contains only alphabetic characters.';
    }

    public function apply($value): string
    {
        return 'alpha';
    }
}
