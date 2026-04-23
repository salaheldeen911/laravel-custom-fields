<?php

namespace Salah\LaravelCustomFields\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Salah\LaravelCustomFields\Filters\CustomFieldFilters\ModelFilter;
use Salah\LaravelCustomFields\Filters\CustomFieldFilters\RequiredFilter;
use Salah\LaravelCustomFields\Filters\CustomFieldFilters\SearchFilter;
use Salah\LaravelCustomFields\Filters\CustomFieldFilters\TrashedFilter;
use Salah\LaravelCustomFields\Filters\CustomFieldFilters\TypeFilter;
use Salah\LaravelCustomFields\Models\CustomField;

class FilterEngine
{
    protected array $filters = [
        'search' => SearchFilter::class,
        'model' => ModelFilter::class,
        'type' => TypeFilter::class,
        'required' => RequiredFilter::class,
        'trashed' => TrashedFilter::class,
    ];

    private Model $model;

    public function __construct()
    {
        $this->model = new CustomField;
    }

    public function registerFilter(string $name, string $filterClass): void
    {
        $this->filters[$name] = $filterClass;
    }

    public function apply(array $filters): Builder
    {
        $query = $this->model->newQuery();

        foreach ($filters as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (isset($this->filters[$name])) {
                $filterClass = $this->filters[$name];
                $query = $filterClass::apply($query, $value);
            }
        }

        return $query;
    }
}
