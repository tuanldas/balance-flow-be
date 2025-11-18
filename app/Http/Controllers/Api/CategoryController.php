<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteCategoryRequest;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService,
    ) {}

    /**
     * Lấy danh sách categories có thể truy cập (system + user's own)
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type'); // 'income' hoặc 'expense' hoặc null (all)

        if ($type !== null && ! in_array($type, ['income', 'expense'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.category.validation.type_invalid'),
            ], 422);
        }

        $categories = $this->categoryService->getAllAccessibleCategories(
            $request->user()->id,
            $type
        );

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => CategoryResource::collection($categories),
            ],
        ]);
    }

    /**
     * Tạo category mới cho user
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createUserCategory(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => __('messages.category.created'),
            'data' => [
                'category' => new CategoryResource($category),
            ],
        ], 201);
    }

    /**
     * Lấy thông tin chi tiết của category
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $category = $this->categoryService->getCategoryById($id);

        if ($category === null) {
            return response()->json([
                'success' => false,
                'message' => __('messages.category.not_found'),
            ], 404);
        }

        // Check if user can access this category
        if (! $category->is_system && $category->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => __('messages.category.unauthorized'),
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'category' => new CategoryResource($category),
            ],
        ]);
    }

    /**
     * Cập nhật category
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->updateCategory(
                $id,
                $request->user()->id,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => __('messages.category.updated'),
                'data' => [
                    'category' => new CategoryResource($category),
                ],
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Xóa category (với option chuyển transactions)
     */
    public function destroy(DeleteCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $this->categoryService->deleteCategory(
                $id,
                $request->user()->id,
                $request->input('transfer_to_category_id')
            );

            return response()->json([
                'success' => true,
                'message' => __('messages.category.deleted'),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Lấy số lượng transactions của category
     */
    public function transactionsCount(string $id): JsonResponse
    {
        $count = $this->categoryService->getTransactionCount($id);

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }
}
