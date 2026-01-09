<?php

namespace LiviuVoica\LbCms\Http\Controllers\Admin\Management;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LiviuVoica\LbCms\DTO\ContentCategoryPayloadDTO;
use LiviuVoica\LbCms\Http\Controllers\Controller;
use LiviuVoica\LbCms\Http\Requests\ContentCategoryRequest;
use LiviuVoica\LbCms\Models\ContentCategory;
use LiviuVoica\LbCms\Services\ContentCategoryService;

class ContentCategoryController extends Controller
{
    /** @var ContentCategoryService The content category service instance. */
    private ContentCategoryService $contentCategoryService;

    /**
     * Initialize the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->contentCategoryService = new ContentCategoryService(new ContentCategory);
    }

    /**
     * Get the list of content category.
     */
    public function index(Request $request): JsonResponse
    {
        $results = $this->contentCategoryService->index();

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): JsonResponse
    {
        $results = $this->contentCategoryService->create();

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Create a new content category.
     */
    public function store(ContentCategoryRequest $request): JsonResponse
    {
        $payload = ContentCategoryPayloadDTO::fromRequest([
            'key' => $request->input('key'),
            'value' => $request->input('value'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $results = $this->contentCategoryService->store($payload);

        return new JsonResponse([
            'success' => true,
            'results' => (int) $results,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(string $id): JsonResponse
    {
        $results = $this->contentCategoryService->edit((int) $id);

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Update a content content category.
     */
    public function update(ContentCategoryRequest $request, string $id): JsonResponse
    {
        $payload = ContentCategoryPayloadDTO::fromRequest([
            'key' => $request->input('key'),
            'value' => $request->input('value'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $results = $this->contentCategoryService->update($payload, (int) $id);

        if ($results === null) {
            return new JsonResponse([
                'success' => false,
                'error_code' => 'SUBJECT_NOT_FOUND',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'results' => (int) $results,
        ]);
    }

    /**
     * Delete a content category.
     */
    public function destroy(string $id): JsonResponse
    {
        $results = $this->contentCategoryService->destroy((int) $id);
        if ($results === false) {
            return new JsonResponse([
                'success' => false,
                'error_code' => 'SUBJECT_NOT_FOUND',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
