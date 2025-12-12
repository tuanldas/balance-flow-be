<?php

namespace App\Http\Controllers;

use App\Services\Contracts\CategoryIconServiceInterface;
use Illuminate\Http\JsonResponse;

class CategoryIconController extends Controller
{
    public function __construct(
        protected CategoryIconServiceInterface $iconService
    ) {}

    /**
     * Get list of default category icons
     * GET /api/category-icons
     */
    public function index(): JsonResponse
    {
        try {
            $icons = $this->iconService->getDefaultIcons();

            return response()->json([
                'success' => true,
                'data' => $icons,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
