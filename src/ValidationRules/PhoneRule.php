<?php

namespace Salah\LaravelCustomFields\ValidationRules;

class PhoneRule implements ValidationRule
{
    public function name(): string
    {
        return 'phone';
    }

    public function label(): string
    {
        return 'Phone Format (e.g., US,EG,mobile)';
    }

    public function configRule()
    {
        return ['nullable', 'string'];
    }

    public function inputType(): string
    {
        return 'text';
    }

    public function placeholder(): ?string
    {
        return 'e.g., US,EG,mobile';
    }

    public function description(): ?string
    {
        return 'Validates the phone number format. Leave empty for automatic detection.';
    }

    public function options(): array
    {
        return [];
    }

    public function apply($value): string
    {
        if (empty($value)) {
            return 'phone:AUTO';
        }

        return "phone:{$value}";
    }
}
