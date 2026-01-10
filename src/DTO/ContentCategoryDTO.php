<?php

namespace LiviuVoica\LbCms\DTO;

use LiviuVoica\LbCms\Models\ContentCategory;

final class ContentCategoryDTO
{
    /**
     * @param array{id: int, content_category_id: int, visibility: string, type: string, url: string, title: string} $content
     * @param array{id: int, full_name: string} $user
     */
    private function __construct(
        public int $id,
        public string $value,
        public bool $is_active,
        public array $content,
        public int $user_id,
        public array $user,
        public string $created_at,
        public string $updated_at
    ) {}

    public static function fromModel(ContentCategory $model): self
    {
        return new self(
            id: $model->id,
            value: $model->value,
            is_active: $model->is_active,
            content: $model->content,
            user_id: $model->user_id,
            user: $model->user,
            created_at: $model->created_at->toDateTimeString(),
            updated_at: $model->updated_at->toDateTimeString()
        );
    }
}
