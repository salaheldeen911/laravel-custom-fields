<?php

namespace Salah\LaravelCustomFields\DTOs;

readonly class CustomFieldDTO
{
    public function __construct(
        public string $name,
        public string $model,
        public string $type,
        public bool $required = false,
        public ?string $placeholder = null,
        public ?array $options = null,
        public ?array $validation_rules = null,
        public ?string $slug = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            model: $data['model'],
            type: $data['type'],
            required: $data['required'] ?? false,
            placeholder: $data['placeholder'] ?? null,
            options: $data['options'] ?? null,
            validation_rules: $data['validation_rules'] ?? null,
            slug: $data['slug'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'model' => $this->model,
            'type' => $this->type,
            'required' => $this->required,
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'validation_rules' => $this->validation_rules,
            'slug' => $this->slug,
        ], fn($value, $key) => $key !== 'slug' || ! is_null($value), ARRAY_FILTER_USE_BOTH);
    }
}
