<?php

namespace Salah\LaravelCustomFields\ValidationRules;

use Salah\LaravelCustomFields\Contracts\HasOptions;

class MimesRule extends ValidationRule implements HasOptions
{
    public function name(): string
    {
        return 'mimes';
    }

    public function label(): string
    {
        return 'Allowed Files (Mimes)';
    }

    public function htmlTag(): string
    {
        return 'select';
    }

    public function htmlAttribute(): string
    {
        return '';
    }

    public function description(): string
    {
        return 'Select the type of files allowed.';
    }

    public function baseRule(): array
    {
        return ['string'];
    }

    public function options(): array
    {
        return [
            ['value' => 'jpg,jpeg,png,webp,gif,svg', 'label' => 'Images (jpg, png, webp, etc)'],
            ['value' => 'pdf,doc,docx,txt', 'label' => 'Documents (pdf, word, txt)'],
            ['value' => 'xls,xlsx,csv', 'label' => 'Spreadsheets (excel, csv)'],
            ['value' => 'mp3,wav,ogg', 'label' => 'Audio (mp3, wav)'],
            ['value' => 'mp4,avi,mov,webm', 'label' => 'Video (mp4, avi)'],
            ['value' => 'zip,rar,7z', 'label' => 'Archives (zip, rar)'],
            ['value' => '*', 'label' => 'All Files (*)'],
        ];
    }

    public function apply($value): string
    {
        if ($value === '*') {
            return ''; // No mime restriction
        }

        return "mimes:$value";
    }
}
