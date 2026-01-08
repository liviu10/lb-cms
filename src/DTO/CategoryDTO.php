<?php

namespace LiviuVoica\LbCms\DTO;

use LiviuVoica\LbCms\Models\Category;

final class CategoryDTO
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
        public bool $is_active,
        public int $user_id,
        public array $user,
        public string $created_at,
        public string $updated_at
    ) {}

    public static function fromModel(Category $model): self
    {
        return new self(
            id: $model->id,
            key: $model->key,
            value: $model->value,
            is_active: $model->is_active,
            user_id: $model->user_id,
            user: $model->user,
            created_at: $model->created_at->toDateTimeString(),
            updated_at: $model->updated_at->toDateTimeString()
        );
    }
}
