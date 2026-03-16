<?php

namespace Salah\LaravelCustomFields\Actions;

use Illuminate\Validation\Rule;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;
use Salah\LaravelCustomFields\ValidationRuleRegistry;

class SetupValidationRulesAction
{
    public function __construct(
        protected CustomFieldRepositoryInterface $repository
    ) {}

    public function execute(string $modelClass): array
    {
        $customFields = $this->repository->getByModel($modelClass);
        $rules = [];

        foreach ($customFields as $customField) {
            $rules[$customField->slug] = $this->getValueRule($customField);
        }

        return $rules;
    }

    protected function getValueRule(CustomField $customField): array
    {
        $handler = $customField->handler();

        if (! $handler) {
            throw new \RuntimeException("Field type '{$customField->type}' is not registered.");
        }

        $rules = [
            $this->getRequirementRule($customField),
            ...$handler->baseRule(),
        ];

        if ($optionsRule = $this->getOptionsRule($customField, $handler)) {
            $rules[] = $optionsRule;
        }

        $rules = array_merge($rules, $this->getCustomRules($customField));

        $stringRules = array_filter($rules, 'is_string');
        $otherRules = array_filter($rules, fn($r) => ! is_string($r));
        $finalRules = array_merge(array_values(array_unique($stringRules)), array_values($otherRules));

        return $this->mergePhoneRules($finalRules);
    }

    protected function getRequirementRule(CustomField $customField): string
    {
        return $customField->required ? 'required' : 'nullable';
    }

    protected function getOptionsRule(CustomField $customField, $handler): mixed
    {
        if ($handler->hasOptions() && ! empty($customField->options)) {
            return Rule::in($customField->options);
        }

        return null;
    }

    protected function getCustomRules(CustomField $customField): array
    {
        $handler = $customField->handler();

        if (! $handler) {
            return [];
        }

        $allowedRules = $handler->allowedRules();
        $storedRules = $customField->validation_rules ?: [];
        $rules = [];

        foreach ($allowedRules as $rule) {
            $ruleObj = is_string($rule) ? app($rule) : $rule;
            $ruleName = $ruleObj->name();

            $value = array_key_exists($ruleName, $storedRules)
                ? $storedRules[$ruleName]
                : null;

            if (is_null($value)) {
                continue;
            }

            $baseRule = $ruleObj->baseRule();

            if (in_array('boolean', $baseRule) && ! $value) {
                continue;
            }

            if (! in_array('boolean', $baseRule) && $value === '') {
                continue;
            }

            $rules[] = $ruleObj->apply($value);
        }

        return $rules;
    }

    private function mergePhoneRules(array $rules): array
    {
        $phoneParams = [];
        $otherRules = [];

        foreach ($rules as $rule) {
            if (is_string($rule) && str_starts_with($rule, 'phone')) {
                $params = explode(',', substr($rule, 6));
                $phoneParams = array_merge($phoneParams, $params);
            } else {
                $otherRules[] = $rule;
            }
        }

        if (! empty($phoneParams)) {
            $uniqueParams = array_unique(array_filter($phoneParams));
            $otherRules[] = 'phone:' . implode(',', $uniqueParams);
        }

        return $otherRules;
    }
}
