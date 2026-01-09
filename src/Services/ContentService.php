<?php

namespace LiviuVoica\LbCms\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LiviuVoica\LbCms\DTO\ContentPaginatedDTO;
use LiviuVoica\LbCms\DTO\ContentPayloadDTO;
use LiviuVoica\LbCms\DTO\FormFieldDTO;
use LiviuVoica\LbCms\Models\Content;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;
use LiviuVoica\LbCms\Services\ContentVisibilityService;
use LiviuVoica\LbCms\Services\ContentCategoryService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use LiviuVoica\LbCms\DTO\ContentDetailsDTO;
use LiviuVoica\LbCms\Enums\ContentType;

class ContentService
{
    use BuildFormTrait, DesiredFieldsTrait, FilterAndOrderTrait;

    /** @var Content The content model instance. */
    private Content $content;

    /**
     * Retrieve the enum values for a given column of the contents table.
     *
     * @param  string  $columnName.  Available values status
     * @return array<int, array{value: string, label: string}>
     *
     * @see Content
     */
    public static function getContentEnum(string $columnName): array
    {
        $tableName = (new Content)->getTable();

        $result = DB::select("SHOW COLUMNS FROM {$tableName} WHERE Field = ?", [$columnName]);
        preg_match("/enum\((.*?)\)/", $result[0]->Type, $matches); // = returneaza int sau false

        $enumValues = isset($matches[1])
            ? (array) preg_split("/\s*,\s*/", trim($matches[1], "'"))
            : array_map(
                fn($case) => $case->value,
                ("App\\Enums\\Content" . ucfirst($columnName))::cases()
            );

        $cached = Cache::get("package.{$tableName}.{$columnName}", []);
        $cachedValues = array_column($cached, 'value');

        if ($enumValues !== $cachedValues) {
            $enum = array_map(fn($option) => [
                'value' => (string) $option,
                'label' => (string) strtolower(str_replace(' ', '_', (string) $option)),
            ], $enumValues);

            Cache::put(
                "package.{$tableName}.{$columnName}",
                $enum,
                Carbon::now()->addYear()
            );

            return $enum;
        }

        return $cached;
    }

    /**
     * Transfers ownership of all contents from one user to another.
     *
     *
     * @see Content
     */
    public static function transferContentOwnership(int $oldUserId, int $defaultUserId): void
    {
        Content::where('user_id', $oldUserId)->update(['user_id' => $defaultUserId]);
    }

