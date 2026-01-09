<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Services\Contracts\AccountServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        protected AccountServiceInterface $accountService
    ) {}

    /**
     * Get all accounts for the authenticated user (paginated)
     * GET /api/accounts
     * Query params: per_page, account_type_id, is_active
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $perPage = (int) $request->query('per_page', 15);
            $accountTypeId = $request->query('account_type_id');
            $isActive = $request->query('is_active');

            // Filter by account type
            if ($accountTypeId) {
                $accounts = $this->accountService->getByAccountType($accountTypeId, $userId);

                return response()->json([
                    'success' => true,
                    'data' => AccountResource::collection($accounts),
                ]);
            }

            // Filter by active status
            if ($isActive !== null) {
                $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
                $accounts = $isActive
                    ? $this->accountService->getActiveForUser($userId)
                    : $this->accountService->getAllForUser($userId);

                return response()->json([
                    'success' => true,
                    'data' => AccountResource::collection($accounts),
                ]);
            }

            // Paginated list
            $accounts = $this->accountService->getPaginatedForUser($userId, $perPage);

            return response()->json([
                'success' => true,
                'data' => AccountResource::collection($accounts->items()),
                'pagination' => [
                    'current_page' => $accounts->currentPage(),
                    'per_page' => $accounts->perPage(),
                    'total' => $accounts->total(),
                    'last_page' => $accounts->lastPage(),
                    'from' => $accounts->firstItem(),
                    'to' => $accounts->lastItem(),
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
     * Get a single account
     * GET /api/accounts/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $account = $this->accountService->findForUser($id, $userId);

            if (! $account) {
                return response()->json([
                    'success' => false,
                    'message' => __('accounts.not_found'),
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => new AccountResource($account),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new account
     * POST /api/accounts
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $account = $this->accountService->createForUser($request->validated(), $userId);

            return response()->json([
                'success' => true,
                'data' => new AccountResource($account),
                'message' => __('accounts.created_success'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an account
     * PUT/PATCH /api/accounts/{id}
     */
    public function update(UpdateAccountRequest $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $success = $this->accountService->updateForUser($id, $request->validated(), $userId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('accounts.not_found'),
                ], 404);
            }

            $account = $this->accountService->findForUser($id, $userId);

            return response()->json([
                'success' => true,
                'data' => new AccountResource($account),
                'message' => __('accounts.updated_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an account
     * DELETE /api/accounts/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $account = $this->accountService->findForUser($id, $userId);

            if (! $account) {
                return response()->json([
                    'success' => false,
                    'message' => __('accounts.not_found'),
                ], 404);
            }

            // Check if account has transactions
            if ($account->transactions()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => __('accounts.has_transactions'),
                ], 422);
            }

            $success = $this->accountService->deleteForUser($id, $userId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('accounts.delete_failed'),
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => __('accounts.deleted_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get total balance across all active accounts
     * GET /api/accounts/balance/total
     * Query params: currency (default: VND)
     */
    public function totalBalance(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $currency = $request->query('currency', 'VND');

            $result = $this->accountService->getTotalBalance($userId, $currency);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle account active status
     * POST /api/accounts/{id}/toggle-active
     */
    public function toggleActive(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $success = $this->accountService->toggleActiveStatus($id, $userId);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('accounts.not_found'),
                ], 404);
            }

            $account = $this->accountService->findForUser($id, $userId);

            return response()->json([
                'success' => true,
                'data' => new AccountResource($account),
                'message' => __('accounts.toggle_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
