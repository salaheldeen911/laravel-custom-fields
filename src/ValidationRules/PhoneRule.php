<?php

namespace Salah\LaravelCustomFields\ValidationRules;

use Salah\LaravelCustomFields\Contracts\HasOptions;
use Salah\LaravelCustomFields\Services\CountryService;

class PhoneRule extends ValidationRule implements HasOptions
{
    protected CountryService $countryService;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
    }

    public function name(): string
    {
        return 'phone';
    }

    public function label(): string
    {
        return 'Phone Format (e.g., US,EG,mobile)';
    }

    public function baseRule(): array
    {
        return ['array'];
    }

    public function htmlTag(): string
    {
        return 'select';
    }

    public function htmlAttribute(): string
    {
        return 'multiple';
    }

    public function placeholder(): string
    {
        return 'e.g., US,EG,mobile';
    }

    public function options(): array
    {
        return $this->countryService->getAll();
    }

    public function description(): string
    {
        return 'Select allowed countries (searchable).';
    }

    public function apply($value): string
    {
        if (empty($value)) {
            return 'phone';
        }

        if (is_array($value)) {
            $value = implode(',', $value);
        }

        return "phone:{$value}";
    }
}
