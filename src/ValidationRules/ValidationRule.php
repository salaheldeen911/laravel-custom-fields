<?php

namespace Salah\LaravelCustomFields\ValidationRules;

use Salah\LaravelCustomFields\Contracts\ConfigurableElement;

abstract class ValidationRule implements ConfigurableElement
{
    /**
     * The unique identifier for this rule.
     */
    abstract public function name(): string;

    /**
     * The human-readable label for the UI.
     */
    abstract public function label(): string;

    /**
     * The HTML tag to be used on the frontend for configuring this rule.
     */
    public function htmlTag(): string
    {
        return 'input';
    }

    /**
     * The type attribute for the HTML tag.
     */
    public function htmlType(): string
    {
        return 'text';
    }

    /**
     * The placeholder for the UI input.
     */
    public function placeholder(): string
    {
        return '';
    }

    /**
     * A description of what this rule does, for the UI.
     */
    public function description(): string
    {
        return '';
    }

    /**
     * Optional predefined values for the rule configuration.
     */
    public function options(): array
    {
        return [];
    }

    /**
     * The base validation rule for configuring this rule.
     */
    public function baseRule(): array
    {
        return ['string'];
    }

    /**
     * Apply the rule to a value, returning the Laravel validation string segment.
     */
    abstract public function apply($value): string;
}
