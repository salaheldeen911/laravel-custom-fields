<?php

namespace Salah\LaravelCustomFields\Contracts;

interface ConfigurableElement
{
    /**
     * The unique identifier for this element.
     */
    public function name(): string;

    /**
     * The human-readable label for the UI.
     */
    public function label(): string;

    /**
     * The HTML tag to be used on the frontend for configuring this element.
     */
    public function htmlTag(): string;

    /**
     * The type attribute for the HTML tag.
     */
    public function htmlAttribute(): string;

    /**
     * The placeholder for the UI input.
     */
    public function placeholder(): string;

    /**
     * A description of what this element does.
     */
    public function description(): string;

    /**
     * The base validation rule for this element.
     */
    public function baseRule(): array;
}
