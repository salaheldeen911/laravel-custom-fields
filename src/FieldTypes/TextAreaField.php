<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;
use Salah\LaravelCustomFields\ValidationRules\NotRegexRule;
use Salah\LaravelCustomFields\ValidationRules\RegexRule;

class TextAreaField extends FieldType
{
    public function name(): string
    {
        return 'textarea';
    }

    public function label(): string
    {
        return 'Text Area (Multi-line)';
    }

    public function htmlTag(): string
    {
        return 'textarea';
    }

    public function htmlAttribute(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'A multi-line text input for longer content.';
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
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.textarea';
    }
}
