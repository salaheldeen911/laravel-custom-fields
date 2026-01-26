<?php

namespace Salah\LaravelCustomFields\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterCustomFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'required' => ['nullable', 'in:0,1'],
            'trashed' => ['nullable', 'in:only'],
        ];
    }
}
