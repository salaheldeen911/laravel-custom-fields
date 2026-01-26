<?php

namespace Salah\LaravelCustomFields\Services;

use Salah\LaravelCustomFields\DTOs\FieldTypeMeta;
use Salah\LaravelCustomFields\DTOs\ValidationRuleMeta;
use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\ValidationRuleRegistry;

class CustomFieldsMetaService
{
    public function __construct(
        protected FieldTypeRegistry $fieldTypeRegistry,
        protected ValidationRuleRegistry $validationRuleRegistry
    ) {}

    public function forBuilder(): array
    {
        return cache()->remember('custom-fields.meta.builder', 3600, function () {
            return [
                'models' => $this->getModels(),
                'types' => $this->getFieldTypes(),
            ];
        });
    }

    public function forIndex(): array
    {
        return cache()->remember('custom-fields.meta.index', 3600, function () {
            return [
                'models' => $this->getModels(),
                'types' => collect($this->getFieldTypes())->pluck('name')->toArray(),
            ];
        });
    }

    public function clearCache(): void
    {
        cache()->forget('custom-fields.meta.builder');
        cache()->forget('custom-fields.meta.index');
    }

    public function getAllMeta(): array
    {
        return $this->forBuilder();
    }

    public function getModels(): array
    {
        return array_keys(config('custom-fields.models', []));
    }

    public function getFieldTypes(): array
    {
        $allRuleDetails = $this->getValidationRuleDetails();
        $types = [];

        foreach ($this->fieldTypeRegistry->all() as $type) {
            $allowedRuleNames = array_keys($type->allowedRules());

            // Map the allowed rule names to their full details
            $fieldRuleDetails = [];
            foreach ($allowedRuleNames as $ruleName) {
                if (isset($allRuleDetails[$ruleName])) {
                    $fieldRuleDetails[] = $allRuleDetails[$ruleName];
                }
            }

            $types[] = (new FieldTypeMeta(
                name: $type->name(),
                label: $type->label(),
                base_rule: $type->baseRule(),
                has_options: $type->hasOptions(),
                allowed_rules: $fieldRuleDetails
            ))->toArray();
        }

        return $types;
    }

    public function getValidationRuleDetails(): array
    {
        $details = [];

        foreach ($this->validationRuleRegistry->all() as $rule) {
            $details[$rule->name()] = (new ValidationRuleMeta(
                name: $rule->name(),
                label: $rule->label(),
                type: $this->transformInputType($rule->inputType()),
                placeholder: $rule->placeholder(),
                description: $rule->description(),
                options: $rule->options()
            ))->toArray();
        }

        return $details;
    }

    protected function transformInputType(string $inputType): string
    {
        // Ensure consistency between backend and frontend
        return match ($inputType) {
            'checkbox' => 'boolean',
            'number' => 'number',
            'text' => 'text',
            default => 'text',
        };
    }
}
