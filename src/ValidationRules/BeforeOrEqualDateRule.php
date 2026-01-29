<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class BeforeOrEqualDateRule extends ValidationRule
{
    public function name(): string
    {
        return 'before_or_equal';
    }

    public function label(): string
    {
        return 'Before or Equal to Date';
    }

    public function baseRule(): array
    {
        return ['date'];
    }

    public function inputType(): string
    {
        return 'date';
    }

    public function placeholder(): string
    {
        return 'Select a date';
    }

    public function description(): string
    {
        return 'The input must be a date before or equal to the specified date.';
    }

    public function apply($value): string
    {
        return "before_or_equal:{$value}";
    }
}
