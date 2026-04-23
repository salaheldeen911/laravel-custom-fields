<?php

namespace Salah\LaravelCustomFields\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Salah\LaravelCustomFields\DTOs\CustomFieldDTO;
use Salah\LaravelCustomFields\Models\CustomField;

interface CustomFieldRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findById(string|int $id, bool $withTrashed = false): CustomField;

    public function store(CustomFieldDTO $data): CustomField;

    public function update(string|int $id, CustomFieldDTO $data): CustomField;

    public function delete(string|int $id): bool;

    public function restore(string|int $id): CustomField;

    public function forceDelete(string|int $id): bool;

    public function getStats(): array;

    public function getByModel(string $modelAlias): Collection;

    public function getByModelAndSlugs(string $modelAlias, array $slugs): Collection;
}
