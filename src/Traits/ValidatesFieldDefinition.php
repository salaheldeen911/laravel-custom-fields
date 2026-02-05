<?php

namespace Salah\LaravelCustomFields\Traits;

use Illuminate\Validation\Rule;
use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\ValidationRuleRegistry;

trait ValidatesFieldDefinition
{
    /**
     * Prepare validation rules for storage by filtering falsy values
     * and ensuring the type's base rule is included.
     */
    protected function prepareRulesForStorage(array $rules, string $type): array
    {
        $handler = $this->getFieldHandler($type);

        if (! $handler) {
            return $rules;
        }

        $rulesMap = $this->mapAllowedRules($handler);
        $registry = app(ValidationRuleRegistry::class);
        $normalizedRules = [];

        foreach ($rules as $ruleName => $value) {
            if ($this->shouldSkipRule($ruleName, $rulesMap, $registry)) {
                continue;
            }

            $processedValue = $this->processRuleValue($rulesMap[$ruleName], $value);

            if (! is_null($processedValue)) {
                $normalizedRules[$ruleName] = $processedValue;
            }
        }

        return $normalizedRules;
    }

    protected function getFieldHandler(string $type)
    {
        return app(FieldTypeRegistry::class)->get($type);
    }

    protected function mapAllowedRules($handler): array
    {
        $rulesMap = [];
        foreach ($handler->allowedRules() as $rule) {
            $ruleObj = is_string($rule) ? app($rule) : $rule;
            $rulesMap[$ruleObj->name()] = $ruleObj;
        }

        return $rulesMap;
    }

    protected function shouldSkipRule(string $ruleName, array $rulesMap, $registry): bool
    {
        return ! isset($rulesMap[$ruleName]) || ! $registry->get($ruleName);
    }

    protected function processRuleValue($ruleObj, $value)
    {
        $baseRule = $ruleObj->baseRule();

        // For toggle/boolean rules (like alpha, email)
        if (in_array('boolean', $baseRule)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : null;
        }

        // For rules with values (like min, max, regex)
        if (! is_null($value) && $value !== '') {
            return $value;
        }

        return null;
    }

    /**
     * Get the validation rules common to both Store and Update requests.
     */
    protected function getCommonRules(array $validTypes, array $validModels): array
    {
        $customFieldId = $this->route('customField') ?? $this->route('custom_field');

        return array_merge(
            $this->getNameRules($customFieldId),
            $this->getModelRules($validModels),
            $this->getTypeRules($validTypes),
            $this->getStandardFieldRules(),
            $this->getOptionsRules(),
            $this->getValidationRulesRules()
        );
    }

    protected function getNameRules($customFieldId): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[\p{L}\p{N}\s]+$/u',
                Rule::unique('custom_fields', 'name')
                    ->where(fn ($q) => $q->where('model', $this->model))
                    ->ignore($customFieldId),
            ],
        ];
    }

    protected function getModelRules(array $validModels): array
    {
        return [
            'model' => ['required', 'string', Rule::in($validModels), 'bail'],
        ];
    }

    protected function getTypeRules(array $validTypes): array
    {
        return [
            'type' => ['required', 'string', Rule::in($validTypes), 'bail'],
        ];
    }

    protected function getStandardFieldRules(): array
    {
        return [
            'required' => ['boolean'],
            'placeholder' => 'nullable|string|max:255',
        ];
    }

    protected function getOptionsRules(): array
    {
        return [
            'options' => [
                'nullable',
                'array',
                Rule::requiredIf(function () {
                    $handler = app(FieldTypeRegistry::class)->get($this->type);

                    return $handler ? $handler->hasOptions() : false;
                }),
            ],
            'options.*' => 'required|string|distinct|max:255',
        ];
    }

    protected function getValidationRulesRules(): array
    {
        $rules = [
            'validation_rules' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $this->validateRulesCompatibility($attribute, $value, $fail);
                },
            ],
        ];

        // Add dynamic rules for the specific values inside validation_rules
        if ($this->has('validation_rules') && is_array($this->validation_rules)) {
            $rules = array_merge($rules, $this->getDynamicRuleConstraints());
        }

        return $rules;
    }

    /**
     * validate that the selected rules are allowed for this field type
     * and do not conflict with each other.
     */
    protected function validateRulesCompatibility($attribute, $value, $fail): void
    {
        $handler = app(FieldTypeRegistry::class)->get($this->type);
        if (! $handler) {
            return;
        }

        // Fix for when passed as string despite prepareForValidation
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        if (! is_array($value)) {
            return;
        }

        $allowedRules = $handler->allowedRules();
        $allowedRuleNames = array_map(fn ($rule) => is_string($rule) ? app($rule)->name() : $rule->name(), $allowedRules);
        $registry = app(ValidationRuleRegistry::class);

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
            foreach ($ruleObj->conflictsWith() as $conflictName) {
                if (in_array($conflictName, $activeRuleNames)) {
                    $fail("The rule '{$ruleObj->label()}' conflicts with '{$conflictName}'. You cannot use both.");
                }
            }
        }
    }

    /**
     * Generate specific validation constraints for the values of the rules themselves.
     * e.g. ensuring 'min' is an integer, or 'regex' is a valid regex string.
     */
    protected function getDynamicRuleConstraints(): array
    {
        $rules = [];
        $handler = app(FieldTypeRegistry::class)->get($this->type);

        if (! $handler) {
            return $rules;
        }

        $allowedRules = $handler->allowedRules();
        $rulesMap = [];
        foreach ($allowedRules as $rule) {
            $ruleObj = is_string($rule) ? app($rule) : $rule;
            $rulesMap[$ruleObj->name()] = $ruleObj;
        }

        foreach ($this->validation_rules as $ruleName => $ruleValue) {
            if (is_numeric($ruleName)) {
                // It's a keyless rule (like 'required' if we had it in this array, but we structure it differently)
                continue;
            }

            if (isset($rulesMap[$ruleName])) {
                $rules["validation_rules.$ruleName"] = $rulesMap[$ruleName]->baseRule();
            }
        }

        // Cross-field validation (Min vs Max)
        if ($this->shouldValidateMinMax()) {
            $minRules = $rules['validation_rules.min'] ?? [];
            $rules['validation_rules.min'] = array_merge(
                (array) $minRules,
                ['lte:validation_rules.max']
            );
        }

        return $rules;
    }

    protected function shouldValidateMinMax(): bool
    {
        return isset($this->validation_rules['min'], $this->validation_rules['max'])
            && is_numeric($this->validation_rules['min'])
            && is_numeric($this->validation_rules['max']);
    }
}
