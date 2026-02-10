<?php

namespace Salah\LaravelCustomFields\Services;

use Illuminate\Support\Facades\Cache;
use libphonenumber\PhoneNumberUtil;

class CountryService
{
    /**
     * Get all countries supported by libphonenumber, with names.
     */
    public static function getAll(): array
    {
        // Cache forever (or until manually cleared) as country codes don't change often.
        return Cache::remember('custom_fields_countries_list', now()->addYear(), function () {
            $util = PhoneNumberUtil::getInstance();
            $regions = $util->getSupportedRegions();

            $countries = [];
            foreach ($regions as $region) {
                // Get the English name for the region
                // Using 'en' locale explicitly for consistency.
                $name = \Locale::getDisplayRegion('-'.$region, 'en');

                // Fallback if name is empty or same as code (rare but possible)
                if (empty($name) || $name === $region) {
                    $name = $region;
                }

                $countries[] = [
                    'value' => $region,
                    'label' => $name." ({$region})",
                    // Adding simple search keywords
                    'keywords' => strtolower($name.' '.$region),
                ];
            }

            // Sort alphabetically by label
            usort($countries, fn ($a, $b) => strcmp($a['label'], $b['label']));

            return $countries;
        });
    }
}
