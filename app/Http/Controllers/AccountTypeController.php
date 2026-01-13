<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountTypeResource;
use App\Services\Contracts\AccountTypeServiceInterface;
use Illuminate\Http\JsonResponse;

class AccountTypeController extends Controller
{
    public function __construct(
        protected AccountTypeServiceInterface $accountTypeService
    ) {}

    /**
     * Get list of account types
     * GET /api/account-types
     */
    public function index(): JsonResponse
    {
        try {
            $accountTypes = $this->accountTypeService->getAll();

            return response()->json([
                'success' => true,
                'message' => __('account_types.list_success'),
                'data' => AccountTypeResource::collection($accountTypes),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('account_types.list_failed'),
            ], 500);
        }
    }
}
