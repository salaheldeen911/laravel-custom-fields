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
            $rules[$customField->slug] = $this->getValueRule($customField);
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
        $modelAlias = $model::getCustomFieldModelAlias();
        $customFields = CustomField::where('model', $modelAlias)
            ->whereIn('slug', array_keys($validatedData))
            ->get()
            ->keyBy('slug');

        $values = [];
        foreach ($validatedData as $fieldSlug => $value) {
            $customField = $customFields->get($fieldSlug);

            if (! $customField) {
                continue;
            }

            $values[] = [
                'custom_field_id' => $customField->id,
                'model_id' => $model->getKey(),
                'model_type' => $model->getMorphClass(),
                'value' => is_array($value) ? json_encode($value) : $value,
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
        $modelAlias = $model::getCustomFieldModelAlias();
        $customFields = CustomField::where('model', $modelAlias)
            ->whereIn('slug', array_keys($validatedData))
            ->get()
            ->keyBy('slug');

        $values = [];
        foreach ($validatedData as $fieldSlug => $value) {
            $customField = $customFields->get($fieldSlug);

            if (! $customField) {
                continue;
            }

            $values[] = [
                'custom_field_id' => $customField->id,
                'model_id' => $model->getKey(),
                'model_type' => $model->getMorphClass(),
                'value' => is_array($value) ? json_encode($value) : $value,
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

    public function getValidationRuleDetails(): array
    {
        $registry = app(ValidationRuleRegistry::class);
        $details = [];

        foreach ($registry->all() as $rule) {
            $baseRule = $rule->baseRule();
            $serializableRules = array_values(array_filter($baseRule, fn($r) => !($r instanceof \Closure)));

            $details[$rule->name()] = [
                'rule' => $serializableRules,
                'label' => $rule->label(),
                'tag' => $rule->htmlTag(),
                'type' => $rule->htmlType(),
            ];
        }

        return $details;
    }

    protected function getValueRule(CustomField $customField): array
    {
        $handler = $customField->handler();

        if (! $handler) return ['string'];

        $rules = [
            $this->getRequirementRule($customField),
            ...$handler->baseRule(),
        ];

        if ($optionsRule = $this->getOptionsRule($customField, $handler)) {
            $rules[] = $optionsRule;
        }

        $rules = array_merge($rules, $this->getCustomRules($customField));

        return array_values(array_unique(array_filter($rules)));
    }

    private function getRequirementRule(CustomField $customField): string
    {
        return $customField->required ? 'required' : 'nullable';
    }

    private function getOptionsRule(CustomField $customField, $handler): ?string
    {
        if ($handler->hasOptions() && ! empty($customField->options)) {
            return 'in:'.implode(',', $customField->options);
        }

        return null;
    }

    private function getCustomRules(CustomField $customField): array
    {
        if (empty($customField->validation_rules)) {
            return [];
        }

        $registry = app(ValidationRuleRegistry::class);
        $rules = [];

        foreach ($customField->validation_rules as $ruleName => $value) {
            $ruleObj = $registry->get($ruleName);
            
            if (! $ruleObj) {
                continue;
            }

            $baseRule = $ruleObj->baseRule();
            
            if (in_array('boolean', $baseRule)) {
                if (! $value) {
                    continue;
                }
            } elseif (is_null($value) || $value === '') {
                continue;
            }

            $rules[] = $ruleObj->apply($value);
        }

        return $rules;
    }
}
