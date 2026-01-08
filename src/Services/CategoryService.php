<?php

namespace LiviuVoica\LbCms\Services;

use LiviuVoica\LbCms\DTO\CategoryPaginatedDTO;
use LiviuVoica\LbCms\DTO\CategoryPayloadDTO;
use LiviuVoica\LbCms\DTO\FormFieldDTO;
use LiviuVoica\LbCms\Models\Category;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;

class CategoryService
{
    use BuildFormTrait, DesiredFieldsTrait, FilterAndOrderTrait;

    /** @var Category The category model instance. */
    private Category $category;

    /**
     * @return array<int, array{value: int, label: string}> All active categories.
     *
     * @see Category
     */
    public static function getAllActiveCategories(): array
    {
        $locale = (string) app()->getLocale();

        return Category::where('is_active', true)
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
     * Transfers ownership of all categories from one user to another.
     *
     *
     * @see Category
     */
    public static function transferCategoryOwnership(int $oldUserId, int $defaultUserId): void
    {
        Category::where('user_id', $oldUserId)->update(['user_id' => $defaultUserId]);
    }

    /**
     * Create a new service instance.
     *
     * @see Category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Get the list of categories.
     *
     * @see CategoryPaginatedDTO
     */
    public function index(): CategoryPaginatedDTO
    {
        $desiredFields = ['id', 'key', 'value', 'is_active', 'user_id'];

        $fields = $this->getFields($this->category, $desiredFields);

        $form = $this->buildForm($this->category, $fields);
        $formOptions = [
            'key' => self::getAllActiveCategories(),
        ];
        foreach ($form as $field) {
            $key = $field->key;
            if (isset($formOptions[$key])) {
                $field->type = 'select';
                $field->options = $formOptions[$key];
            }
        }

        $paginator = $this->category->select($fields)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->paginate(25);

        return CategoryPaginatedDTO::fromPaginator($paginator, $form);
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

        $form = $this->buildForm($this->category, $this->getFields($this->category, $desiredFields));

        return $form;
    }

    /**
     * Create a new category.
     *
     * @see CategoryPayloadDTO
     */
    public function store(CategoryPayloadDTO $payload): int
    {
        $category = Category::create([
            'key' => $payload->key,
            'value' => $payload->value,
            'is_active' => $payload->is_active,
            'user_id' => (int) auth()->id(),
        ]);

        return $category->id;
    }

    /**
     * Show the edit form.
     *
     * @return FormFieldDTO[]
     *
     * @see FormFieldDTO
     */
    public function edit(int $categoryId): array
    {
        $desiredFields = ['key', 'value', 'is_active'];

        $fields = $this->getFields($this->category, $desiredFields);

        $form = $this->buildForm($this->category, $fields);

        $category = $this->category->select($fields)->findOrFail($categoryId);

        foreach ($form as $field) {
            $field->value = $category->{$field->key};
        }

        return $form;
    }

    /**
     * Update a category.
     *
     * @see CategoryPayloadDTO
     */
    public function update(CategoryPayloadDTO $payload, int $categoryId): ?int
    {
        $category = $this->category->find($categoryId);
        if (! $category) {
            return null;
        }

        $category->update([
            'key' => $payload->key ?? $category->key,
            'value' => $payload->value ?? $category->value,
            'is_active' => $payload->is_active ?? $category->is_active,
            'user_id' => (int) auth()->id(),
        ]);

        return $category->id;
    }

    /**
     * Delete a category.
     */
    public function destroy(int $categoryId): bool
    {
        $category = $this->category->find($categoryId);
        if (! $category) {
            return false;
        }

        $category->delete();

        return true;
    }
}
