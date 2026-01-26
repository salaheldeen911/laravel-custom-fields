<?php

namespace Salah\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    use HasFactory;

    protected $guard_name = 'api';

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

    public function getModelTypeAttribute($value)
    {
        return array_search($value, config('custom-fields.models'));
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
