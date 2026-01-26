<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Salah\LaravelCustomFields\FieldTypeRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreCustomFieldRequest extends FormRequest
{
    use CustomFieldValidationRules;

    public function authorize(): bool
    {
        return true;
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
                // Remove null or empty string rules to avoid triggering "required" errors for unused rules
                $rules = array_filter($rules, fn ($value) => ! is_null($value) && $value !== '');
            }
            $this->merge(['validation_rules' => $rules]);
        }
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

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        Log::error('Custom Field Validation Failed:', $validator->errors()->toArray());
        parent::failedValidation($validator);
    }
}
