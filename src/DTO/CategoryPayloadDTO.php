<?php

namespace LiviuVoica\LbCms\DTO;

final class CategoryPayloadDTO
{
    /**
     * @param  array<string, string>  $value
     */
    private function __construct(
        public string $key,
        public array $value,
        public bool $is_active,
    ) {}

    /**
     * @param array{
     *   key: string,
     *   value: array<string, string>,
     *   is_active?: bool,
     * } $payload
     */
    public static function fromRequest(array $payload): self
    {
        return new self(
            key: $payload['key'],
            value: $payload['value'],
            is_active: $payload['is_active'] ?? false,
        );
    }
}
