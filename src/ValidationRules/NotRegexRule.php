<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class NotRegexRule extends ValidationRule
{
    public function name(): string
    {
        return 'not_regex';
    }

    public function label(): string
    {
        return 'Not Regular Expression (Not Regex)';
    }

    public function baseRule(): array
    {
        return ['required', 'string', function ($attribute, $value, $fail) {
            if (@preg_match("/$value/", '') === false) {
                $fail('The regular expression is invalid.');
            }
        }];
    }

    public function placeholder(): string
    {
        return '^[a-zA-Z]+$';
    }

    public function description(): string
    {
        return 'Validates that the input does not match a regular expression pattern.';
    }

    public function apply($value): string
    {
        return "not_regex:/{$value}/";
    }
}