    /**
     * Create a new service instance.
     *
     * @see Content
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Get the list of contents.
     *
     * @param array<string, string|int|bool> $params
     * @see ContentPaginatedDTO
     */
    public function index(array $params): ContentPaginatedDTO
    {
        $desiredFields = ['id', 'content_visibility_id', 'content_category_id', 'type', 'scheduled_on', 'url', 'title', 'user_id'];

        $fields = $this->getFields($this->content, $desiredFields);

        $form = $this->buildForm($this->content, $fields);
        $formOptions = [
            'content_visibility_id' => ContentVisibilityService::getAllActiveContentVisibilities(),
            'content_category_id' => ContentCategoryService::getAllActiveContentCategories(),
            'type' => self::getContentEnum('type'),
        ];
        foreach ($form as $field) {
            $key = $field->key;
            if (isset($formOptions[$key])) {
                $field->type = 'select';
                $field->options = $formOptions[$key];
            }
        }

        $query = $this->content->select($fields)
            ->with([
                'content_visibility' => function ($query) {
                    $query->select('id', 'value');
                },
                'content_category' => function ($query) {
                    $query->select('id', 'value')->where('is_active', true);
                },
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->paginate(25);

        if (array_key_exists('trashed', $params) && $params['trashed']) {
            $query->onlyTrashed();
        }

        $paginator = $query->paginate(25);

        $locale = (string) app()->getLocale();

        $paginator->getCollection()->transform(function ($item) use ($locale) {
            if ($item->relationLoaded('content_visibility')) {
                $item->content_visibility->transform(function ($visibility) use ($locale) {
                    return [
                        'value' => (int) $visibility->id,
                        'label' => (string) ($visibility->value[$locale] ?? $visibility->value['en']),
                    ];
                });
            }

            if ($item->relationLoaded('content_category')) {
                $item->content_category->transform(function ($category) use ($locale) {
                    return [
                        'value' => (int) $category->id,
                        'label' => (string) ($category->value[$locale] ?? $category->value['en']),
                    ];
                });
            }

            return $item;
        });

        return ContentPaginatedDTO::fromPaginator($paginator, $form);
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
        $desiredFields = [
            'content_visibility_id',
            'content_category_id',
            'type',
            'scheduled_on',
            'title',
            'allow_comments',
            'allow_share',
        ];

        $form = $this->buildForm($this->content, $this->getFields($this->content, $desiredFields));

        return $form;
    }

    /**
     * Create a new content.
     *
     * @see ContentPayloadDTO
     */
    public function store(ContentPayloadDTO $payload): int
    {
        $data = [
            'content_visibility_id' => (int) $payload->content_visibility_id,
            'content_category_id' => (int) $payload->content_category_id,
            'type' => (string) $payload->type,
            'scheduled_on' => $payload->scheduled_on,
            'title' => $payload->title,
            'allow_comments' => (bool) $payload->allow_comments,
            'allow_share' => (bool) $payload->allow_share,
        ];

        $data['slug'] = Str::slug($data['title']);
        $data['url'] = $this->getContentUrl($data['type'], $data['slug']);
        $data['user_id'] = (int) auth()->id();

        $content = Content::create($data);

        return $content->id;
    }

    /**
     * Retrieve a content.
     *
     * @see ContentDetailsDTO
     */
    public function show(int $contentId): ?ContentDetailsDTO
    {
        $desiredFields = [
            'content_visibility_id',
            'content_category_id',
            'type',
            'slug',
            'url',
            'title',
            'allow_comments',
            'allow_share',
            'user_id',
        ];

        $fields = $this->getFields($this->content, $desiredFields);

        $content = $this->content->select($fields)
            ->with([
                'content_visibility' => function ($query) {
                    $query->select('id', 'value');
                },
                'content_category' => function ($query) {
                    $query->select('id', 'value')->where('is_active', true);
                },
                'user' => function ($query) {
                    $query->select('id', 'full_name');
                },
            ])
            ->find($contentId);

        if (! $content) {
            return null;
        }

        $locale = (string) app()->getLocale();

        $visibility = $content->content_visibility;
        $content->content_visibility->value = $content->value[$locale] ?? $visibility->value['en'];

        $category = $content->content_category;
        $content->content_category->value = $content->value[$locale()] ?? $category->value['en'];

        return ContentDetailsDTO::fromModel($content);
    }

    /**
     * Show the edit form.
     *
     * @return FormFieldDTO[]
     *
     * @see FormFieldDTO
     */
    public function edit(int $contentId): array
    {
        $desiredFields = [
            'content_visibility_id',
            'content_category_id',
            'type',
            'scheduled_on',
            'title',
            'allow_comments',
            'allow_share',
        ];

        $fields = $this->getFields($this->content, $desiredFields);

        $form = $this->buildForm($this->content, $fields);

        $content = $this->content->select($fields)->findOrFail($contentId);

        foreach ($form as $field) {
            $field->value = $content->{$field->key};
        }

        return $form;
    }

    /**
     * Update a content.
     *
     * @see ContentPayloadDTO
     */
    public function update(ContentPayloadDTO $payload, int $contentId): ?int
    {
        $content = $this->content->find($contentId);
        if (! $content) {
            return null;
        }
        $data = [
            'content_visibility_id' => (int) $payload->content_visibility_id ?? $content->content_visibility_id,
            'content_category_id' => (int) $payload->content_category_id ?? $content->content_category_id,
            'type' => (string) $payload->type ?? $content->type,
            'scheduled_on' => $payload->scheduled_on ?? $content->scheduled_on,
            'title' => $payload->title ?? $content->title,
            'allow_comments' => (bool) $payload->allow_comments ?? $content->allow_comments,
            'allow_share' => (bool) $payload->allow_share ?? $content->allow_share,
            'user_id' => (int) auth()->id(),
        ];

        $data['slug'] = Str::slug($data['title']);
        $data['url'] = $this->getContentUrl($data['type'], $data['slug']);
        $data['user_id'] = (int) auth()->id();

        $content->update($data);

        return $content->id;
    }

    /**
     * Delete a content.
     */
    public function destroy(int $contentId): bool
    {
        $content = $this->content->find($contentId);
        if (! $content) {
            return false;
        }

        $content->delete();

        return true;
    }

    /**
     * Restore a content.
     */
    public function restore(int $contentId): bool
    {
        $content = $this->content->withTrashed()->find($contentId);
        if (! $content) {
            return false;
        }

        $content->restore();

        return true;
    }

    /**
     * @param string $type
     * @param string $slug
     * @return string
     */
    private function getContentUrl(string $type, string $slug): string
    {
        $type = strtolower($type);
        if ($type === ContentType::ARTICLE) {
            return config('cms.app_url') . "/blog/{$type}/{$slug}";
        }

        return config('cms.app_url') . "{$slug}";
    }
}
