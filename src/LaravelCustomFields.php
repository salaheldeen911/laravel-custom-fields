<?php

namespace CustomFields\LaravelCustomFields;

use Salah\LaravelCustomFields\Models\CustomField;

class LaravelCustomFields
{
    /**
     * Get all custom fields definition for a given model class.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFields(string $modelClass)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever('custom_fields_'.$modelClass, function () use ($modelClass) {
            return CustomField::where('model', $modelClass)->get();
        });
    }

    /**
     * Clear custom fields cache for a model.
     *
     * @return void
     */
    public function clearCache(string $modelClass)
    {
        \Illuminate\Support\Facades\Cache::forget('custom_fields_'.$modelClass);
    }
}
