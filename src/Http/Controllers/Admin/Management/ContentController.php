<?php

namespace LiviuVoica\LbCms\Http\Controllers\Admin\Management;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LiviuVoica\LbCms\DTO\ContentPayloadDTO;
use LiviuVoica\LbCms\Http\Controllers\Controller;
use LiviuVoica\LbCms\Http\Requests\ContentRequest;
use LiviuVoica\LbCms\Models\Content;
use LiviuVoica\LbCms\Services\ContentService;

class ContentController extends Controller
{
    /** @var ContentService The content service instance. */
    private ContentService $contentService;

    /**
     * Initialize the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->contentService = new ContentService(new Content);
    }

    /**
     * Get the list of contents.
     */
    public function index(Request $request): JsonResponse
    {
        $params = [
            'trashed' => (bool) $request->query('trashed', false),
        ];
        $results = $this->contentService->index($params);

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
        $results = $this->contentService->create();

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Create a new content.
     */
    public function store(ContentRequest $request): JsonResponse
    {
        $payload = ContentPayloadDTO::fromRequest([
            'content_category_id' => $request->input('content_category_id'),
            'visibility' => $request->input('visibility'),
            'type' => $request->input('type'),
            'scheduled_on' => $request->input('scheduled_on'),
            'tags' => $request->input('tags'),
            'title' => $request->input('title'),
            'allow_comments' => $request->boolean('allow_comments'),
            'allow_share' => $request->boolean('allow_share'),
        ]);

        $results = $this->contentService->store($payload);

        return new JsonResponse([
            'success' => true,
            'results' => (int) $results,
        ]);
    }

    /**
     * Retrieve a content.
     */
    public function show(string $id): JsonResponse
    {
        $results = $this->contentService->show((int) $id);

        if ($results === null) {
            return new JsonResponse([
                'success' => false,
                'error_code' => 'CONTENT_NOT_FOUND',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(string $id): JsonResponse
    {
        $results = $this->contentService->edit((int) $id);

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Update a content content.
     */
    public function update(ContentRequest $request, string $id): JsonResponse
    {
        $payload = ContentPayloadDTO::fromRequest([
            'content_category_id' => $request->input('content_category_id'),
            'visibility' => $request->input('visibility'),
            'type' => $request->input('type'),
            'scheduled_on' => $request->input('scheduled_on'),
            'tags' => $request->input('tags'),
            'title' => $request->input('title'),
            'allow_comments' => $request->boolean('allow_comments'),
            'allow_share' => $request->boolean('allow_share'),
        ]);

        $results = $this->contentService->update($payload, (int) $id);

        if ($results === null) {
            return new JsonResponse([
                'success' => false,
                'error_code' => 'CONTENT_NOT_FOUND',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'results' => (int) $results,
        ]);
    }

    /**
     * Delete a content.
     */
    public function destroy(string $id): JsonResponse
    {
        $results = $this->contentService->destroy((int) $id);
        if ($results === false) {
            return new JsonResponse([
                'success' => false,
                'error_code' => 'CONTENT_NOT_FOUND',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Restore a content.
     */
    public function restore(string $id): JsonResponse
    {
        $results = $this->contentService->restore((int) $id);
        if ($results === false) {
            return new JsonResponse([
                'success' => false,
                'error_code' => 'CONTENT_NOT_FOUND',
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
