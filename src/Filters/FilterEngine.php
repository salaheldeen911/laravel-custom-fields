<?php

namespace Salah\LaravelCustomFields\Filters;

use Salah\LaravelCustomFields\Models\CustomField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FilterEngine
{
    private Model $model;

    public function __construct()
    {
        $this->model = new CustomField;
    }

    public function apply(array $filters)
    {
        $query = static::applyDecoratorsFromRequest($filters, ($this->model)->newQuery());

        return static::getResults($query);
    }

    private static function applyDecoratorsFromRequest(array $filters, Builder $query)
    {
        foreach ($filters as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $decorator = static::createFilterDecorator($name);

            if (static::isValidDecorator($decorator)) {
                $query = $decorator::apply($query, $value);
            }
        }

        return $query;
    }

    private static function createFilterDecorator($name)
    {
        return __NAMESPACE__.'\\CustomFieldFilters\\'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name))).'Filter';
    }

    private static function isValidDecorator($decorator)
    {
        return class_exists($decorator) && is_subclass_of($decorator, FilterInterface::class);
    }

    private static function getResults(Builder $query)
    {
        return $query;
    }
}
