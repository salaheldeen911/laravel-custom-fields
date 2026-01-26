<?php

namespace Salah\LaravelCustomFields\FieldTypes;

abstract class FieldType
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
     * Whether this field type supports options (for select, radio, etc).
     */
    public function hasOptions(): bool
    {
        return false;
    }

    /**
     * The base validation rule for the value storage (e.g. 'string', 'integer', 'array').
     */
    abstract public function baseRule(): string;

    /**
     * Allowed validation rules with their expected value types.
     * Example: ['min' => 'integer', 'max' => 'integer']
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
        $allowed = array_keys($this->allowedRules());
        foreach (array_keys($rules) as $rule) {
            if (! in_array($rule, $allowed)) {
                throw new \InvalidArgumentException("Rule '$rule' is not allowed for field type '{$this->name()}'");
            }
            // Additional check for rule value type could go here
        }
    }
}
