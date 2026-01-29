<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class FieldValidationRule extends ValidationRule
{
    public function __construct(
        private ValidationRule $rule,
        private ?array $baseRuleOverride = []
    ) {}

    public function name(): string
    {
        return $this->rule->name();
    }

    public function label(): string
    {
        return $this->rule->label();
    }

    public function baseRule(): array
    {
        return $this->baseRuleOverride !== null 
            ? (array) $this->baseRuleOverride 
            : $this->rule->baseRule();
    }

    public function htmlTag(): string
    {
        return $this->rule->htmlTag();
    }

    public function htmlType(): string
    {
        return $this->rule->htmlType();
    }

    public function placeholder(): string
    {
        return $this->rule->placeholder();
    }

    public function description(): string
    {
        return $this->rule->description();
    }

    public function options(): array
    {
        return $this->rule->options();
    }

    /**
     * Apply the rule to a value, returning the Laravel validation string segment.
     *
     * @param  mixed  $value  The configuration value provided by the user (e.g., 5 for min:5)
     */
    public function apply($value): string
    {
        return $this->rule->apply($value);
    }

    /**
     * Get the wrapped ValidationRule instance.
     */
    public function getWrappedRule(): ValidationRule
    {
        return $this->rule;
    }
}

