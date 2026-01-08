<?php

namespace LiviuVoica\LbCms\DTO;

final class ContentVisibilityPayloadDTO
{
    /**
     * @param  array<string, string>  $value
     */
    private function __construct(
        public string $key,
        public array $value,
    ) {}

    /**
     * @param array{
     *   key: string,
     *   value: array<string, string>,
     * } $payload
     */
    public static function fromRequest(array $payload): self
    {
        return new self(
            key: $payload['key'],
            value: $payload['value'],
        );
    }
}
