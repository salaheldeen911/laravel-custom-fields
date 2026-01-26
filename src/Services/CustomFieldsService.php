<?php

namespace Salah\LaravelCustomFields\Services;

use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Models\CustomFieldValue;
use Salah\LaravelCustomFields\ValidationRuleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class CustomFieldsService
{
    /**
     * Get rules for custom fields associated with the model.
     */
    public function getValidationRules(string $modelClass): array
    {
        $customFields = CustomField::where('model', $modelClass)->get();
        $rules = [];

        foreach ($customFields as $customField) {
            $customFieldID = $customField->slug.'.custom_field_id';
            $customFieldValue = $customField->slug.'.value';

            $rules[$customField->slug] = 'array'.($customField->required ? '|required' : '');

            // We validate the ID if the field is present
            // Note: This logic assumes the input structure is array-based [slug => ['custom_field_id' => 1, 'value' => 'foo']]
            $rules[$customFieldID] = 'required_with:'.$customField->slug.'|integer|exists:custom_fields,id';
            $rules[$customFieldValue] = $this->getValueRule($customField);
        }

        return $rules;
    }

    /**
     * Validate the request data for custom fields.
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validate(string $modelClass, array $data)
    {
        $rules = $this->getValidationRules($modelClass);

        return Validator::make($data, $rules);
    }

    /**
     * Store custom field values for a model instance.
     *
     * @return void
     */
    public function storeValues(Model $model, array $validatedData)
    {
        $values = [];
        foreach ($validatedData as $fieldSlug => $fieldData) {
            if (! is_array($fieldData) || ! isset($fieldData['custom_field_id'])) {
                continue;
            }

            $values[] = [
                'custom_field_id' => $fieldData['custom_field_id'],
                'model_id' => $model->getKey(),
                'model_type' => $model->getMorphClass(),
                'value' => is_array($fieldData['value'] ?? null) ? json_encode($fieldData['value']) : ($fieldData['value'] ?? null),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($values)) {
            CustomFieldValue::insert($values);
        }
    }

    /**
     * Update custom field values for a model instance.
     *
     * @return void
     */
    public function updateValues(Model $model, array $validatedData)
    {
        $values = [];
        foreach ($validatedData as $fieldSlug => $fieldData) {
            if (! is_array($fieldData) || ! isset($fieldData['custom_field_id'])) {
                continue;
            }

            $values[] = [
                'custom_field_id' => $fieldData['custom_field_id'],
                'model_id' => $model->getKey(),
                'model_type' => $model->getMorphClass(),
                'value' => is_array($fieldData['value'] ?? null) ? json_encode($fieldData['value']) : ($fieldData['value'] ?? null),
                'updated_at' => now(),
            ];
        }

        if (! empty($values)) {
            CustomFieldValue::upsert(
                $values,
                ['custom_field_id', 'model_type', 'model_id'],
                ['value', 'updated_at']
            );
        }
    }

    protected function getValueRule(CustomField $customField): string
    {
        $handler = $customField->handler();

        if (! $handler) {
            // Fallback or error? defaulting to string
            return 'string';
        }

        $rules = [];

        // 1. Required or Nullable
        // We use 'bail' with required to prevent subsequent rules (like type checks)
        // from failing and causing duplicate or confusing error messages on null/empty input.
        if ($customField->required) {
            $rules[] = 'required';
            $rules[] = 'bail';
        } else {
            $rules[] = 'nullable';
        }

        // 2. Base Rule
        $rules[] = $handler->baseRule();

        // 3. Options
        if ($handler->hasOptions()) {
            $rules[] = 'in:'.@implode(',', $customField->options);
        }

        // 4. Custom Rules
        if (! empty($customField->validation_rules)) {
            $registry = app(ValidationRuleRegistry::class);

            foreach ($customField->validation_rules as $ruleName => $value) {
                // Determine if we should skip this rule
                // Handled above in getValueRule initialization via $customField->required

                $ruleObj = $registry->get($ruleName);
                if ($ruleObj) {
                    if ($ruleObj->configRule() === 'boolean') {
                        if (! $value) {
                            continue;
                        }
                    } elseif (is_null($value) || $value === '') {
                        // Skip rules with empty configuration values to prevent malformed rules like "max:"
                        continue;
                    }

                    $rules[] = $ruleObj->apply($value);
                } else {
                    // Fallback for standard rules
                    // Skip if value is empty/null which might cause issues?
                    // Assuming existing logic: allow if numeric key (value is rule) or value present.
                    // Let's safe check empty value (unless it's a flag rule)
                    if (! is_numeric($ruleName) && (is_null($value) || $value === '')) {
                        continue;
                    }

                    if (is_numeric($ruleName)) {
                        $rules[] = $value;
                    } else {
                        $rules[] = $ruleName.':'.$value;
                    }
                }
            }
        }

        return implode('|', array_filter($rules));
    }

    public function getValidationRuleDetails(): array
    {
        $registry = app(ValidationRuleRegistry::class);
        $details = [];

        foreach ($registry->all() as $rule) {
            $configRule = $rule->configRule();
            if (is_array($configRule)) {
                $configRule = array_values(array_filter($configRule, function ($item) {
                    return ! ($item instanceof \Closure);
                }));
            }
            $details[$rule->name()] = [
                'rule' => $configRule,
                'label' => $rule->label(),
                'inputType' => $rule->inputType(),
            ];
        }

        return $details;
    }
}
