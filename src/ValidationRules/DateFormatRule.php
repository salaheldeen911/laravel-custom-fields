<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class DateFormatRule extends ValidationRule
{
    public function name(): string
    {
        return 'date_format';
    }

    public function label(): string
    {
        return 'Date Format';
    }

    public function baseRule(): array
    {
        return ['string'];
    }

    public function htmlTag(): string
    {
        return 'select';
    }

    public function htmlType(): string
    {
        return '';
    }

    public function placeholder(): string
    {
        return 'Choose a format';
    }

    public function description(): string
    {
        return 'The date must match the given format.';
    }

    public function options(): array
    {
        return [
            ['value' => 'Y-m-d', 'label' => 'Standard (2024-12-31)'],
            ['value' => 'd/m/Y', 'label' => 'European (31/12/2024)'],
            ['value' => 'm/d/Y', 'label' => 'US (12/31/2024)'],
            ['value' => 'd-m-Y', 'label' => 'Hyphenated (31-12-2024)'],
            ['value' => 'Y/m/d', 'label' => 'Slashed Year (2024/12/31)'],
        ];
    }

    public function apply($value): string
    {
        return "date_format:{$value}";
    }
}
