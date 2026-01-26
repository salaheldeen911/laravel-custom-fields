<?php

namespace Salah\LaravelCustomFields\Filters\CustomFieldFilters;

use Salah\LaravelCustomFields\Filters\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class TrashedFilter implements FilterInterface
{
    public static function apply(Builder $builder, $value): Builder
    {
        if ($value === 'only') {
            return $builder->onlyTrashed();
        }

        return $builder;
    }
}
