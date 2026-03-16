<?php

namespace Salah\LaravelCustomFields\Presenters;

use Salah\LaravelCustomFields\Models\CustomField;

class CustomFieldPresenter
{
    public function __construct(
        protected CustomField $customField
    ) {}

    /**
     * Get the current value for the field, considering the model and old input.
     */
    public function currentValue($model = null): mixed
    {
        $dbValue = null;

        if ($model && method_exists($model, 'custom')) {
            $dbValue = $model->custom($this->customField->slug);
        }

        // Format the value based on the field type handler
        $handler = $this->customField->handler();
        $formattedValue = $handler ? $handler->formatValue($dbValue) : $dbValue;

        // Old input takes precedence (using slug as the key)
        return old($this->customField->slug, $formattedValue);
    }

    /**
     * Prepare rules for UI display (converting "1" to true for checkboxes)
     */
    public function prepareRulesForUi(): array
    {
        $rules = $this->customField->validation_rules ?: [];
        $handler = $this->customField->handler();

        if (! $handler) {
            return $rules;
        }

        $allowedRules = $handler->allowedRules();
        $booleanRules = [];
        foreach ($allowedRules as $rule) {
            if (in_array('boolean', $rule->baseRule())) {
                $booleanRules[] = $rule->name();
            }
        }

        foreach ($rules as $key => $value) {
            if (in_array($key, $booleanRules)) {
                $rules[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $rules;
    }
}
