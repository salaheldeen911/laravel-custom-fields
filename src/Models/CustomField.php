<?php

namespace Salah\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\FieldTypes\FieldType;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

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
        return old($this->slug, $formattedValue);
    }

    /**
     * Prepare rules for UI display (converting "1" to true for checkboxes)
     */
    public function prepareRulesForUi(): array
    {
        $rules = $this->validation_rules ?: [];
        $handler = $this->handler();

        if (! $handler) {
            return $rules;
        }

        $allowedRules = $handler->allowedRules();
        $booleanRules = [];
        foreach ($allowedRules as $rule) {
            if (in_array('boolean', $rule->baseRule())) {
                $booleanRules[] = $rule->name();
            }
        }

        foreach ($rules as $key => $value) {
            if (in_array($key, $booleanRules)) {
                $rules[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $rules;
    }

    protected static function booted()
    {
        static::creating(function ($customField) {
            if (empty($customField->slug)) {
                $customField->slug = Str::slug($customField->name);
            }
        });

        static::updating(function ($customField) {
            if ($customField->isDirty('name') && ! $customField->isDirty('slug')) {
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
}
