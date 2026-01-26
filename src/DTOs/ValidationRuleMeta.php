<?php

namespace Salah\LaravelCustomFields\DTOs;

class ValidationRuleMeta
{
    public function __construct(
        public string $name,
        public string $label,
        public string $type,
        public ?string $placeholder = null,
        public ?string $description = null,
        public array $options = [],
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'placeholder' => $this->placeholder,
            'description' => $this->description,
            'options' => $this->options,
        ];
    }
}
