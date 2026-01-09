<?php

namespace LiviuVoica\LbCms\Services;

use LiviuVoica\LbCms\DTO\ContentCategoryPaginatedDTO;
use LiviuVoica\LbCms\DTO\ContentCategoryPayloadDTO;
use LiviuVoica\LbCms\DTO\FormFieldDTO;
use LiviuVoica\LbCms\Models\ContentCategory;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;

class ContentCategoryService
{
    use BuildFormTrait, DesiredFieldsTrait, FilterAndOrderTrait;

    /** @var ContentCategory The content category model instance. */
    private ContentCategory $contentCategory;

    /**
     * @return array<int, array{value: int, label: string}> All active content categories.
     *
     * @see ContentCategory
     */
    public static function getAllActiveContentCategories(): array
    {
        $locale = (string) app()->getLocale();

        return ContentCategory::where('is_active', true)
            ->orderBy('id', 'asc')
            ->get(['id', 'value'])
            ->map(fn ($item) => [
                'value' => (int) $item->id,
                'label' => (string) (
                    isset($item->value[$locale])
                    ? $item->value[$locale]
                    : ($item->value['en'] ?? '')
                ),
            ])
            ->toArray();
    }

    /**
     * Transfers ownership of all content categories from one user to another.
     *
     *
     * @see ContentCategory
     */
    public static function transferContentCategoryOwnership(int $oldUserId, int $defaultUserId): void
    {
        ContentCategory::where('user_id', $oldUserId)->update(['user_id' => $defaultUserId]);
    }

    /**
     * Create a new service instance.
     *
     * @see ContentCategory
     */
    public function __construct(ContentCategory $contentCategory)
    {
        $this->contentCategory = $contentCategory;
    }

    /**
     * Get the list of content categories.
     *
     * @see ContentCategoryPaginatedDTO
     */
    public function index(): ContentCategoryPaginatedDTO
    {
        $desiredFields = ['id', 'key', 'value', 'is_active', 'user_id'];

        $fields = $this->getFields($this->contentCategory, $desiredFields);

        $form = $this->buildForm($this->contentCategory, $fields);
        $formOptions = [
            'key' => self::getAllActiveContentCategories(),
        ];
        foreach ($form as $field) {
            $key = $field->key;
            if (isset($formOptions[$key])) {
                $field->type = 'select';
                $field->options = $formOptions[$key];
            }
        }

        $paginator = $this->contentCategory->select($fields)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->paginate(25);

        return ContentCategoryPaginatedDTO::fromPaginator($paginator, $form);
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
        $desiredFields = ['key', 'value', 'is_active'];

        $form = $this->buildForm($this->contentCategory, $this->getFields($this->contentCategory, $desiredFields));

        return $form;
    }

    /**
     * Create a new content category.
     *
     * @see ContentCategoryPayloadDTO
     */
    public function store(ContentCategoryPayloadDTO $payload): int
    {
        $contentCategory = ContentCategory::create([
            'key' => $payload->key,
            'value' => $payload->value,
            'is_active' => $payload->is_active,
            'user_id' => (int) auth()->id(),
        ]);

        return $contentCategory->id;
    }

    /**
     * Show the edit form.
     *
     * @return FormFieldDTO[]
     *
     * @see FormFieldDTO
     */
    public function edit(int $contentCategoryId): array
    {
        $desiredFields = ['key', 'value', 'is_active'];

        $fields = $this->getFields($this->contentCategory, $desiredFields);

        $form = $this->buildForm($this->contentCategory, $fields);

        $contentCategory = $this->contentCategory->select($fields)->findOrFail($contentCategoryId);

        foreach ($form as $field) {
            $field->value = $contentCategory->{$field->key};
        }

        return $form;
    }

    /**
     * Update a content category.
     *
     * @see ContentCategoryPayloadDTO
     */
    public function update(ContentCategoryPayloadDTO $payload, int $contentCategoryId): ?int
    {
        $contentCategory = $this->contentCategory->find($contentCategoryId);
        if (! $contentCategory) {
            return null;
        }

        $contentCategory->update([
            'key' => $payload->key ?? $contentCategory->key,
            'value' => $payload->value ?? $contentCategory->value,
            'is_active' => $payload->is_active ?? $contentCategory->is_active,
            'user_id' => (int) auth()->id(),
        ]);

        return $contentCategory->id;
    }

    /**
     * Delete a content category.
     */
    public function destroy(int $contentCategoryId): bool
    {
        $contentCategory = $this->contentCategory->find($contentCategoryId);
        if (! $contentCategory) {
            return false;
        }

        $contentCategory->delete();

        return true;
    }
}
