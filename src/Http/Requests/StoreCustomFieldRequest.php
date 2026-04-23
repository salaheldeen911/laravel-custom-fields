<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Salah\LaravelCustomFields\FieldTypeRegistry;

class StoreCustomFieldRequest extends CustomFieldBaseRequest
{
    public function rules(): array
    {
        $validTypes = array_keys(app(FieldTypeRegistry::class)->all());
        $validModels = array_keys(config('custom-fields.models', []));

        return $this->getCommonRules($validTypes, $validModels);
    }

    public function messages()
    {
        return $this->customFieldMessages();
    }

    protected function prepareForValidation()
    {
        $this->prepareCustomFieldInput();
    }

    protected function failedValidation(Validator $validator)
    {
        $this->onFailedCustomFieldValidation($validator);
    }
}
