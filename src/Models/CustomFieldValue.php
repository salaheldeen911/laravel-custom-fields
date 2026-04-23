<?php

namespace Salah\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_field_id',
        'model_id',
        'model_type',
        'value',
    ];

    public function customField()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id', 'id');
    }

    protected static array $modelTypeCache = [];

    public function getModelTypeAttribute($value)
    {
        $useStaticCache = config('custom-fields.cache.octane_compatibility', true) === false;

        if ($useStaticCache && isset(static::$modelTypeCache[$value])) {
            return static::$modelTypeCache[$value];
        }

        $type = array_search($value, config('custom-fields.models', []));
        $result = ($type !== false ? $type : $value);

        if ($useStaticCache) {
            static::$modelTypeCache[$value] = $result;
        }

        return $result;
    }

    public function getValueAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        // Try to decode JSON if it looks like a JSON string
        if (str_starts_with($value, '{') || str_starts_with($value, '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }
}
