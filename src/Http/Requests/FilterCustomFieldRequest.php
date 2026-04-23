<?php

namespace Salah\LaravelCustomFields\Http\Requests;

class FilterCustomFieldRequest extends CustomFieldBaseRequest
{
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
