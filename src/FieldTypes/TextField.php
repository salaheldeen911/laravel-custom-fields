<?php

namespace Salah\LaravelCustomFields\FieldTypes;

class TextField extends FieldType
{
    public function name(): string
    {
        return 'text';
    }

    public function label(): string
    {
        return 'Text Field';
    }

    public function baseRule(): string
    {
        return 'string';
    }

    public function allowedRules(): array
    {
        return [
            'min' => 'integer',
            'max' => 'integer',
            'regex' => 'string',
            'not_regex' => 'string',
            'email' => 'boolean',
            'url' => 'boolean',
            'alpha' => 'boolean',
            'alpha_dash' => 'boolean',
            'alpha_num' => 'boolean',
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.text';
    }
}
