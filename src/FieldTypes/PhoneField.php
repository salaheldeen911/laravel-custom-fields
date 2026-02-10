<?php

namespace Salah\LaravelCustomFields\FieldTypes;

use Salah\LaravelCustomFields\ValidationRules\LandlineRule;
use Salah\LaravelCustomFields\ValidationRules\MobileRule;
use Salah\LaravelCustomFields\ValidationRules\PhoneRule;

class PhoneField extends FieldType
{
    public function name(): string
    {
        return 'phone';
    }

    public function label(): string
    {
        return 'Phone Number';
    }

    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlAttribute(): string
    {
        return 'tel';
    }

    public function description(): string
    {
        return 'A field for entering and validating phone numbers.';
    }

    public function baseRule(): array
    {
        return ['string'];
    }

    public function allowedRules(): array
    {
        return [
            new PhoneRule,
            new MobileRule,
            new LandlineRule,
        ];
    }

    public function view(): string
    {
        return 'custom-fields::components.types.phone';
    }
}
