<?php

namespace Salah\LaravelCustomFields\Models;

use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\FieldTypes\FieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($customField) {
            if (empty($customField->slug)) {
                $customField->slug = Str::slug($customField->name);
            }
        });

        static::updating(function ($customField) {
            if ($customField->isDirty('name')) {
                $customField->slug = Str::slug($customField->name);
            }
        });

        static::saved(function ($customField) {
            Cache::forget('custom_fields_'.$customField->attributes['model']);
        });

        static::deleted(function ($customField) {
            Cache::forget('custom_fields_'.$customField->attributes['model']);
        });
    }

    protected $guard_name = 'api';

    protected $table = 'custom_fields';

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'required' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'slug',
        'model',
        'type',
        'required',
        'placeholder',
        'options',
        'validation_rules',
        'deleted_at',
    ];

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class, 'custom_field_id', 'id');
    }

    public function handler(): ?FieldType
    {
        return app(FieldTypeRegistry::class)->get($this->type);
    }

    /**
     * Get the current value for the field, considering the model and old input.
     */
    public function currentValue($model = null): mixed
    {
        $dbValue = null;

        if ($model && method_exists($model, 'custom')) {
            $dbValue = $model->custom($this->slug);
        }

        // Format the value based on the field type handler
        $formattedValue = $this->handler() ? $this->handler()->formatValue($dbValue) : $dbValue;

        // Old input takes precedence (using slug as the key)
        return old($this->slug.'.value', $formattedValue);
    }
}
