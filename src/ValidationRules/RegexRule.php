<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class RegexRule extends ValidationRule
{
    public function name(): string
    {
        return 'regex';
    }

    public function label(): string
    {
        return 'Regular Expression (Regex)';
    }

    public function baseRule(): array
    {
        return ['string', function ($attribute, $value, $fail) {
            if (@preg_match("/$value/", '') === false) {
                $fail('The regular expression is invalid.');
            }

            $oldLimit = ini_get('pcre.backtrack_limit');
            ini_set('pcre.backtrack_limit', '10000');
            $result = @preg_match("/$value/", str_repeat('a', 100));
            ini_set('pcre.backtrack_limit', $oldLimit);

            if ($result === false) {
                $fail('The regular expression is too complex or causes excessive backtracking.');
            }
        }];
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function placeholder(): string
    {
        return '^[a-zA-Z]+$';
    }

    public function description(): string
    {
        return 'Validates the input against a custom regular expression pattern.';
    }

    public function apply($value): string
    {
        return "regex:/{$value}/";
    }
}
