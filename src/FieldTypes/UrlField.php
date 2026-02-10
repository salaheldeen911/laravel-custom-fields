<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\FieldValidationRule;
use Salah\LaravelCustomFields\ValidationRules\MaxRule;
use Salah\LaravelCustomFields\ValidationRules\MinRule;
use Salah\LaravelCustomFields\ValidationRules\NotRegexRule;
use Salah\LaravelCustomFields\ValidationRules\RegexRule;

class UrlField extends FieldType
{
    public function name(): string
    {
        return 'url';
    }

    public function label(): string
    {
        return 'URL Field';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'url';
    }

    public function description(): string
    {
        return 'A field for entering and validating web URLs.';
    }

    public function baseRule(): array
    {
        return ['url'];
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
        return 'custom-fields::components.types.url';
    }
}
