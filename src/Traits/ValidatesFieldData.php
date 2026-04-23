<?php

namespace Salah\LaravelCustomFields\Traits;

use Illuminate\Database\Eloquent\Model;
use Salah\LaravelCustomFields\Services\CustomFieldsService;

trait ValidatesFieldData
{
    /**
     * Merge the custom fields validation rules with the request rules.
     * To be used inside the rules() method of a FormRequest.
     *
     * If no $model is provided, it automatically resolves the correct model instance
     * from the route's bindings (e.g., on update routes with route model binding).
     */
    public function withCustomFieldsRules(string $modelClass, array $baseRules = [], ?Model $model = null): array
    {
        $service = app(CustomFieldsService::class);

        // Resolve alias
        $alias = array_search($modelClass, config('custom-fields.models', []));
        $target = $alias !== false ? $alias : $modelClass;

        // Auto-detect the model from route bindings if not explicitly provided.
        // This eliminates the need to manually pass the model on update FormRequests.
        if ($model === null) {
            $model = $this->resolveModelFromRoute($modelClass);
        }

        $customRules = $service->getValidationRules($target, $model);

        return array_merge($baseRules, $customRules);
    }

    /**
     * Automatically mark the custom fields data as validated after FormRequest passes.
     */
    protected function passedValidation(): void
    {
        app(CustomFieldsService::class)->markAsValidated();
    }

    /**
     * Resolve the model instance from the current route's bindings.
     * Finds the first route parameter that is an instance of the given class.
     */
    protected function resolveModelFromRoute(string $modelClass): ?Model
    {
        $route = $this->route();

        if (! $route) {
            return null;
        }

        foreach ($route->parameters() as $parameter) {
            if ($parameter instanceof $modelClass) {
                return $parameter;
            }
        }

        return null;
    }
}
