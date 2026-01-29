<?php

namespace Salah\LaravelCustomFields\DTOs;

class ValidationRuleMeta
{
    public function __construct(
        public string $name,
        public string $label,
        public string $component,
        public string $type,
        public array $config_rules,
        public ?string $placeholder = null,
        public ?string $description = null,
        public array $options = [],
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'description' => $this->description,
            'ui' => [
                'component' => $this->component,
                'type' => $this->type,
                'placeholder' => $this->placeholder,
                'options' => $this->options,
            ],
            'validation' => [
                'rule' => $this->name,
                'config_rules' => $this->config_rules,
            ],
        ];
    }
}
