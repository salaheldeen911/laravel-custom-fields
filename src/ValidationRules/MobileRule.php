<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class MobileRule extends ValidationRule
{
    public function name(): string
    {
        return 'mobile_only';
    }

    public function label(): string
    {
        return 'Mobile Numbers Only';
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
        return 'Restrict to mobile numbers only.';
    }

    public function conflictsWith(): array
    {
        return ['landline_only'];
    }

    public function apply($value): string
    {
        return 'phone:mobile';
    }
}
