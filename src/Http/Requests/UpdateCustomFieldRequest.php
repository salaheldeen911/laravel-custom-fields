<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\Traits\ValidatesFieldDefinition;

class UpdateCustomFieldRequest extends FormRequest
{
    use ValidatesFieldDefinition;

    public function authorize(): bool
    {
        return true;
    }

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
