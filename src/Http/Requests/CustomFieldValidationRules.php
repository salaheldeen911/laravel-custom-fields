<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\ValidationRuleRegistry;
use Illuminate\Validation\Rule;

trait CustomFieldValidationRules
{
    /**
     * Get the validation rules common to both Store and Update requests.
     */
    protected function getCommonRules(array $validTypes, array $validModels): array
    {
        $customFieldId = $this->route('customField') ?? $this->route('custom_field');

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/',
                Rule::unique('custom_fields', 'name')->where(fn ($q) => $q->where('model', $this->model))->ignore($customFieldId),
            ],
            'model' => ['required', 'string', Rule::in($validModels), 'bail'],
            'type' => ['required', 'string', Rule::in($validTypes), 'bail'],
            'required' => ['boolean'],
            'placeholder' => 'nullable|string|max:255',
            'options' => [
                'nullable',
                'array',
                Rule::requiredIf(function () {
                    $handler = app(FieldTypeRegistry::class)->get($this->type);

                    return $handler ? $handler->hasOptions() : false;
                }),
            ],
            'options.*' => 'required|string|distinct|max:255',
            'validation_rules' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $handler = app(FieldTypeRegistry::class)->get($this->type);
                    if ($handler) {
                        $allowed = array_keys($handler->allowedRules());
                        foreach (array_keys($value) as $ruleName) {
                            if (! in_array($ruleName, $allowed)) {
                                $fail("The rule '{$ruleName}' is not allowed for the selected field type.");
                            }
                        }
                    }
                },
            ],
        ];

        if ($this->has('validation_rules') && is_array($this->validation_rules)) {
            $registry = app(ValidationRuleRegistry::class);
            foreach ($this->validation_rules as $ruleName => $ruleValue) {
                $ruleObj = $registry->get($ruleName);
                if ($ruleObj) {
                    $rules["validation_rules.$ruleName"] = $ruleObj->configRule();
                }
            }

            // Cross-field validation for min/max
            if (isset($this->validation_rules['min']) && isset($this->validation_rules['max'])) {
                if (is_numeric($this->validation_rules['min']) && is_numeric($this->validation_rules['max'])) {
                    $minRules = $rules['validation_rules.min'] ?? [];
                    if (is_string($minRules)) {
                        $minRules = explode('|', $minRules);
                    }
                    $rules['validation_rules.min'] = array_merge(
                        (array) $minRules,
                        ['lte:validation_rules.max']
                    );
                }
            }
        }

        return $rules;
    }
}
