<?php

namespace Salah\LaravelCustomFields;

use Illuminate\Support\Facades\Cache;
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
        return Cache::rememberForever('custom_fields_'.$modelClass, function () use ($modelClass) {
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
        Cache::forget('custom_fields_'.$modelClass);
    }
}
