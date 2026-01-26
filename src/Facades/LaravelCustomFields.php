<?php

namespace Salah\LaravelCustomFields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Salah\LaravelCustomFields\LaravelCustomFields
 */
class LaravelCustomFields extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelCustomFields::class;
    }
}
