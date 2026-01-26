<?php

namespace Salah\LaravelCustomFields\Traits;

use Salah\LaravelCustomFields\Services\CustomFieldsService;

trait ValidatesCustomFields
{
    /**
     * Merge the custom fields validation rules with the request rules.
     * To be used inside the rules() method of a FormRequest.
     */
    public function withCustomFieldsRules(string $modelClass, array $baseRules = []): array
    {
        // Resolve alias if needed, though getValidationRules expects the 'value' stored in DB if we query CustomField.
        // Wait, CustomFeields are stored with 'alias' in DB now.
        // So we need to pass the alias to getValidationRules.

        // We can't easily access the HasCustomFields trait alias resolver from here statically without the model instance.
        // But the user passes $modelClass.

        // Let's instantiate the service.
        $service = new CustomFieldsService;

        // Resolve alias
        $alias = array_search($modelClass, config('custom-fields.models'));
        $target = $alias !== false ? $alias : $modelClass;

        $customRules = $service->getValidationRules($target);

        return array_merge($baseRules, $customRules);
    }
}
