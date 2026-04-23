<?php

namespace Salah\LaravelCustomFields;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Salah\LaravelCustomFields\Models\CustomField;

class LaravelCustomFields
{
    /**
     * Get all custom fields definition for a given model class.
     *
     * @return Collection
     */
    public function getFields(string $modelClass)
    {
        $prefix = config('custom-fields.cache.prefix', 'custom_fields_');
        $ttl = config('custom-fields.cache.ttl', 3600);

        return Cache::remember($prefix.$modelClass, $ttl, function () use ($modelClass) {
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
        $prefix = config('custom-fields.cache.prefix', 'custom_fields_');
        Cache::forget($prefix.$modelClass);
    }
}
