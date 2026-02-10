<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\AfterDateRule;
use Salah\LaravelCustomFields\ValidationRules\AfterOrEqualDateRule;
use Salah\LaravelCustomFields\ValidationRules\BeforeDateRule;
use Salah\LaravelCustomFields\ValidationRules\BeforeOrEqualDateRule;
use Salah\LaravelCustomFields\ValidationRules\DateFormatRule;

class DateField extends FieldType
{
    public function name(): string
    {
        return 'date';
    }

    public function label(): string
    {
        return 'Date Picker';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'date';
    }

    public function description(): string
    {
        return 'A field for selecting dates using a calendar picker.';
    }

    public function baseRule(): array
    {
        return ['date'];
    }

    public function allowedRules(): array
    {
        return [
            new AfterDateRule,
            new BeforeDateRule,
            new AfterOrEqualDateRule,
            new BeforeOrEqualDateRule,
            new DateFormatRule,
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.date';
    }
}
