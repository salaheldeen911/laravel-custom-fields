<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class MaxFileSizeRule extends ValidationRule
{
    public function name(): string
    {
        return 'max_file_size';
    }

    public function label(): string
    {
        return 'Max File Size (KB)';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'number';
    }

    public function description(): string
    {
        return 'Maximum allowed file size in kilobytes.';
    }

    public function baseRule(): array
    {
        return ['integer', 'min:1'];
    }

    public function apply($value): string
    {
        return "max:$value";
    }
}
