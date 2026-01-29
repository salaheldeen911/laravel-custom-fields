<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\Contracts\ConfigurableElement;
use Salah\LaravelCustomFields\ValidationRules\ValidationRule;

abstract class FieldType implements ConfigurableElement
{
    /**
     * The unique identifier for this field type (e.g. 'text', 'select').
     */
    abstract public function name(): string;

    /**
     * The human-readable label (e.g. 'Text Field').
     */
    abstract public function label(): string;

    /**
     * The HTML tag to be used on the frontend for this field.
     */
    public function htmlTag(): string
    {
        return 'input';
    }

    /**
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
     * A description of what this field type is.
     */
    public function description(): string
    {
        return '';
    }

    /**
     * Optional predefined values for the field (for select, radio, etc).
     */
    public function options(): array
    {
        return [];
    }

    /**
     * Whether this field type supports options (for select, radio, etc).
     */
    public function hasOptions(): bool
    {
        return false;
    }

    /**
     * The base validation rule for the value storage (e.g. 'string', 'integer', 'array').
     */
    abstract public function baseRule(): array;

    /**
     * Allowed validation rules as ValidationRule objects.
     * Each field type can return ValidationRule instances, optionally wrapped with FieldValidationRule
     * to override baseRule() behavior.
     *
     * @return ValidationRule[]
     */
    abstract public function allowedRules(): array;

    /**
     * The blade view to render this field.
     */
    abstract public function view(): string;

    /**
     * Format the stored value for display/frontend.
     */
    public function formatValue(mixed $value): mixed
    {
        return $value;
    }

    /**
     * Validate the rules provided by the user against this type's capabilities.
     * This ensures the user doesn't add 'min' to a 'boolean' field if not supported.
     */
    public function validateRules(array $rules): void
    {
        $allowedRules = $this->allowedRules();
        $allowedRuleNames = array_map(fn(ValidationRule $rule) => $rule->name(), $allowedRules);

        foreach (array_keys($rules) as $rule) {
            if (! in_array($rule, $allowedRuleNames)) {
                throw new \InvalidArgumentException("Rule '$rule' is not allowed for field type '{$this->name()}'");
            }
            // Additional check for rule value type could go here
        }
    }
}
