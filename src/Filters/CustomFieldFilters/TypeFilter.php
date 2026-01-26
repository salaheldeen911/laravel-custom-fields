<?php

namespace Salah\LaravelCustomFields\Filters\CustomFieldFilters;

use Salah\LaravelCustomFields\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class TypeFilter implements FilterInterface
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->where('type', $value);
    }
}
