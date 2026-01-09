<?php

namespace LiviuVoica\LbCms\DTO;

final class ContentPayloadDTO
{
    private function __construct(
        public int $content_visibility_id,
        public int $content_category_id,
        public string $type,
        public string $scheduled_on,
        public string $title,
        public bool $allow_comments,
        public bool $allow_share,
    ) {}

    /**
     * @param array{
     *   content_visibility_id: int,
     *   content_category_id: int,
     *   type: string,
     *   scheduled_on: string,
     *   title: string,
     *   allow_comments: bool,
     *   allow_share: bool,
     * } $payload
     */
    public static function fromRequest(array $payload): self
    {
        return new self(
            content_visibility_id: $payload['content_visibility_id'],
            content_category_id: $payload['content_category_id'],
            type: $payload['type'],
            scheduled_on: $payload['scheduled_on'],
            title: $payload['title'],
            allow_comments: $payload['allow_comments'],
            allow_share: $payload['allow_share'],
        );
    }
}
