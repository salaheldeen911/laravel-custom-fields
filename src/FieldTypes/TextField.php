<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\AlphaDashRule;
use Salah\LaravelCustomFields\ValidationRules\AlphaNumRule;
use Salah\LaravelCustomFields\ValidationRules\AlphaRule;
use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;
use Salah\LaravelCustomFields\ValidationRules\NotRegexRule;
use Salah\LaravelCustomFields\ValidationRules\RegexRule;

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

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'text';
    }

    public function description(): string
    {
        return 'A basic text input field for strings.';
    }

    public function baseRule(): array
    {
        return ['string'];
    }

    public function allowedRules(): array
    {
        return [
            new FieldValidationRule(new MinRule, ['integer', 'min:0']),
            new FieldValidationRule(new MaxRule, ['integer', 'min:0']),
            new RegexRule,
            new NotRegexRule,
            new AlphaRule,
            new AlphaDashRule,
            new AlphaNumRule,
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.text';
    }
}
