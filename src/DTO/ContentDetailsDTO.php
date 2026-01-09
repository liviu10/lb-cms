<?php

namespace LiviuVoica\LbCms\DTO;

use LiviuVoica\LbCms\Enums\ContentType;
use LiviuVoica\LbCms\Models\Content;

final class ContentDetailsDTO
{
    /**
     * @param array{value: int, label: string} $content_visibility
     * @param array{value: int, label: string} $content_category
     * @param array{id: int, full_name: string} $user
     */
    private function __construct(
        public int $id,
        public int $content_visibility_id,
        public array $content_visibility,
        public int $content_category_id,
        public array $content_category,
        public ContentType $type,
        public string|null $scheduled_on,
        public string $url,
        public string $title,
        public int $user_id,
        public array $user,
        public string $created_at,
        public string $updated_at
    ) {}

    public static function fromModel(Content $model): self
    {
        return new self(
            id: $model->id,
            content_visibility_id: $model->content_visibility_id,
            content_visibility: $model->content_visibility,
            content_category_id: $model->content_category_id,
            content_category: $model->content_category,
            type: $model->type,
            scheduled_on: $model->scheduled_on->toDateTimeString(),
            url: $model->url,
            title: $model->title,
            user_id: $model->user_id,
            user: $model->user,
            created_at: $model->created_at->toDateTimeString(),
            updated_at: $model->updated_at->toDateTimeString()
        );
    }
}
