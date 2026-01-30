<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class AfterOrEqualDateRule extends ValidationRule
{
    public function name(): string
    {
        return 'after_or_equal';
    }

    public function label(): string
    {
        return 'After or Equal to Date';
    }

    public function baseRule(): array
    {
        return ['date'];
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlType(): string
    {
        return 'date';
    }

    public function placeholder(): string
    {
        return 'Select a date';
    }

    public function description(): string
    {
        return 'The input must be a date after or equal to the specified date.';
    }

    public function conflictsWith(): array
    {
        return ['after'];
    }

    public function apply($value): string
    {
        return "after_or_equal:{$value}";
    }
}
