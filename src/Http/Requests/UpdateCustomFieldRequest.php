<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;

class UpdateCustomFieldRequest extends CustomFieldBaseRequest
{
    public function rules(): array
    {
        $validTypes = array_keys(app(FieldTypeRegistry::class)->all());
        $validModels = array_keys(config('custom-fields.models', []));

        return array_merge($this->getCommonRules($validTypes, $validModels), [
            'model' => ['prohibited'],
            'type' => ['prohibited'],
        ]);
    }

    public function messages()
    {
        return array_merge($this->customFieldMessages(), [
            'model.prohibited' => 'The model field cannot be updated.',
            'type.prohibited' => 'The field type cannot be updated.',
        ]);
    }

    protected function prepareForValidation()
    {
        $this->prepareCustomFieldInput();
    }

    /**
     * Ensure we still have the original model/type in the validated data for the DTO.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        $customField = $this->route('customField') ?? $this->route('custom_field');

        if ($customField) {
            if (is_string($customField)) {
                $customField = app(CustomFieldRepositoryInterface::class)->findById($customField, true);
            }

            $validated['model'] = $customField->model;
            $validated['type'] = $customField->type;
        }

        return $validated;
    }

    protected function failedValidation(Validator $validator)
    {
        $this->onFailedCustomFieldValidation($validator);
    }
}
