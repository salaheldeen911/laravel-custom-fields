<?php

namespace Salah\LaravelCustomFields\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Salah\LaravelCustomFields\DTOs\CustomFieldDTO;
use Salah\LaravelCustomFields\Filters\FilterEngine;
use Salah\LaravelCustomFields\Models\CustomField;

class CustomFieldRepository implements CustomFieldRepositoryInterface
{
    public function __construct(
        protected FilterEngine $filterEngine
    ) {}

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->filterEngine
            ->apply($filters)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string|int $id, bool $withTrashed = false): CustomField
    {
        $query = CustomField::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->findOrFail($id);
    }

    public function store(CustomFieldDTO $data): CustomField
    {
        return CustomField::create($data->toArray());
    }

    public function update(string|int $id, CustomFieldDTO $data): CustomField
    {
        $field = $this->findById($id, true);
        $field->update($data->toArray());

        return $field;
    }

    public function delete(string|int $id): bool
    {
        return $this->findById($id)->delete();
    }

    public function restore(string|int $id): CustomField
    {
        $field = CustomField::onlyTrashed()->findOrFail($id);
        $field->restore();

        return $field;
    }

    public function forceDelete(string|int $id): bool
    {
        return CustomField::withTrashed()->findOrFail($id)->forceDelete();
    }

    public function getStats(): array
    {
        return CustomField::selectRaw('
            COUNT(*) as total,
            COUNT(DISTINCT model) as models,
            COUNT(DISTINCT type) as types,
            SUM(CASE WHEN required = 1 THEN 1 ELSE 0 END) as required
        ')->first()?->toArray() ?? [
            'total' => 0,
            'models' => 0,
            'types' => 0,
            'required' => 0,
        ];
    }

    public function getByModel(string $modelAlias): Collection
    {
        return CustomField::where('model', $modelAlias)->get();
    }

    public function getByModelAndSlugs(string $modelAlias, array $slugs): Collection
    {
        return CustomField::where('model', $modelAlias)
            ->whereIn('slug', $slugs)
            ->get();
    }
}
