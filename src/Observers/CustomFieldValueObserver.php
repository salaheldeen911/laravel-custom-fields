<?php

namespace Salah\LaravelCustomFields\Observers;

use Salah\LaravelCustomFields\Actions\ProcessCustomFieldFilesAction;
use Salah\LaravelCustomFields\Models\CustomFieldValue;

class CustomFieldValueObserver
{
    public function __construct(
        protected ProcessCustomFieldFilesAction $processFilesAction
    ) {}

    public function deleted(CustomFieldValue $customFieldValue): void
    {
        // Only cleanup if the related custom field is a file type
        $customField = $customFieldValue->customField;

        if (! $customField || $customField->type !== 'file') {
            return;
        }

        if (! config('custom-fields.files.cleanup', true)) {
            return;
        }

        $value = $customFieldValue->getAttributes()['value'] ?? null;

        if ($value) {
            $this->processFilesAction->deleteFile($value);
        }
    }
}
