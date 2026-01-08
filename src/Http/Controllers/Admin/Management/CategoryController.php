<?php

namespace LiviuVoica\LbCms\Http\Controllers\Admin\Communication;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LiviuVoica\LbCms\DTO\CategoryPayloadDTO;
use LiviuVoica\LbCms\Http\Controllers\Controller;
use LiviuVoica\LbCms\Http\Requests\CategoryRequest;
use LiviuVoica\LbCms\Models\Category;
use LiviuVoica\LbCms\Services\CategoryService;

class CategoryController extends Controller
{
    /** @var CategoryService The category service instance. */
    private CategoryService $categoryService;

    /**
     * Initialize the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->categoryService = new CategoryService(new Category);
    }

    /**
     * Get the list of category.
     */
    public function index(Request $request): JsonResponse
    {
        $results = $this->categoryService->index();

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
        $results = $this->categoryService->create();

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Create a new category.
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $payload = CategoryPayloadDTO::fromRequest([
            'key' => $request->input('key'),
            'value' => $request->input('value'),
            'is_active' => $request->boolean('is_active'),
            'is_reviewable' => $request->boolean('is_reviewable'),
        ]);

        $results = $this->categoryService->store($payload);

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
        $results = $this->categoryService->edit((int) $id);

        return new JsonResponse([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Update a category.
     */
    public function update(CategoryRequest $request, string $id): JsonResponse
    {
        $payload = CategoryPayloadDTO::fromRequest([
            'key' => $request->input('key'),
            'value' => $request->input('value'),
            'is_active' => $request->boolean('is_active'),
            'is_reviewable' => $request->boolean('is_reviewable'),
        ]);

        $results = $this->categoryService->update($payload, (int) $id);

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
     * Delete a category.
     */
    public function destroy(string $id): JsonResponse
    {
        $results = $this->categoryService->destroy((int) $id);
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
