<?php

namespace Salah\LaravelCustomFields\ValidationRules;

abstract class AbstractValidationRule implements ValidationRule
{
    /**
     * {@inheritdoc}
     */
    abstract public function name(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function label(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function baseRule();

    /**
     * {@inheritdoc}
     */
    public function htmlTag(): string
    {
        return 'input';
    }

    public function htmlType(): string
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function description(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function options(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    abstract public function apply($value): string;
}
