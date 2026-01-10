<?php

namespace LiviuVoica\LbCms\Services;

use Exception;
use LiviuVoica\LbCms\DTO\ContentCategoryDTO;
use LiviuVoica\LbCms\DTO\PaginatedDTO;
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
            ->map(fn($item) => [
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
     * @return PaginatedDTO<ContentCategoryDTO>
     * @see PaginatedDTO
     * @see ContentCategoryDTO
     */
    public function index(): PaginatedDTO
    {
        $desiredFields = ['id', 'value', 'is_active', 'user_id'];

        $fields = $this->getFields($this->contentCategory, $desiredFields);

        $form = $this->buildForm($this->contentCategory, $fields);
        $formOptions = [
            'key' => self::getAllActiveContentCategories(),
        ];
        $form = array_map(function (FormFieldDTO $field) use ($formOptions) {
            $key = $field->key;
            if (isset($formOptions[$key])) {
                return FormFieldDTO::fromArray([
                    'key' => $field->key,
                    'type' => 'select',
                    'value' => $field->value,
                    'options' => $formOptions[$key],
                ]);
            }
            return $field;
        }, $form);

        $paginator = $this->contentCategory->select($fields)
            ->with([
                'content' => function ($query) {
                    $query->select('id', 'content_category_id', 'visibility', 'type', 'url', 'title');
                },
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->paginate(25);

        return PaginatedDTO::fromPaginator(
            $paginator,
            $form,
            fn($item) => ContentCategoryDTO::fromModel($item)
        );
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
        $desiredFields = ['value', 'is_active'];

        $form = $this->buildForm($this->contentCategory, $this->getFields($this->contentCategory, $desiredFields));

        return $form;
    }

    /**
     * Create a new content category.
     * @param array{value: array<string, string>, is_active?: bool} $payload
     */
    public function store(array $payload): int
    {
        $data = [
            'value' => $payload['value'],
            'is_active' => $payload['is_active'],
        ];
        $data['user_id'] = (int) auth()->id();

        $contentCategory = ContentCategory::create($data);

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
        $desiredFields = ['value', 'is_active'];

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
     * @param array{value: array<string, string>, is_active?: bool} $payload
     */
    public function update(array $payload, int $contentCategoryId): ?int
    {
        $contentCategory = $this->contentCategory->find($contentCategoryId);
        if (! $contentCategory) {
            return null;
        }

        $data = [
            'value' => $payload['value'] ?? $contentCategory->value,
            'is_active' => $payload['is_active'] ?? $contentCategory->is_active,
        ];
        $data['user_id'] = (int) auth()->id();
        $contentCategory->update($data);

        return $contentCategory->id;
    }

    /**
     * Delete a content category.
     *
     * @param int $contentCategoryId
     * @return bool|int True if deleted, false if not found, -1 if has content
     */
    public function destroy(int $contentCategoryId): bool|int
    {
        $contentCategory = $this->contentCategory->find($contentCategoryId);
        if (! $contentCategory) {
            return false;
        }

        if ($contentCategory->content()->exists()) {
            return -1;
        }

        $contentCategory->delete();

        return true;
    }
}
