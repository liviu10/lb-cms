<?php

namespace LiviuVoica\LbCms\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LiviuVoica\LbCms\DTO\ContentPayloadDTO;
use LiviuVoica\LbCms\DTO\FormFieldDTO;
use LiviuVoica\LbCms\Models\Content;
use LiviuVoica\LbCms\Utilities\BuildFormTrait;
use LiviuVoica\LbCms\Utilities\DesiredFieldsTrait;
use LiviuVoica\LbCms\Utilities\FilterAndOrderTrait;
use LiviuVoica\LbCms\Services\ContentCategoryService;
use Illuminate\Support\Facades\Cache;
use LiviuVoica\LbCms\DTO\ContentDetailsDTO;
use Illuminate\Support\Facades\Storage;
use LiviuVoica\LbCms\DTO\ContentDTO;
use LiviuVoica\LbCms\DTO\PaginatedDTO;
use LiviuVoica\LbCms\Enums\ContentMediaType;
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
        preg_match("/enum\((.*?)\)/", $result[0]->Type, $matches);

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
     * @return PaginatedDTO<ContentDTO>
     * @see PaginatedDTO
     * @see ContentDTO
     */
    public function index(array $params): PaginatedDTO
    {
        $desiredFields = ['id', 'content_category_id', 'visibility', 'type', 'scheduled_on', 'url', 'title', 'user_id'];

        $fields = $this->getFields($this->content, $desiredFields);

        $form = $this->buildForm($this->content, $fields);
        $formOptions = [
            'content_category_id' => ContentCategoryService::getAllActiveContentCategories(),
            'visibility' => self::getContentEnum('visibility'),
            'type' => self::getContentEnum('type'),
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

        $query = $this->content->select($fields)
            ->with([
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

        return PaginatedDTO::fromPaginator(
            $paginator,
            $form,
            fn($item) => ContentDTO::fromModel($item)
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
        $desiredFields = [
            'content_category_id',
            'visibility',
            'type',
            'scheduled_on',
            'tags',
            'title',
            'content',
        ];

        $form = $this->buildForm($this->content, $this->getFields($this->content, $desiredFields));

        $form['content_media_files'] = [
            'key' => 'content_media_files',
            'type' => 'file',
            'value' => '',
        ];

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
            'content_category_id' => (int) $payload->content_category_id,
            'visibility' => (string) $payload->visibility,
            'type' => (string) $payload->type,
            'scheduled_on' => $payload->scheduled_on,
            'slug' => (string) $payload->slug,
            'tags' => $payload->tags,
            'title' => $payload->title,
            'content' => $payload->content ?? null,
            'content_media_files' => $payload->content_media_files,
        ];

        $prefix = ContentType::from($payload->type)->urlPrefix();
        $data['url'] = config('cms.app_name').($prefix !== '' ? "/{$prefix}" : '')."/{$data['slug']}";
        $data['user_id'] = (int) auth()->id();

        $content = Content::create($data);

        if (! empty($payload->content_media_files)) {
            foreach ($payload->content_media_files as $file) {
                $mediaType = $this->detectMediaType($file['extension'], $file['client_mime_type']);
                $directory = "uploads/content/{$content->id}/" . strtolower($mediaType);
                $contents = file_get_contents($file['temporary_path']);
                $path = Storage::disk('local')->put($directory . '/' . $file['original_name'], $contents);

                $content->media()->create([
                    'type' => $mediaType,
                    'path' => $path,
                    'title' => $file['original_name'],
                    'metadata' => null,
                ]);
            }
        }

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
            'id',
            'content_category_id',
            'visibility',
            'type',
            'slug',
            'url',
            'tags',
            'title',
            'user_id',
        ];

        $fields = $this->getFields($this->content, $desiredFields);

        $content = $this->content->select($fields)
            ->with([
                'content_category' => function ($query) {
                    $query->select('id', 'value')->where('is_active', true);
                },
                'content_media' => function ($query) {
                    $query->select('id', 'content_id', 'title', 'path');
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

        $category = $content->content_category;
        $content->content_category->value = $content->value[$locale()] ?? $category->value['en'];

        $content->media_files = $content->media->map(fn($m) => [
            'title' => $m->title,
            'path' => $m->path,
        ])->toArray();

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
            'content_category_id',
            'visibility',
            'type',
            'scheduled_on',
            'tags',
            'title',
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
            'content_category_id' => (int) $payload->content_category_id ?? $content->content_category_id,
            'visibility' => (string) $payload->visibility ?? $content->visibility,
            'type' => (string) $payload->type ?? $content->type,
            'scheduled_on' => $payload->scheduled_on ?? $content->scheduled_on,
            'slug' => (string) $payload->slug ?? $content->slug,
            'tags' => $payload->tags ?? $content->tags,
            'title' => $payload->title ?? $content->title,
            'user_id' => (int) auth()->id(),
        ];

        $prefix = ContentType::from($payload->type)->urlPrefix();
        $data['url'] = config('cms.app_name').($prefix !== '' ? "/{$prefix}" : '')."/{$data['slug']}";
        $data['user_id'] = (int) auth()->id();

        $content->update($data);

        $existingMediaFiles = $content->media()->get();
        $payloadFilenames = array_map(fn($f) => $f['original_name'], $payload->content_media_files ?? []);

        // Delete media that exist in DB but are not in payload
        foreach ($existingMediaFiles as $media) {
            if (! in_array($media->title, $payloadFilenames)) {
                if (Storage::disk('local')->exists($media->path)) {
                    Storage::disk('local')->delete($media->path);
                }

                $media->delete();
            }
        }

        // Add new media files (your original if block)
        if (! empty($payload->content_media_files)) {
            foreach ($payload->content_media_files as $file) {
                // Skip file if it already exists in DB (title match)
                $alreadyExists = $content->media()->where('title', $file['original_name'])->exists();
                if ($alreadyExists) {
                    continue;
                }

                $mediaType = $this->detectMediaType($file['extension']);
                $directory = "uploads/content/{$content->id}/" . strtolower($mediaType);

                $contents = file_get_contents($file['temporary_path']);
                $path = Storage::disk('local')->put($directory . '/' . $file['original_name'], $contents);

                $content->media()->create([
                    'type' => $mediaType,
                    'path' => $path,
                    'title' => $file['original_name'],
                    'metadata' => null,
                ]);
            }
        }

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

        foreach ($content->media as $media) {
            $media->delete();
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

        foreach ($content->media()->withTrashed()->get() as $media) {
            $media->restore();
        }

        return true;
    }

    /**
     * Force delete a content along with all its associated media.
     */
    public function forceDestroy(int $contentId): bool
    {
        $content = $this->content->withTrashed()->with('media')->find($contentId);
        if (! $content) {
            return false;
        }

        // Delete all associated media files from storage and DB
        foreach ($content->media()->withTrashed()->get() as $media) {
            if (Storage::disk('local')->exists($media->path)) {
                Storage::disk('local')->delete($media->path);
            }

            $media->forceDelete();
        }

        // Optionally delete the content's directory entirely
        $contentDirectory = "uploads/content/{$content->id}";
        if (Storage::disk('local')->exists($contentDirectory)) {
            Storage::disk('local')->deleteDirectory($contentDirectory);
        }

        $content->forceDelete();

        return true;
    }

    /**
     * Detects the appropriate ContentMediaType based on file extension or MIME type.
     *
     * @param string $extension
     * @return string
     */
    private function detectMediaType(string $extension): string
    {
        $extension = strtolower($extension);

        return match (true) {
            in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => ContentMediaType::IMAGES->value,
            in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'ppt', 'pptx']) => ContentMediaType::DOCUMENTS->value,
            in_array($extension, ['mp4', 'avi', 'mov', 'mkv', 'webm']) => ContentMediaType::VIDEO->value,
            in_array($extension, ['mp3', 'wav', 'ogg', 'flac', 'aac']) => ContentMediaType::AUDIO->value,
            default => ContentMediaType::OTHERS->value,
        };
    }
}
