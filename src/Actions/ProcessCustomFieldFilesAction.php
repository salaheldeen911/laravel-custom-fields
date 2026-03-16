<?php

namespace Salah\LaravelCustomFields\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProcessCustomFieldFilesAction
{
    /**
     * Handle file uploads if present in the value.
     */
    public function prepareValueForStorage($value, ?string $oldValue = null): mixed
    {
        if (is_array($value) && ! empty($value) && $value[0] instanceof UploadedFile) {
            throw new \InvalidArgumentException('Multiple file upload is not supported.');
        }

        if ($value instanceof UploadedFile) {
            if ($oldValue && config('custom-fields.files.cleanup', true)) {
                $this->deleteFile($oldValue);
            }

            return json_encode($this->storeFileItem($value));
        }

        if (is_string($value) && config('custom-fields.security.sanitize_html', true)) {
            $value = strip_tags($value);
        }

        return is_array($value) ? json_encode($value) : $value;
    }

    protected function storeFileItem(UploadedFile $file): array
    {
        $disk = config('custom-fields.files.disk', 'public');
        $folder = config('custom-fields.files.path', 'custom-fields');
        $path = $file->store($folder, $disk);

        return [
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function deleteFile(string $value): void
    {
        $data = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $disk = config('custom-fields.files.disk', 'public');

        if (isset($data['path']) && Storage::disk($disk)->exists($data['path'])) {
            Storage::disk($disk)->delete($data['path']);
        }
    }

    public function cleanupFilesForModel(Model $model): void
    {
        $values = $model->customFieldsValues()
            ->whereHas('customField', function ($q) {
                $q->where('type', 'file');
            })
            ->get();

        foreach ($values as $fieldValue) {
            $this->deleteFile($fieldValue->getAttributes()['value']);
        }
    }
}
