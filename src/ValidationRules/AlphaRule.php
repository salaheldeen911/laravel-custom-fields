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

    public function description(): string
    {
        return 'Validates that the input contains only alphabetic characters.';
    }

    public function conflictsWith(): array
    {
        return ['alpha_num', 'alpha_dash'];
    }

    public function apply($value): string
    {
        return 'alpha';
    }
}
