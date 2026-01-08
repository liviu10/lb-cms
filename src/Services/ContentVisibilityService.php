<?php

namespace LiviuVoica\LbCms\Services;

use LiviuVoica\LbCms\DTO\ContentVisibilityPaginatedDTO;
use LiviuVoica\LbCms\DTO\ContentVisibilityPayloadDTO;
use LiviuVoica\LbCms\DTO\FormFieldDTO;
use LiviuVoica\LbCms\Models\ContentVisibility;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;

class ContentVisibilityService
{
    use BuildFormTrait, DesiredFieldsTrait, FilterAndOrderTrait;

    /** @var ContentVisibility The content visibility model instance. */
    private ContentVisibility $contentVisibility;

    /**
     * @return array<int, array{value: int, label: string}> All active content visibilities.
     *
     * @see ContentVisibility
     */
    public static function getAllActiveContentVisibilities(): array
    {
        $locale = (string) app()->getLocale();

        return ContentVisibility::orderBy('id', 'asc')
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
     * Transfers ownership of all content visibilities from one user to another.
     *
     *
     * @see ContentVisibility
     */
    public static function transferContentVisibilityOwnership(int $oldUserId, int $defaultUserId): void
    {
        ContentVisibility::where('user_id', $oldUserId)->update(['user_id' => $defaultUserId]);
    }

    /**
     * Create a new service instance.
     *
     * @see ContentVisibility
     */
    public function __construct(ContentVisibility $contentVisibility)
    {
        $this->contentVisibility = $contentVisibility;
    }

    /**
     * Get the list of content visibilities.
     *
     * @see ContentVisibilityPaginatedDTO
     */
    public function index(): ContentVisibilityPaginatedDTO
    {
        $desiredFields = ['id', 'key', 'value', 'user_id'];

        $fields = $this->getFields($this->contentVisibility, $desiredFields);

        $form = $this->buildForm($this->contentVisibility, $fields);
        $formOptions = [
            'key' => self::getAllActiveContentVisibilities(),
        ];
        foreach ($form as $field) {
            $key = $field->key;
            if (isset($formOptions[$key])) {
                $field->type = 'select';
                $field->options = $formOptions[$key];
            }
        }

        $paginator = $this->contentVisibility->select($fields)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->paginate(25);

        return ContentVisibilityPaginatedDTO::fromPaginator($paginator, $form);
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
        $desiredFields = ['key', 'value'];

        $form = $this->buildForm($this->contentVisibility, $this->getFields($this->contentVisibility, $desiredFields));

        return $form;
    }

    /**
     * Create a new content visibility.
     *
     * @see ContentVisibilityPayloadDTO
     */
    public function store(ContentVisibilityPayloadDTO $payload): int
    {
        $contentVisibility = ContentVisibility::create([
            'key' => $payload->key,
            'value' => $payload->value,
            'user_id' => (int) auth()->id(),
        ]);

        return $contentVisibility->id;
    }

    /**
     * Show the edit form.
     *
     * @return FormFieldDTO[]
     *
     * @see FormFieldDTO
     */
    public function edit(int $contentVisibilityId): array
    {
        $desiredFields = ['key', 'value'];

        $fields = $this->getFields($this->contentVisibility, $desiredFields);

        $form = $this->buildForm($this->contentVisibility, $fields);

        $contentVisibility = $this->contentVisibility->select($fields)->findOrFail($contentVisibilityId);

        foreach ($form as $field) {
            $field->value = $contentVisibility->{$field->key};
        }

        return $form;
    }

    /**
     * Update a content visibility.
     *
     * @see ContentVisibilityPayloadDTO
     */
    public function update(ContentVisibilityPayloadDTO $payload, int $contentVisibilityId): ?int
    {
        $contentVisibility = $this->contentVisibility->find($contentVisibilityId);
        if (! $contentVisibility) {
            return null;
        }

        $contentVisibility->update([
            'key' => $payload->key ?? $contentVisibility->key,
            'value' => $payload->value ?? $contentVisibility->value,
            'user_id' => (int) auth()->id(),
        ]);

        return $contentVisibility->id;
    }
}
