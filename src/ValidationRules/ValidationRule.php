<?php

namespace Salah\LaravelCustomFields\ValidationRules;

interface ValidationRule
{
    /**
     * The unique name of the rule (e.g., 'min', 'max', 'regex').
     */
    public function name(): string;

    /**
     * A human-readable label for the UI (e.g., 'Minimum Value').
     */
    public function label(): string;

    /**
     * The validation rule(s) required for the rule's configuration value itself.
     * Example: 'integer' means the user must input an integer for this rule.
     *
     * @return string|array
     */
    public function configRule();

    /**
     * The input type to be used on the frontend for configuring this rule.
     * Examples: 'number', 'text', 'checkbox'.
     */
    public function inputType(): string;

    /**
     * The placeholder for the UI input.
     */
    public function placeholder(): ?string;

    /**
     * A description of what this rule does, for the UI.
     */
    public function description(): ?string;

    /**
     * Optional predefined values for the rule configuration.
     */
    public function options(): array;

    /**
     * Apply the rule to a value, returning the Laravel validation string segment.
     *
     * @param  mixed  $value  The configuration value provided by the user (e.g., 5 for min:5)
     */
    public function apply($value): string;
}
