<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class RegexRule implements ValidationRule
{
    public function name(): string
    {
        return 'regex';
    }

    public function label(): string
    {
        return 'Regex Pattern';
    }

    public function configRule()
    {
        return [
            'required',
            'string',
            function ($attribute, $value, $fail) {
                if (@preg_match("/{$value}/", '') === 0) {
                    $fail("The {$attribute} must be a valid regular expression pattern.");
                }
            },
        ];
    }

    public function inputType(): string
    {
        return 'text';
    }

    public function placeholder(): ?string
    {
        return '^[a-zA-Z]+$';
    }

    public function description(): ?string
    {
        return 'Validates the input against a custom regular expression pattern.';
    }

    public function options(): array
    {
        return [];
    }

    public function apply($value): string
    {
        return "regex:/{$value}/";
    }
}
