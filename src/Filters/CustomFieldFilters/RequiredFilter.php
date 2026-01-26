<?php

namespace Salah\LaravelCustomFields\Filters\CustomFieldFilters;

use Salah\LaravelCustomFields\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class RequiredFilter implements FilterInterface
{
    public static function apply(Builder $builder, $value): Builder
    {
        return $builder->where('required', $value);
    }
}
