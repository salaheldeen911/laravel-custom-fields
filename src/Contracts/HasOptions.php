<?php

namespace Salah\LaravelCustomFields\Contracts;

interface HasOptions
{
    /**
     * Optional predefined values for the element configuration.
     *
     * @return array<array{value: string, label: string}>
     */
    public function options(): array;
}
