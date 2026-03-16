<?php

namespace Salah\LaravelCustomFields\Observers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Salah\LaravelCustomFields\Models\CustomField;

class CustomFieldObserver
{
    public function creating(CustomField $customField): void
    {
        if (empty($customField->slug)) {
            $customField->slug = Str::slug($customField->name);
        }
    }

    public function updating(CustomField $customField): void
    {
        if ($customField->isDirty('name') && ! $customField->isDirty('slug')) {
            $customField->slug = Str::slug($customField->name);
        }
    }

    public function saved(CustomField $customField): void
    {
        Cache::forget(config('custom-fields.cache.prefix', 'custom_fields_') . $customField->getAttributes()['model']);
    }

    public function deleted(CustomField $customField): void
    {
        Cache::forget(config('custom-fields.cache.prefix', 'custom_fields_') . $customField->getAttributes()['model']);
    }

    public function forceDeleting(CustomField $customField): void
    {
        if ($customField->type === 'file') {
            $customField->values()->each(function ($value) {
                $value->delete();
            });
        }
    }
}
