<?php

namespace LiviuVoica\LbCms\DTO;

use LiviuVoica\LbCms\Enums\ContentType;
use LiviuVoica\LbCms\Enums\ContentVisibility;
use LiviuVoica\LbCms\Models\Content;

final class ContentDetailsDTO
{
    /**
     * @param array{value: int, label: string} $content_category
     * @param array<string> $tags
     * @param array<int, array{id: int, content_id: int, title: string, path: string}> $content_media
     * @param array<int, array{id: int, content_id: int, full_name: string, email: string, privacy_policy: bool, terms_and_conditions: bool}> $content_comments
     * @param array{id: int, full_name: string} $user
     */
    private function __construct(
        public int $id,
        public int $content_category_id,
        public array $content_category,
        public ContentVisibility $visibility,
        public ContentType $type,
        public string|null $scheduled_on,
        public string $url,
        public array $tags,
        public string $title,
        public array $content_media = [],
        public int $user_id,
        public array $user,
        public string $created_at,
        public string $updated_at
    ) {}

    public static function fromModel(Content $model): self
    {
        return new self(
            id: $model->id,
            content_category_id: $model->content_category_id,
            content_category: $model->content_category,
            visibility: $model->visibility,
            type: $model->type,
            scheduled_on: $model->scheduled_on->toDateTimeString(),
            url: $model->url,
            tags: $model->tags,
            title: $model->title,
            content_media: $model->media->map(fn($m) => [
                'title' => $m->title,
                'path' => $m->path,
            ])->toArray(),
            user_id: $model->user_id,
            user: $model->user,
            created_at: $model->created_at->toDateTimeString(),
            updated_at: $model->updated_at->toDateTimeString()
        );
    }
}
