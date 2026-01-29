<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class AlphaDashRule extends ValidationRule
{
    public function name(): string
    {
        return 'alpha_dash';
    }

    public function label(): string
    {
        return 'Alpha-Dash (Letters, numbers, dashes, underscores)';
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
        return 'Validates that the input contains only alphabetic characters, dashes, and underscores.';
    }

    public function apply($value): string
    {
        return 'alpha_dash';
    }
}
