<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class UrlRule extends ValidationRule
{
    public function name(): string
    {
        return 'url';
    }

    public function label(): string
    {
        return 'URL';
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
        return 'Validates that the input is a valid URL.';
    }

    public function apply($value): string
    {
        return 'url';
    }
}
