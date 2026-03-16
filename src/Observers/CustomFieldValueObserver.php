<?php

namespace Salah\LaravelCustomFields\Observers;

use Salah\LaravelCustomFields\Models\CustomFieldValue;
use Salah\LaravelCustomFields\Actions\ProcessCustomFieldFilesAction;

class CustomFieldValueObserver
{
    public function __construct(
        protected ProcessCustomFieldFilesAction $processFilesAction
    ) {}

    public function deleted(CustomFieldValue $customFieldValue): void
    {
        // Check if value looks like a file metadata JSON
        $value = $customFieldValue->getAttributes()['value'] ?? null;
        if ($value && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
            if (! config('custom-fields.files.cleanup', true)) {
                return;
            }

            $this->processFilesAction->deleteFile($value);
        }
    }
}
