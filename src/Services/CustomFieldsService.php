<?php

namespace Salah\LaravelCustomFields\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Salah\LaravelCustomFields\Exceptions\ValidationIntegrityException;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Models\CustomFieldValue;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;
use Salah\LaravelCustomFields\ValidationRuleRegistry;

class CustomFieldsService
{
    protected array $validatedHashes = [];

    public function __construct(
        protected CustomFieldRepositoryInterface $repository
    ) {}

    /**
     * Get rules for custom fields associated with the model.
     */
    public function getValidationRules(string $modelClass): array
    {
        $customFields = $this->repository->getByModel($modelClass);
        $rules = [];

        foreach ($customFields as $customField) {
            $rules[$customField->slug] = $this->getValueRule($customField);
        }

        return $rules;
    }

    /**
     * Validate the request data for custom fields.
     */
    public function validate(string $modelClass, array $data): ValidationValidator
    {
        $rules = $this->getValidationRules($modelClass);
        $validator = Validator::make($data, $rules);

        $validator->after(function ($validator) {
            if (! $validator->errors()->any()) {
                $this->markAsValidated($validator->validated());
            }
        });

        return $validator;
    }

    /**
     * Mark a data set as successfully validated.
     */
    public function markAsValidated(array $data): void
    {
        $hash = $this->generateDataHash($data);
        $this->validatedHashes[$hash] = true;
    }

    /**
     * Check if the data set has been validated.
     */
    public function isValidated(array $data): bool
    {
        return isset($this->validatedHashes[$this->generateDataHash($data)]);
    }

    /**
     * Generate a stable hash for the data set.
     */
    protected function generateDataHash(array $data): string
    {
        // Filter the data to only include keys that match custom field slugs for this model
        // Actually, just sorting and hashing the whole array is fine as long as it's consistent.
        ksort($data);

        return md5(json_encode($data));
    }

    /**
     * Store custom field values for a model instance.
     */
    public function storeValues(Model $model, array $data): void
    {
        $this->ensureDataIsValidated($data);

        $modelAlias = $model::getCustomFieldModelAlias();
        $customFields = $this->repository->getByModelAndSlugs($modelAlias, array_keys($data))
            ->keyBy('slug');

        $values = [];
        foreach ($data as $fieldSlug => $value) {
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
     */
    public function updateValues(Model $model, array $data): void
    {
        $this->ensureDataIsValidated($data);

        $modelAlias = $model::getCustomFieldModelAlias();
        $customFields = $this->repository->getByModelAndSlugs($modelAlias, array_keys($data))
            ->keyBy('slug');

        $values = [];
        foreach ($data as $fieldSlug => $value) {
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

    /**
     * Ensure the data has been validated before processing.
     */
    protected function ensureDataIsValidated(array $data): void
    {
        if (! config('custom-fields.strict_validation', true)) {
            return;
        }

        if (! $this->isValidated($data)) {
            throw ValidationIntegrityException::unvalidatedData();
        }
    }

    public function getValidationRuleDetails(): array
    {
        $registry = app(ValidationRuleRegistry::class);
        $details = [];

        foreach ($registry->all() as $rule) {
            $baseRule = $rule->baseRule();
            $serializableRules = array_values(array_filter($baseRule, fn ($r) => ! ($r instanceof \Closure)));

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

        if (! $handler) {
            return ['string'];
        }

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

            // Use stored value if exists, otherwise check for a default value defined in the rule class
            $value = array_key_exists($ruleName, $storedRules)
                ? $storedRules[$ruleName]
                : $ruleObj->defaultConfigValue();

            if (is_null($value)) {
                continue;
            }

            $baseRule = $ruleObj->baseRule();

            // For boolean rules, skip if false/null (unless it's a default that explicitly wants to run)
            if (in_array('boolean', $baseRule) && ! $value) {
                continue;
            }

            // For value-based rules, skip if empty string (unless it's a default that handles empty)
            if (! in_array('boolean', $baseRule) && $value === '' && is_null($ruleObj->defaultConfigValue())) {
                continue;
            }

            $rules[] = $ruleObj->apply($value);
        }

        return $rules;
    }
}
