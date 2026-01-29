<?php

namespace Salah\LaravelCustomFields\Traits;

use Salah\LaravelCustomFields\Models\CustomField;
use Salah\LaravelCustomFields\Models\CustomFieldValue;
use Salah\LaravelCustomFields\Services\CustomFieldsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

trait HasCustomFields
{
    public static function getCustomFieldModelAlias(): bool|int|string
    {
        $alias = array_search(self::class, config('custom-fields.models'));

        return $alias !== false ? $alias : self::class;
    }

    public static function customFields(): mixed
    {
        $modelKey = self::getCustomFieldModelAlias();

        return Cache::rememberForever('custom_fields_'.$modelKey, function () use ($modelKey) {
            return CustomField::where('model', $modelKey)->get();
        });
    }

    public static function customFieldsValidation(Request $request)
    {
        // For Backward Compatibility, we keep this signature but use the service.
        $service = app(CustomFieldsService::class);

        return $service->validate(self::getCustomFieldModelAlias(), $request->all());
    }

    public function saveCustomFields(array $data): void
    {
        $service = app(CustomFieldsService::class);
        // We assume $data is already validated or contains the correct specific structure keys
        $service->storeValues($this, $data);
        // Also refresh relations?
        $this->load('customFieldsValues');
    }

    public function updateCustomFields(array $data)
    {
        $service = app(CustomFieldsService::class);
        $service->updateValues($this, $data);
        $this->load('customFieldsValues');
    }

    public static function storeCustomFieldValue($validation, $model)
    {
        // Wrapper for backward compatibility or cleaner use if validator is passed
        $service = app(CustomFieldsService::class);
        $service->storeValues($model, $validation->validated());
    }

    public static function updateCustomFieldValue($validation, $model)
    {
        $service = app(CustomFieldsService::class);
        $service->updateValues($model, $validation->validated());
    }

    public function customFieldsValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'model')->with('customField');
    }

    /**
     * Get all custom fields as a flat key-value array for API responses.
     */
    public function customFieldsResponse(): array
    {
        return $this->customFieldsValues->mapWithKeys(function ($item) {
            return [$item->customField->slug => $item->value];
        })->toArray();
    }

    public function custom(string $fieldName)
    {
        $field = $this->customFieldsValues->sortByDesc('created_at')->first(function ($item) use ($fieldName) {
            return $item->customField->slug === $fieldName;
        });

        return $field ? $field->value : null;
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
