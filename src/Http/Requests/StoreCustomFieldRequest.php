<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Salah\LaravelCustomFields\FieldTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Salah\LaravelCustomFields\Traits\CustomFieldValidationRules;
use \Illuminate\Contracts\Validation\Validator;

class StoreCustomFieldRequest extends FormRequest
{
    use CustomFieldValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get valid types dynamically
        $validTypes = array_keys(app(FieldTypeRegistry::class)->all());
        $validModels = array_keys(config('custom-fields.models', []));

        return $this->getCommonRules($validTypes, $validModels);
    }

    public function messages()
    {
        return [
            'model.in' => 'The selected model is invalid.',
            'type.in' => 'The selected field type is invalid.',
            'options.required_if' => 'Options are required for this field type.',
            'name.unique' => 'A field with this name already exists for the selected model.',
        ];
    }

    protected function prepareForValidation()
    {
        // Decode JSON if string (API support)
        if ($this->has('options')) {
            $options = $this->options;
            if (is_string($options)) {
                $options = json_decode($options, true);
            }
            if (is_array($options)) {
                $options = array_values(array_filter($options, fn ($value) => ! is_null($value) && $value !== ''));
            }
            $this->merge(['options' => $options]);
        }

        if ($this->has('validation_rules')) {
            $rules = $this->validation_rules;
            if (is_string($rules)) {
                $rules = json_decode($rules, true);
            }
            
            if (is_array($rules)) {
                $rules = $this->prepareRulesForStorage($rules, $this->type);
            }
            
            $this->merge(['validation_rules' => $rules]);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        Log::error('Custom Field Creation Validation Failed:', $validator->errors()->toArray());
        parent::failedValidation($validator);
    }
}
