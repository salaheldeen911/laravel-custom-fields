<?php

namespace Salah\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::deleted(function ($customFieldValue) {
            // Check if value looks like a file metadata JSON
            $value = $customFieldValue->attributes['value'] ?? null;
            if ($value && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                if (! config('custom-fields.files.cleanup', true)) {
                    return;
                }

                $data = json_decode($value, true);
                $disk = config('custom-fields.files.disk', 'public');

                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($data['path']) && Storage::disk($disk)->exists($data['path'])) {
                        Storage::disk($disk)->delete($data['path']);
                    }
                }
            }
        });
    }

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
        if (isset(static::$modelTypeCache[$value])) {
            return static::$modelTypeCache[$value];
        }

        $type = array_search($value, config('custom-fields.models', []));

        return static::$modelTypeCache[$value] = ($type !== false ? $type : $value);
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
