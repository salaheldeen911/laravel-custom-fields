<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class PhoneField extends FieldType
{
    public function name(): string
    {
        return 'phone';
    }

    public function label(): string
    {
        return 'Phone Number';
    }

    public function baseRule(): string
    {
        return 'string';
    }

    public function allowedRules(): array
    {
        return [
            'phone' => 'string',
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.phone';
    }
}
