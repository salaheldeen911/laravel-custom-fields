<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class LandlineRule extends ValidationRule
{
    public function name(): string
    {
        return 'landline_only';
    }

    public function label(): string
    {
        return 'Landline Numbers Only';
    }

    public function baseRule(): array
    {
        return ['boolean']; // It's a checkbox/toggle, so base rule is boolean
    }

    public function htmlTag(): string
    {
        return 'checkbox';
    }

    public function htmlAttribute(): string
    {
        return '';
    }

    public function placeholder(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'Restrict to landline/fixed-line numbers only.';
    }

    public function conflictsWith(): array
    {
        return ['mobile_only'];
    }

    public function apply($value): string
    {
        if ($value) {
            return 'phone:fixed_line';
        }

        return '';
    }
}
