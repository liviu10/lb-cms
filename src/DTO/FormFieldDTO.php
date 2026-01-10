<?php

namespace LiviuVoica\LbCms\DTO;

final class FormFieldDTO
{
    /**
     * @param  array<int, array{value:int|string,label:string}>|null  $options
     */
    private function __construct(
        public string $key,
        public string $type,
        public string|bool|null $value,
        public ?array $options,
    ) {}

    /**
     * @param array{
     *     key: string,
     *     type: string,
     *     value?: string|bool|null,
     *     options?: array<int, array{value: int|string,label: string}>|null
     * } $field
     */
    public static function fromArray(array $field): self
    {
        return new self(
            key: $field['key'],
            type: $field['type'],
            value: $field['value'] ?? null,
            options: $field['options'] ?? null,
        );
    }
}
