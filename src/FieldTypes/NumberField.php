<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;

class NumberField extends FieldType
{
    public function name(): string
    {
        return 'number';
    }

    public function label(): string
    {
        return 'Number Field';
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
        return 'A field for entering numeric values.';
    }

    public function baseRule(): array
    {
        return ['numeric'];
    }

    public function allowedRules(): array
    {
        return [
            MinRule::class,
            MaxRule::class,
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.number';
    }
}
