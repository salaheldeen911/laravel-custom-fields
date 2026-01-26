<?php

namespace Salah\LaravelCustomFields\Filters\CustomFieldFilters;

use Salah\LaravelCustomFields\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class ModelFilter implements FilterInterface
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->where('model', $value);
    }
}
