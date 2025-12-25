<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Services\Contracts\TransactionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        protected TransactionServiceInterface $transactionService
    ) {}

    /**
     * Get all transactions for the authenticated user (paginated)
     * GET /api/transactions
     * Query params: per_page, sort_by, sort_direction, category_id (comma-separated), search, start_date, end_date
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $perPage = (int) $request->query('per_page', 15);
            $sortBy = $request->query('sort_by', 'transaction_date');
            $sortDirection = $request->query('sort_direction', 'desc');

            $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? strtolower($sortDirection) : 'desc';

            $allowedSortFields = ['transaction_date', 'amount', 'created_at', 'updated_at'];
            $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'transaction_date';

            $categoryIds = $request->query('category_id')
                ? array_filter(explode(',', $request->query('category_id')))
                : null;

            $filters = array_filter([
                'category_ids' => $categoryIds,
                'search' => $request->query('search'),
                'start_date' => $request->query('start_date'),
                'end_date' => $request->query('end_date'),
            ]);

            $transactions = $this->transactionService->getPaginatedForUser(
                $userId,
                $perPage,
                $sortBy,
                $sortDirection,
                $filters
            );

            return response()->json([
                'success' => true,
                'data' => TransactionResource::collection($transactions->items()),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
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
     * Get a single transaction
     * GET /api/transactions/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $transaction = $this->transactionService->findById($id);

            if (! $transaction) {
                return response()->json([
                    'success' => false,
                    'message' => __('transactions.not_found'),
                ], 404);
            }

            // Check ownership
            if ($transaction->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => __('transactions.unauthorized'),
                ], 403);
            }

            // Load category relationship
            $transaction->load('category');

            return response()->json([
                'success' => true,
                'data' => new TransactionResource($transaction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new transaction
     * POST /api/transactions
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $transaction = $this->transactionService->createTransaction($userId, $request->validated());

            // Load category relationship
            $transaction->load('category');

            return response()->json([
                'success' => true,
                'data' => new TransactionResource($transaction),
                'message' => __('transactions.created_success'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a transaction
     * PUT/PATCH /api/transactions/{id}
     */
    public function update(UpdateTransactionRequest $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $success = $this->transactionService->updateTransaction($userId, $id, $request->validated());

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('transactions.update_failed'),
                ], 500);
            }

            $transaction = $this->transactionService->findById($id);
            $transaction->load('category');

            return response()->json([
                'success' => true,
                'data' => new TransactionResource($transaction),
                'message' => __('transactions.updated_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a transaction
     * DELETE /api/transactions/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $success = $this->transactionService->deleteTransaction($userId, $id);

            if (! $success) {
                return response()->json([
                    'success' => false,
                    'message' => __('transactions.delete_failed'),
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => __('transactions.deleted_success'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get transaction summary for the authenticated user
     * GET /api/transactions/summary
     * Query params: start_date, end_date (optional)
     */
    public function summary(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $summary = $this->transactionService->getSummary($userId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
