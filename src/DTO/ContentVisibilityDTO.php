<?php

namespace LiviuVoica\LbCms\DTO;

use LiviuVoica\LbCms\Models\ContentVisibility;

final class ContentVisibilityDTO
{
    /**
     * @param array{
     *     id: int,
     *     full_name: string
     * } $user
     */
    private function __construct(
        public int $id,
        public string $key,
        public string $value,
        public int $user_id,
        public array $user,
        public string $created_at,
        public string $updated_at
    ) {}

    public static function fromModel(ContentVisibility $model): self
    {
        return new self(
            id: $model->id,
            key: $model->key,
            value: $model->value,
            user_id: $model->user_id,
            user: $model->user,
            created_at: $model->created_at->toDateTimeString(),
            updated_at: $model->updated_at->toDateTimeString()
        );
    }
}
