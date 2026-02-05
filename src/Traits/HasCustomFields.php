<?php

namespace Salah\LaravelCustomFields\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Models\CustomFieldValue;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;
use Salah\LaravelCustomFields\Services\CustomFieldsService;

trait HasCustomFields
{
    protected static array $modelAliasCache = [];

    /**
     * Boot the trait to handle model events.
     */
    public static function bootHasCustomFields()
    {
        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->customFieldsValues()->delete();
        });
    }

    public static function getCustomFieldModelAlias(): string
    {
        if (isset(static::$modelAliasCache[static::class])) {
            return static::$modelAliasCache[static::class];
        }

        $alias = array_search(static::class, config('custom-fields.models', []));

        return static::$modelAliasCache[static::class] = ($alias !== false ? $alias : static::class);
    }

    public static function customFields(): mixed
    {
        $modelAlias = static::getCustomFieldModelAlias();

        return Cache::rememberForever('custom_fields_'.$modelAlias, function () use ($modelAlias) {
            return app(CustomFieldRepositoryInterface::class)
                ->getByModel($modelAlias);
        });
    }

    /**
     * Get only the rules array for integration with FormRequests.
     */
    public static function getCustomFieldRules(): array
    {
        return app(CustomFieldsService::class)->getValidationRules(static::getCustomFieldModelAlias());
    }

    public static function customFieldsValidation(Request $request)
    {
        // For Backward Compatibility, we keep this signature but use the service.
        $service = app(CustomFieldsService::class);

        return $service->validate(static::getCustomFieldModelAlias(), $request->all());
    }

    public function saveCustomFields(array $data): void
    {
        $service = app(CustomFieldsService::class);
        $service->storeValues($this, $data);
        $this->unsetRelation('customFieldsValues');
    }

    public function updateCustomFields(array $data)
    {
        $service = app(CustomFieldsService::class);
        $service->updateValues($this, $data);
        $this->unsetRelation('customFieldsValues');
    }

    public function customFieldsValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'model')->with('customField');
    }

    /**
     * Scope to eager load custom field values correctly.
     */
    public function scopeWithCustomFields($query)
    {
        return $query->with(['customFieldsValues' => function ($q) {
            $q->with('customField');
        }]);
    }

    /**
     * Get all custom fields as a flat key-value array for API responses.
     */
    public function customFieldsResponse(): array
    {
        // Filter out values belonging to soft-deleted custom fields to prevent crashes
        return $this->customFieldsValues
            ->filter(fn ($item) => $item->customField !== null)
            ->keyBy('customField.slug')
            ->map->value
            ->toArray();
    }

    public function custom(string $fieldName)
    {
        // Check if relation is loaded and ensure customField exists
        $fieldValue = $this->customFieldsValues->first(function ($item) use ($fieldName) {
            return $item->customField && $item->customField->slug === $fieldName;
        });

        return $fieldValue ? $fieldValue->value : null;
    }

    public function scopeWhereCustomField($query, string $fieldName, $value)
    {
        return $query->whereHas('customFieldsValues', function ($q) use ($fieldName, $value) {
            $q->where('value', $value)
                ->whereHas('customField', function ($q) use ($fieldName) {
                    $q->where('slug', $fieldName);
                });
        });
    }
}
