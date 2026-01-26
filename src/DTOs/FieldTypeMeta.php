<?php

namespace Salah\LaravelCustomFields\DTOs;

class FieldTypeMeta
{
    public function __construct(
        public string $name,
        public string $label,
        public string $base_rule,
        public bool $has_options,
        public array $allowed_rules,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'base_rule' => $this->base_rule,
            'has_options' => $this->has_options,
            'allowed_rules' => $this->allowed_rules,
        ];
    }
}
