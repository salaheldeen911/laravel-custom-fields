<?php

namespace Salah\LaravelCustomFields\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    /**
     * Apply a given search value to the builder instance.
     *
     * @param  mixed  $value
     */
    public static function apply(Builder $builder, $value): Builder;
}
