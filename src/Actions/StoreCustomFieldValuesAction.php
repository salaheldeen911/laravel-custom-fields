<?php

namespace Salah\LaravelCustomFields\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Salah\LaravelCustomFields\Models\CustomFieldValue;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;

class StoreCustomFieldValuesAction
{
    public function __construct(
        protected CustomFieldRepositoryInterface $repository,
        protected ProcessCustomFieldFilesAction $processFilesAction
    ) {}

    public function execute(Model $model, array $data): void
    {
        $this->upsertValues($model, $data, includeCreatedAt: true);
    }

    public function update(Model $model, array $data): void
    {
        $this->upsertValues($model, $data, includeCreatedAt: false);
    }

    private function upsertValues(Model $model, array $data, bool $includeCreatedAt): void
    {
        DB::transaction(function () use ($model, $data, $includeCreatedAt) {
            $modelAlias = $model::getCustomFieldModelAlias();
            $customFields = $this->repository->getByModelAndSlugs($modelAlias, array_keys($data))
                ->keyBy('slug');

            // Eager load existing values to avoid N+1 queries during file cleanup check
            $existingValues = CustomFieldValue::where('model_type', $model->getMorphClass())
                ->where('model_id', $model->getKey())
                ->whereIn('custom_field_id', $customFields->pluck('id'))
                ->get()
                ->keyBy('custom_field_id');

            $values = [];
            foreach ($data as $fieldSlug => $value) {
                $customField = $customFields->get($fieldSlug);

                if (! $customField) {
                    continue;
                }

                $oldValue = null;
                if ($value instanceof UploadedFile) {
                    $existing = $existingValues->get($customField->id);
                    $oldValue = $existing ? $existing->getAttributes()['value'] : null;
                }

                $entry = [
                    'custom_field_id' => $customField->id,
                    'model_id' => $model->getKey(),
                    'model_type' => $model->getMorphClass(),
                    'value' => $this->processFilesAction->prepareValueForStorage($value, $oldValue),
                    'updated_at' => now(),
                ];

                if ($includeCreatedAt) {
                    $entry['created_at'] = now();
                }

                $values[] = $entry;
            }

            if (! empty($values)) {
                CustomFieldValue::upsert(
                    $values,
                    ['custom_field_id', 'model_type', 'model_id'],
                    ['value', 'updated_at']
                );
            }
        });
    }
}
