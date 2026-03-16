<?php

namespace Salah\LaravelCustomFields\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Salah\LaravelCustomFields\FieldTypeRegistry;
use Salah\LaravelCustomFields\FieldTypes\FieldType;
use Salah\LaravelCustomFields\Presenters\CustomFieldPresenter;

class CustomField extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class, 'custom_field_id', 'id');
    }

    public function handler(): ?FieldType
    {
        return app(FieldTypeRegistry::class)->get($this->type);
    }

    public function present(): CustomFieldPresenter
    {
        return new CustomFieldPresenter($this);
    }
}
