<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class AlphaNumRule extends ValidationRule
{
    public function name(): string
    {
        return 'alpha_num';
    }

    public function label(): string
    {
        return 'Alpha-Numeric (Letters and numbers)';
    }

    public function baseRule(): array
    {
        return ['boolean'];
    }

    public function description(): string
    {
        return 'Validates that the input contains only alphabetic and numeric characters.';
    }

    public function conflictsWith(): array
    {
        return ['alpha', 'alpha_dash'];
    }

    public function apply($value): string
    {
        return 'alpha_num';
    }
}
