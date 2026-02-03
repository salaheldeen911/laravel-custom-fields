<?php

namespace Salah\LaravelCustomFields\Traits;

use Illuminate\Validation\Rule;
use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\ValidationRuleRegistry;

trait CustomFieldValidationRules
{
    /**
     * Prepare validation rules for storage by filtering falsy values
     * and ensuring the type's base rule is included.
     */
    protected function prepareRulesForStorage(array $rules, string $type): array
    {
        $handler = app(FieldTypeRegistry::class)->get($type);

        if (! $handler) {
            return $rules;
        }

        $allowedRules = $handler->allowedRules();

        $rulesMap = [];
        foreach ($allowedRules as $rule) {
            $ruleObj = is_string($rule) ? app($rule) : $rule;
            $rulesMap[$ruleObj->name()] = $ruleObj;
        }

        $registry = app(ValidationRuleRegistry::class);
        $normalizedRules = [];

        foreach ($rules as $ruleName => $value) {
            // Only allow rules that are BOTH allowed by the field type AND registered in the system
            if (! isset($rulesMap[$ruleName]) || ! $registry->get($ruleName)) {
                continue;
            }

            $ruleObj = $rulesMap[$ruleName];
            $baseRule = $ruleObj->baseRule();

            // For toggle/boolean rules (like alpha, email)
            if (in_array('boolean', $baseRule)) {
                if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $normalizedRules[$ruleName] = '1';
                }

                continue;
            }

            // For rules with values (like min, max, regex)
            if (! is_null($value) && $value !== '') {
                $normalizedRules[$ruleName] = $value;
            }
        }

        return $normalizedRules;
    }

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
                'regex:/^[\p{L}\p{N}\s]+$/u',
                // Unique name per model
                Rule::unique('custom_fields', 'name')->where(fn ($q) => $q->where('model', $this->model))->ignore($customFieldId),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                // Unique slug per model
                Rule::unique('custom_fields', 'slug')->where(fn ($q) => $q->where('model', $this->model))->ignore($customFieldId),
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
                        $allowedRules = $handler->allowedRules();
                        $allowedRuleNames = array_map(function ($rule) {
                            return is_string($rule) ? app($rule)->name() : $rule->name();
                        }, $allowedRules);

                        $registry = app(ValidationRuleRegistry::class);
                        
                        // Fix for when passed as string despite prepareForValidation
                        if (is_string($value)) {
                            $value = json_decode($value, true);
                        }

                        if (! is_array($value)) {
                            return;
                        }

                        $activeRuleNames = array_keys($value);

                        foreach ($activeRuleNames as $ruleName) {
                            if (is_numeric($ruleName)) {
                                continue;
                            }

                            if (! in_array($ruleName, $allowedRuleNames) || ! ($ruleObj = $registry->get($ruleName))) {
                                $fail("The rule '{$ruleName}' is invalid or not registered in the system.");

                                continue;
                            }

                            // Conflict Validation
                            $conflicts = $ruleObj->conflictsWith();
                            foreach ($conflicts as $conflictName) {
                                if (in_array($conflictName, $activeRuleNames)) {
                                    $conflictRule = $registry->get($conflictName);
                                    $conflictLabel = $conflictRule ? $conflictRule->label() : $conflictName;
                                    $fail("The rule '{$ruleObj->label()}' conflicts with '{$conflictName}'. You cannot use both.");
                                }
                            }
                        }
                    }
                },
            ],
        ];

        if ($this->has('validation_rules') && is_array($this->validation_rules)) {
            $handler = app(FieldTypeRegistry::class)->get($this->type);
            if ($handler) {
                $allowedRules = $handler->allowedRules();

                $rulesMap = [];
                foreach ($allowedRules as $rule) {
                    $ruleObj = is_string($rule) ? app($rule) : $rule;
                    $rulesMap[$ruleObj->name()] = $ruleObj;
                }

                foreach ($this->validation_rules as $ruleName => $ruleValue) {
                    if (is_numeric($ruleName)) {
                        $rules["validation_rules.$ruleName"] = 'string'; // The base rule itself (e.g., 'string', 'numeric')

                        continue;
                    }

                    if (isset($rulesMap[$ruleName])) {
                        $ruleObj = $rulesMap[$ruleName];
                        $rules["validation_rules.$ruleName"] = $ruleObj->baseRule();
                    }
                }
            }

            // Cross-field validation for min/max
            if (isset($this->validation_rules['min']) && isset($this->validation_rules['max'])) {
                if (is_numeric($this->validation_rules['min']) && is_numeric($this->validation_rules['max'])) {
                    $minRules = $rules['validation_rules.min'] ?? [];
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
