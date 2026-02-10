<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Illuminate\Support\Facades\Storage;
use Salah\LaravelCustomFields\ValidationRules\MaxFileSizeRule;
use Salah\LaravelCustomFields\ValidationRules\MimesRule;

class FileField extends FieldType
{
    public function name(): string
    {
        return 'file';
    }

    public function label(): string
    {
        return 'File Upload';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'file';
    }

    public function description(): string
    {
        return 'A file upload field with support for validation and secure storage.';
    }

    public function baseRule(): array
    {
        return ['file'];
    }

    public function allowedRules(): array
    {
        return [
            new MimesRule,
            new MaxFileSizeRule,
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.file';
    }

    /**
     * Format the stored value (which is JSON metadata) to include a helper URL.
     */
    public function formatValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $data = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $value;
            }

            $disk = config('custom-fields.files.disk', 'public');

            // Handle Single File
            if (is_array($data) && isset($data['path'])) {
                $data['url'] = Storage::disk($disk)->url($data['path']);

                return $data;
            }
        }

        return $value;
    }
}
