<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class BeforeDateRule extends ValidationRule
{
    public function name(): string
    {
        return 'before';
    }

    public function label(): string
    {
        return 'Before Date';
    }

    public function baseRule(): array
    {
        return ['date'];
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'date';
    }

    public function placeholder(): string
    {
        return 'Select a date';
    }

    public function description(): string
    {
        return 'The input must be a date before the specified date.';
    }

    public function conflictsWith(): array
    {
        return ['before_or_equal'];
    }

    public function apply($value): string
    {
        return "before:{$value}";
    }
}
