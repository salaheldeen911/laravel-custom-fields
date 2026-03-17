<?php

namespace Salah\LaravelCustomFields\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator as ValidationValidator;
use Salah\LaravelCustomFields\Exceptions\ValidationIntegrityException;
use Salah\LaravelCustomFields\Repositories\CustomFieldRepositoryInterface;
use Salah\LaravelCustomFields\Actions\SetupValidationRulesAction;
use Salah\LaravelCustomFields\Actions\ValidateCustomFieldsAction;
use Salah\LaravelCustomFields\Actions\StoreCustomFieldValuesAction;
use Salah\LaravelCustomFields\Actions\ProcessCustomFieldFilesAction;
use Salah\LaravelCustomFields\ValidationRuleRegistry;

class CustomFieldsService
{
    protected bool $validated = false;

    public function __construct(
        protected CustomFieldRepositoryInterface $repository,
        protected SetupValidationRulesAction $setupRulesAction,
        protected ValidateCustomFieldsAction $validateAction,
        protected StoreCustomFieldValuesAction $storeAction,
        protected ProcessCustomFieldFilesAction $processFilesAction
    ) {}

    /**
     * Get rules for custom fields associated with the model.
     */
    public function getValidationRules(string $modelClass): array
    {
        return $this->setupRulesAction->execute($modelClass);
    }

    /**
     * Validate the request data for custom fields.
     */
    public function validate(string $modelClass, array $data): ValidationValidator
    {
        return $this->validateAction->execute($modelClass, $data, function () {
            $this->markAsValidated();
        });
    }

    /**
     * Mark a data set as successfully validated.
     */
    public function markAsValidated(): void
    {
        $this->validated = true;
    }

    /**
     * Check if the data set has been validated.
     */
    public function isValidated(): bool
    {
        return $this->validated;
    }

    /**
     * Store custom field values for a model instance.
     */
    public function storeValues(Model $model, array $data): void
    {
        $this->ensureDataIsValidated();

        $this->storeAction->execute($model, $data);
    }

    /**
     * Update custom field values for a model instance.
     */
    public function updateValues(Model $model, array $data): void
    {
        $this->ensureDataIsValidated();

        $this->storeAction->update($model, $data);
    }

    /**
     * Delete all files associated with a model's custom fields.
     */
    public function cleanupFilesForModel(Model $model): void
    {
        $this->processFilesAction->cleanupFilesForModel($model);
    }



    /**
     * Ensure the data has been validated before processing.
     */
    protected function ensureDataIsValidated(): void
    {
        if (! config('custom-fields.strict_validation', true)) {
            return;
        }

        if (! $this->isValidated()) {
            throw ValidationIntegrityException::unvalidatedData();
        }
    }

    /**
     * Reset the validation state.
     */
    public function reset(): void
    {
        $this->validated = false;
    }
}
