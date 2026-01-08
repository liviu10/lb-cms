<?php

namespace LiviuVoica\LbCms\Http\Controllers\Admin\Management;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LiviuVoica\LbCms\DTO\ContentVisibilityPayloadDTO;
use LiviuVoica\LbCms\Http\Controllers\Controller;
use LiviuVoica\LbCms\Http\Requests\ContentVisibilityRequest;
use LiviuVoica\LbCms\Models\ContentVisibility;
use LiviuVoica\LbCms\Services\ContentVisibilityService;

class ContentVisibilityController extends Controller
{
    /** @var ContentVisibilityService The content visibility service instance. */
    private ContentVisibilityService $contentVisibilityService;

    /**
     * Initialize the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->contentVisibilityService = new ContentVisibilityService(new ContentVisibility);
    }

    /**
     * Get the list of content visibility.
     */
    public function index(Request $request): JsonResponse
    {
        $results = $this->contentVisibilityService->index();

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
        $results = $this->contentVisibilityService->create();

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Create a new content visibility.
     */
    public function store(ContentVisibilityRequest $request): JsonResponse
    {
        $payload = ContentVisibilityPayloadDTO::fromRequest([
            'key' => $request->input('key'),
            'value' => $request->input('value'),
        ]);

        $results = $this->contentVisibilityService->store($payload);

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
        $results = $this->contentVisibilityService->edit((int) $id);

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Update a content visibility.
     */
    public function update(ContentVisibilityRequest $request, string $id): JsonResponse
    {
        $payload = ContentVisibilityPayloadDTO::fromRequest([
            'key' => $request->input('key'),
            'value' => $request->input('value'),
        ]);

        $results = $this->contentVisibilityService->update($payload, (int) $id);

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
}
