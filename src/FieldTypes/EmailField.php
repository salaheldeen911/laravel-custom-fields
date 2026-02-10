<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;
use Salah\LaravelCustomFields\ValidationRules\NotRegexRule;
use Salah\LaravelCustomFields\ValidationRules\RegexRule;

class EmailField extends FieldType
{
    public function name(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return 'Email Address';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'email';
    }

    public function description(): string
    {
        return 'A field for entering and validating email addresses.';
    }

    public function baseRule(): array
    {
        return ['email'];
    }

    public function allowedRules(): array
    {
        return [
            new FieldValidationRule(new MinRule, ['integer', 'min:0']),
            new FieldValidationRule(new MaxRule, ['integer', 'min:0']),
            new RegexRule,
            new NotRegexRule,
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.email';
    }
}
