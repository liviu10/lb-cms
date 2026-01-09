<?php

namespace LiviuVoica\LbCms\DTO;

final class ContentPayloadDTO
{
    /** @param array<string> $tags */
    private function __construct(
        public int $content_category_id,
        public string $visibility,
        public string $type,
        public string $scheduled_on,
        public array $tags,
        public string $title,
        public bool $allow_comments,
        public bool $allow_share,
    ) {}

    /**
     * @param array{
     *   content_category_id: int,
     *   visibility: string,
     *   type: string,
     *   scheduled_on: string,
     *   tags: array<string>,
     *   title: string,
     *   allow_comments: bool,
     *   allow_share: bool,
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
            allow_comments: $payload['allow_comments'],
            allow_share: $payload['allow_share'],
        );
    }
}
