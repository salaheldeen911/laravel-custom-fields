<?php

namespace CustomFields\LaravelCustomFields;

use Salah\LaravelCustomFields\ValidationRules\ValidationRule;

class ValidationRuleRegistry
{
    protected array $rules = [];

    public function register(ValidationRule $rule): void
    {
        $this->rules[$rule->name()] = $rule;
    }

    public function get(string $name): ?ValidationRule
    {
        return $this->rules[$name] ?? null;
    }

    /**
     * @return ValidationRule[]
     */
    public function all(): array
    {
        return $this->rules;
    }
}
