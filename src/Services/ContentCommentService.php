<?php

namespace LiviuVoica\LbCms\Services;

use LiviuVoica\LbCms\DTO\FormFieldDTO;
use LiviuVoica\LbCms\Enums\ContentCommentStatus;
use LiviuVoica\LbCms\Models\ContentComment;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;

class ContentCommentService
{
    use BuildFormTrait, DesiredFieldsTrait, FilterAndOrderTrait;

    /** @var ContentComment The content comment model instance. */
    private ContentComment $contentComment;

    /**
     * Transfers ownership of all content comments from one user to another.
     *
     * @see ContentComment
     */
    public static function transferContentCommentOwnership(int $oldUserId, int $defaultUserId): void
    {
        ContentComment::where('user_id', $oldUserId)->update(['user_id' => $defaultUserId]);
    }

    /**
     * Create a new service instance.
     *
     * @see ContentComment
     */
    public function __construct(ContentComment $contentComment)
    {
        $this->contentComment = $contentComment;
    }

    /**
     * Show the create form.
     *
     * @return FormFieldDTO[]
     *
     * @see FormFieldDTO
     */
    public function create(): array
    {
        $desiredFields = ['full_name', 'email', 'message', 'privacy_policy', 'terms_and_conditions'];

        $form = $this->buildForm($this->contentComment, $this->getFields($this->contentComment, $desiredFields));

        return $form;
    }

    /**
     * Create a new content comment.
     * @param array{full_name: string, email: string, message: string, privacy_policy?: bool, terms_and_conditions?: bool} $payload
     */
    public function store(array $payload): int
    {
        $data = [
            'content_id' => null,
            'status' => ContentCommentStatus::PENDING,
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'message' => $payload['message'],
            'privacy_policy' => $payload['privacy_policy'],
            'terms_and_conditions' => $payload['terms_and_conditions'],
            'user_id' => null,
        ];

        $contentComment = ContentComment::create($data);

        return $contentComment->id;
    }

    /**
     * Show the edit form.
     *
     * @return FormFieldDTO[]
     *
     * @see FormFieldDTO
     */
    public function edit(int $contentCommentId): array
    {
        $desiredFields = ['status', 'message'];

        $fields = $this->getFields($this->contentComment, $desiredFields);

        $form = $this->buildForm($this->contentComment, $fields);

        $contentComment = $this->contentComment->select($fields)->findOrFail($contentCommentId);

        foreach ($form as $field) {
            $field->value = $contentComment->{$field->key};
        }

        return $form;
    }

    /**
     * Update a content comment.
     * @param array{status: array<string, string>, message: bool} $payload
     */
    public function update(array $payload, int $contentCommentId): ?int
    {
        $contentComment = $this->contentComment->find($contentCommentId);
        if (! $contentComment) {
            return null;
        }

        $data = [
            'status' => $payload['status'] ?? $contentComment->status,
            'message' => $payload['message'] ?? $contentComment->message,
            'user_id' => (int) auth()->id(),
        ];
        $contentComment->update($data);

        return $contentComment->id;
    }

    /**
     * Delete a content comment.
     *
     * @param int $contentCommentId
     * @return bool|int True if deleted, false if not found, -1 if has content
     */
    public function destroy(int $contentCommentId): bool|int
    {
        $contentComment = $this->contentComment->find($contentCommentId);
        if (! $contentComment) {
            return false;
        }

        $contentComment->delete();

        return true;
    }
}
