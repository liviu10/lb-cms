<?php

namespace LiviuVoica\LbCms\DTO;

final class ContentPayloadDTO
{
    /** 
     * @param array<string> $tags
     * @param array<int, array{
     *     original_name: string,
     *     client_mime_type: string,
     *     size: int,
     *     error: int,
     *     temporary_path: string,
     *     extension: string,
     *     guessed_extension: string,
     *     hash_name: string,
     *     is_valid: bool,
     * }> $content_media_files
     */
    private function __construct(
        public int $content_category_id,
        public string $visibility,
        public string $type,
        public string $scheduled_on,
        public array $tags,
        public string $title,
        public string|null $content,
        public array|null $content_media_files
    ) {}

    /**
     * @param array{
     *   content_category_id: int,
     *   visibility: string,
     *   type: string,
     *   scheduled_on: string,
     *   tags: array<string>,
     *   title: string,
     *   content: string|null,
     *   content_media_files: array<int, array{
     *     original_name: string,
     *     client_mime_type: string,
     *     size: int,
     *     error: int,
     *     temporary_path: string,
     *     extension: string,
     *     guessed_extension: string,
     *     hash_name: string,
     *     is_valid: bool,
     *   }>,
     * } $payload
     */
    public static function fromRequest(array $payload): self
    {
        return new self(
            content_category_id: $payload['content_category_id'],
            visibility: $payload['visibility'],
            type: $payload['type'],
            scheduled_on: $payload['scheduled_on'],
            tags: $payload['tags'],
            title: $payload['title'],
            content: $payload['content'] ?? null,
            content_media_files: $payload['content_media_files'] ?? null,
        );
    }
}
