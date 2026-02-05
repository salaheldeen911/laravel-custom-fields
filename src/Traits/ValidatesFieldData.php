<?php

namespace Salah\LaravelCustomFields\Traits;

use Salah\LaravelCustomFields\Services\CustomFieldsService;

trait ValidatesFieldData
{
    /**
     * Merge the custom fields validation rules with the request rules.
     * To be used inside the rules() method of a FormRequest.
     */
    public function withCustomFieldsRules(string $modelClass, array $baseRules = []): array
    {
        $service = app(CustomFieldsService::class);

        // Resolve alias
        $alias = array_search($modelClass, config('custom-fields.models', []));
        $target = $alias !== false ? $alias : $modelClass;

        $customRules = $service->getValidationRules($target);

        return array_merge($baseRules, $customRules);
    }

    /**
     * Automatically mark the custom fields data as validated after FormRequest passes.
     */
    protected function passedValidation(): void
    {
        app(CustomFieldsService::class)->markAsValidated($this->validated());
    }
}
