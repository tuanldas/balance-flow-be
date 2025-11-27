<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryServiceInterface $categoryService
    ) {}

    /**
     * Get all categories for the authenticated user (paginated)
     * GET /api/categories
     * Query params: type (income/expense), per_page (default: 15)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $type = $request->query('type');
            $perPage = (int) $request->query('per_page', 15);

            if ($type) {
                $categories = $this->categoryService->getPaginatedByType($userId, $type, $perPage);
            } else {
                $categories = $this->categoryService->getPaginatedForUser($userId, $perPage);
            }

            return response()->json([
                'success' => true,
                'data' => $categories->items(),
                'pagination' => [
                    'current_page' => $categories->currentPage(),
                    'per_page' => $categories->perPage(),
                    'total' => $categories->total(),
                    'last_page' => $categories->lastPage(),
                    'from' => $categories->firstItem(),
                    'to' => $categories->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single category
     * GET /api/categories/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->findById($id);

            if (! $category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Danh mục không tồn tại',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $category,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new user category
     * POST /api/categories
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $category = $this->categoryService->createUserCategory($userId, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Tạo danh mục thành công',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a user category
     * PUT/PATCH /api/categories/{id}
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $success = $this->categoryService->updateUserCategory($userId, $id, $request->validated());

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cập nhật danh mục thất bại',
                ], 500);
            }

            $category = $this->categoryService->findById($id);

            return response()->json([
                'success' => true,
                'data' => $category,
                'message' => 'Cập nhật danh mục thành công',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a user category
     * DELETE /api/categories/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $success = $this->categoryService->deleteUserCategory($userId, $id);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Xóa danh mục thất bại',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get subcategories of a parent category
     * GET /api/categories/{id}/subcategories
     */
    public function subcategories(string $id): JsonResponse
    {
        try {
            $subcategories = $this->categoryService->getSubcategories($id);

            return response()->json([
                'success' => true,
                'data' => $subcategories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
