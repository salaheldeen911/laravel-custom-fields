<?php

namespace Salah\LaravelCustomFields\DTOs;

use Salah\LaravelCustomFields\Contracts\ConfigurableElement;

class ElementMeta
{
    public function __construct(
        protected ConfigurableElement $element,
        protected array $baseRules = [],
        protected array $additionalData = [],
        protected ?string $component = null
    ) {}

    public function toArray(): array
    {
        $rules = $this->baseRules ?: $this->element->baseRule();
        $serializableRules = array_values(array_filter($rules, fn($r) => !($r instanceof \Closure)));

        return array_merge([
            'name' => $this->element->name(),
            'label' => $this->element->label(),
            'description' => $this->element->description(),
            'ui' => [
                'tag' => $this->element->htmlTag(),
                'type' => $this->element->htmlType(),
                'placeholder' => $this->element->placeholder(),
                'options' => $this->element->options(),
            ],
            'validation' => [
                'config_rules' => $serializableRules,
            ],
        ], $this->additionalData);
    }
}
