<?php

namespace Salah\LaravelCustomFields\Services;

use Salah\LaravelCustomFields\DTOs\ElementMeta;
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
        $types = [];

        foreach ($this->fieldTypeRegistry->all() as $type) {
            $allowedRules = $type->allowedRules();

            // Map the ValidationRule objects to their full details
            $fieldRuleDetails = [];
            foreach ($allowedRules as $rule) {
                $fieldRuleDetails[] = (new ElementMeta(element: $rule))->toArray();
            }

            $types[] = (new ElementMeta(
                element: $type,
                additionalData: [
                    'has_options' => $type->hasOptions(),
                    'allowed_rules' => $fieldRuleDetails
                ]
            ))->toArray();
        }

        return $types;
    }

    public function getValidationRuleDetails(): array
    {
        $details = [];

        foreach ($this->validationRuleRegistry->all() as $rule) {
            $details[$rule->name()] = (new ElementMeta(element: $rule))->toArray();
        }

        return $details;
    }
}

