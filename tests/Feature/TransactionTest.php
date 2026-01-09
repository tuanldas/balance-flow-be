<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $incomeCategory;

    protected Category $expenseCategory;

    protected Account $account;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed system categories and account types
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);
        $this->artisan('db:seed', ['--class' => 'AccountTypeSeeder']);

        // Set default headers for all requests in this test
        $this->withHeaders([
            'Accept-Language' => 'vi',
        ]);

        // Create a test user
        $this->user = User::factory()->create();

        // Get system categories for testing
        $this->incomeCategory = Category::where('is_system', true)
            ->where('category_type', 'income')
            ->first();

        $this->expenseCategory = Category::where('is_system', true)
            ->where('category_type', 'expense')
            ->first();

        // Create account for testing (account_id is now required)
        $accountType = AccountType::first();
        $this->account = Account::factory()
            ->forUser($this->user)
            ->forAccountType($accountType)
            ->create();
    }

    /**
     * Test: User can get all their transactions with pagination
     */
    public function test_user_can_get_all_transactions(): void
    {
        // Create some transactions for the user
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(5)
            ->create();

        $response = $this->actingAs($this->user)->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'amount',
                        'raw_amount',
                        'name',
                        'transaction_date',
                        'notes',
                        'category',
                        'account',
                        'tags',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                    'from',
                    'to',
                ],
            ]);

        $this->assertEquals(5, $response->json('pagination.total'));
    }

    /**
     * Test: Pagination works with custom per_page
     */
    public function test_pagination_with_custom_per_page(): void
    {
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(10)
            ->create();

        $response = $this->actingAs($this->user)->getJson('/api/transactions?per_page=3');

        $response->assertStatus(200);

        $data = $response->json('data');
        $pagination = $response->json('pagination');

        $this->assertCount(3, $data);
        $this->assertEquals(3, $pagination['per_page']);
        $this->assertEquals(10, $pagination['total']);
    }

    /**
     * Test: Transactions are sorted by transaction_date desc by default
     */
    public function test_transactions_sorted_by_date_desc_by_default(): void
    {
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['transaction_date' => now()->subDays(2)]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['transaction_date' => now()]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['transaction_date' => now()->subDay()]);

        $response = $this->actingAs($this->user)->getJson('/api/transactions');

        $response->assertStatus(200);

        $data = $response->json('data');

        // First should be the most recent (today)
        $this->assertGreaterThan(
            $data[1]['transaction_date'],
            $data[0]['transaction_date']
        );
    }

    /**
     * Test: User can sort transactions by amount
     */
    public function test_user_can_sort_transactions_by_amount(): void
    {
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 100]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 300]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 200]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions?sort_by=amount&sort_direction=asc');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals(100, abs($data[0]['raw_amount']));
        $this->assertEquals(200, abs($data[1]['raw_amount']));
        $this->assertEquals(300, abs($data[2]['raw_amount']));
    }

    /**
     * Test: User can filter transactions by category
     */
    public function test_user_can_filter_transactions_by_category(): void
    {
        $userCategory = Category::factory()->forUser($this->user->id)->expense()->create();

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create();

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($userCategory->id)
            ->forAccount($this->account->id)
            ->count(2)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?category_id={$userCategory->id}");

        $response->assertStatus(200);

        $this->assertEquals(2, $response->json('pagination.total'));

        $data = $response->json('data');
        foreach ($data as $transaction) {
            $this->assertEquals($userCategory->id, $transaction['category']['id']);
        }
    }

    /**
     * Test: User can filter transactions by multiple categories
     */
    public function test_user_can_filter_transactions_by_multiple_categories(): void
    {
        $userCategory1 = Category::factory()->forUser($this->user->id)->expense()->create();
        $userCategory2 = Category::factory()->forUser($this->user->id)->expense()->create();

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create();

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($userCategory1->id)
            ->forAccount($this->account->id)
            ->count(2)
            ->create();

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($userCategory2->id)
            ->forAccount($this->account->id)
            ->count(4)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?category_id={$userCategory1->id},{$userCategory2->id}");

        $response->assertStatus(200);

        $this->assertEquals(6, $response->json('pagination.total'));

        $data = $response->json('data');
        foreach ($data as $transaction) {
            $this->assertContains($transaction['category']['id'], [$userCategory1->id, $userCategory2->id]);
        }
    }

    /**
     * Test: User can search transactions by merchant name
     */
    public function test_user_can_search_transactions_by_name(): void
    {
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'Grab Food']);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'Shopee']);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'GrabBike']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions?search=Grab');

        $response->assertStatus(200);

        $this->assertEquals(2, $response->json('pagination.total'));

        $data = $response->json('data');
        foreach ($data as $transaction) {
            $this->assertStringContainsStringIgnoringCase('Grab', $transaction['name']);
        }
    }

    /**
     * Test: User can combine multiple filters
     */
    public function test_user_can_combine_multiple_filters(): void
    {
        $userCategory = Category::factory()->forUser($this->user->id)->expense()->create();

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($userCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'Grab Food']);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($userCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'Shopee']);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'Grab Express']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?category_id={$userCategory->id}&search=Grab");

        $response->assertStatus(200);

        $this->assertEquals(1, $response->json('pagination.total'));

        $data = $response->json('data');
        $this->assertEquals('Grab Food', $data[0]['name']);
    }

    /**
     * Test: User can filter transactions by date range (both start and end)
     */
    public function test_user_can_filter_transactions_by_date_range(): void
    {
        // Create transactions on different dates
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['transaction_date' => now()->subDays(10)]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create(['transaction_date' => now()->subDays(5)]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(2)
            ->create(['transaction_date' => now()->subDays(2)]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['transaction_date' => now()]);

        $startDate = now()->subDays(6)->format('Y-m-d');
        $endDate = now()->subDays(1)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        // Should return only transactions between 6 days ago and 1 day ago (3 + 2 = 5)
        $this->assertEquals(5, $response->json('pagination.total'));
    }

    /**
     * Test: User can filter transactions by start date only
     */
    public function test_user_can_filter_transactions_by_start_date_only(): void
    {
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(2)
            ->create(['transaction_date' => now()->subDays(10)]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create(['transaction_date' => now()->subDays(2)]);

        $startDate = now()->subDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?start_date={$startDate}");

        $response->assertStatus(200);

        // Should return only transactions from 5 days ago to now (3 transactions)
        $this->assertEquals(3, $response->json('pagination.total'));
    }

    /**
     * Test: User can filter transactions by end date only
     */
    public function test_user_can_filter_transactions_by_end_date_only(): void
    {
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(2)
            ->create(['transaction_date' => now()->subDays(10)]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create(['transaction_date' => now()->subDays(2)]);

        $endDate = now()->subDays(5)->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions?end_date={$endDate}");

        $response->assertStatus(200);

        // Should return only transactions up to 5 days ago (2 transactions)
        $this->assertEquals(2, $response->json('pagination.total'));
    }

    /**
     * Test: User can only see their own transactions
     */
    public function test_user_can_only_see_own_transactions(): void
    {
        $otherUser = User::factory()->create();

        // Create transactions for both users
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create();

        Transaction::factory()
            ->forUser($otherUser->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(5)
            ->create();

        $response = $this->actingAs($this->user)->getJson('/api/transactions');

        $response->assertStatus(200);

        // Should only see own transactions
        $this->assertEquals(3, $response->json('pagination.total'));
    }

    /**
     * Test: User can get a single transaction
     */
    public function test_user_can_get_single_transaction(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['name' => 'Test Merchant']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'name' => 'Test Merchant',
                ],
            ]);
    }

    /**
     * Test: User cannot get another user's transaction
     */
    public function test_user_cannot_get_another_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()
            ->forUser($otherUser->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: User can create a transaction
     */
    public function test_user_can_create_transaction(): void
    {
        $data = [
            'category_id' => $this->expenseCategory->id,
            'account_id' => $this->account->id,
            'amount' => 150000,
            'name' => 'Grab Food',
            'transaction_date' => '2025-12-14T10:30:00',
            'notes' => 'Lunch',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Tạo giao dịch thành công.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'category_id' => $this->expenseCategory->id,
            'account_id' => $this->account->id,
            'amount' => 150000,
            'name' => 'Grab Food',
        ]);
    }

    /**
     * Test: Transaction amount is returned with correct sign based on category type
     */
    public function test_transaction_amount_has_correct_sign(): void
    {
        // Create expense transaction
        $expense = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 100000]);

        // Create income transaction
        $income = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->incomeCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 200000]);

        // Get expense
        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$expense->id}");

        $response->assertStatus(200);
        $this->assertLessThan(0, $response->json('data.amount')); // Negative for expense

        // Get income
        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$income->id}");

        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('data.amount')); // Positive for income
    }

    /**
     * Test: User can create transaction with system category
     */
    public function test_user_can_create_transaction_with_system_category(): void
    {
        $data = [
            'category_id' => $this->incomeCategory->id,
            'account_id' => $this->account->id,
            'amount' => 5000000,
            'transaction_date' => now()->toIso8601String(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'category_id' => $this->incomeCategory->id,
            'account_id' => $this->account->id,
        ]);
    }

    /**
     * Test: User cannot create transaction with another user's category
     */
    public function test_user_cannot_create_transaction_with_another_users_category(): void
    {
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->forUser($otherUser->id)->expense()->create();

        $data = [
            'category_id' => $otherCategory->id,
            'account_id' => $this->account->id,
            'amount' => 100000,
            'transaction_date' => now()->toIso8601String(),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/transactions', $data);

        $response->assertStatus(422);
    }

    /**
     * Test: Validation fails for missing required fields
     */
    public function test_validation_fails_for_missing_required_fields(): void
    {
        // Missing all required fields
        $response = $this->actingAs($this->user)->postJson('/api/transactions', []);
        $response->assertStatus(422);

        // Missing category_id
        $response = $this->actingAs($this->user)->postJson('/api/transactions', [
            'amount' => 100000,
            'transaction_date' => now()->toIso8601String(),
        ]);
        $response->assertStatus(422);

        // Missing amount
        $response = $this->actingAs($this->user)->postJson('/api/transactions', [
            'category_id' => $this->expenseCategory->id,
            'transaction_date' => now()->toIso8601String(),
        ]);
        $response->assertStatus(422);

        // Missing transaction_date
        $response = $this->actingAs($this->user)->postJson('/api/transactions', [
            'category_id' => $this->expenseCategory->id,
            'amount' => 100000,
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test: Validation fails for invalid amount
     */
    public function test_validation_fails_for_invalid_amount(): void
    {
        // Negative amount
        $response = $this->actingAs($this->user)->postJson('/api/transactions', [
            'category_id' => $this->expenseCategory->id,
            'amount' => -100,
            'transaction_date' => now()->toIso8601String(),
        ]);
        $response->assertStatus(422);

        // Zero amount
        $response = $this->actingAs($this->user)->postJson('/api/transactions', [
            'category_id' => $this->expenseCategory->id,
            'amount' => 0,
            'transaction_date' => now()->toIso8601String(),
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test: User can update their own transaction
     */
    public function test_user_can_update_own_transaction(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $data = [
            'amount' => 250000,
            'name' => 'Updated Merchant',
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/transactions/{$transaction->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật giao dịch thành công.',
            ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 250000,
            'name' => 'Updated Merchant',
            'notes' => 'Updated notes',
        ]);
    }

    /**
     * Test: User can update transaction category
     */
    public function test_user_can_update_transaction_category(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        // Change to income category
        $response = $this->actingAs($this->user)
            ->putJson("/api/transactions/{$transaction->id}", [
                'category_id' => $this->incomeCategory->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $this->incomeCategory->id,
        ]);
    }

    /**
     * Test: User cannot update another user's transaction
     */
    public function test_user_cannot_update_another_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()
            ->forUser($otherUser->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/transactions/{$transaction->id}", [
                'amount' => 999999,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test: User can delete their own transaction
     */
    public function test_user_can_delete_own_transaction(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Xóa giao dịch thành công.',
            ]);

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * Test: User cannot delete another user's transaction
     */
    public function test_user_cannot_delete_another_users_transaction(): void
    {
        $otherUser = User::factory()->create();
        $transaction = Transaction::factory()
            ->forUser($otherUser->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(422);

        // Transaction should still exist
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
        ]);
    }

    /**
     * Test: User can get transaction summary
     */
    public function test_user_can_get_transaction_summary(): void
    {
        // Create income transactions
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->incomeCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 1000000]);

        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->incomeCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 2000000]);

        // Create expense transactions
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create(['amount' => 500000]);

        $response = $this->actingAs($this->user)->getJson('/api/transactions/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_income',
                    'total_expense',
                    'balance',
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals(3000000, $data['total_income']);
        $this->assertEquals(500000, $data['total_expense']);
        $this->assertEquals(2500000, $data['balance']);
    }

    /**
     * Test: Summary can be filtered by date range
     */
    public function test_summary_can_be_filtered_by_date_range(): void
    {
        // Create transaction last month (outside range)
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->incomeCategory->id)
            ->forAccount($this->account->id)
            ->create([
                'amount' => 1000000,
                'transaction_date' => now()->subMonth(),
            ]);

        // Create transaction today (inside range)
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->incomeCategory->id)
            ->forAccount($this->account->id)
            ->create([
                'amount' => 500000,
                'transaction_date' => now(),
            ]);

        $startDate = now()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/summary?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(500000, $data['total_income']);
    }

    /**
     * Test: Response includes account data when available
     */
    public function test_response_includes_account_data_when_available(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.tags', []);

        // Account field should be null if Account model doesn't exist yet
        if (! class_exists(\App\Models\Account::class)) {
            $this->assertNull($response->json('data.account'));
        }
    }

    /**
     * Test: Response includes category data
     */
    public function test_response_includes_category_data(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'category' => [
                        'id',
                        'name',
                        'type',
                        'icon',
                    ],
                ],
            ]);
    }

    /**
     * Test: Unauthenticated user cannot access transactions API
     */
    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/transactions');
        $response->assertStatus(401);

        $response = $this->postJson('/api/transactions', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/transactions/summary');
        $response->assertStatus(401);
    }

    /**
     * Test: Transaction not found returns 404
     */
    public function test_transaction_not_found_returns_404(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$fakeId}");

        $response->assertStatus(404);
    }

    /**
     * Test: Category cannot be deleted if it has transactions
     */
    public function test_category_cannot_be_deleted_if_has_transactions(): void
    {
        // Create user's own category
        $category = Category::factory()->forUser($this->user->id)->expense()->create();

        // Create transaction for this category
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($category->id)
            ->forAccount($this->account->id)
            ->create();

        // Try to delete category
        $response = $this->actingAs($this->user)
            ->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(422);

        // Category should still exist
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test: User can filter transactions by account_id
     */
    public function test_user_can_filter_transactions_by_account_id(): void
    {
        // Create second account
        $accountType = AccountType::first();
        $secondAccount = Account::factory()
            ->forUser($this->user)
            ->forAccountType($accountType)
            ->create();

        // Create transactions for first account
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->count(3)
            ->create();

        // Create transactions for second account
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($secondAccount->id)
            ->count(2)
            ->create();

        // Test filter by first account
        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions?account_id='.$this->account->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);

        $this->assertEquals(3, $response->json('pagination.total'));

        // Test filter by second account
        $response = $this->actingAs($this->user)
            ->getJson('/api/transactions?account_id='.$secondAccount->id);

        $this->assertEquals(2, $response->json('pagination.total'));
    }

    /**
     * Test: User can create transaction with account_id
     */
    public function test_user_can_create_transaction_with_account_id(): void
    {
        $transactionData = [
            'category_id' => $this->expenseCategory->id,
            'account_id' => $this->account->id,
            'amount' => 50000,
            'name' => 'Test Transaction with Account',
            'transaction_date' => now()->format('Y-m-d'),
            'notes' => 'Testing account_id field',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', $transactionData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'name',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('transactions', [
            'name' => 'Test Transaction with Account',
            'account_id' => $this->account->id,
        ]);
    }

    /**
     * Test: Account ID is required when creating transaction
     */
    public function test_account_id_is_required(): void
    {
        $transactionData = [
            'category_id' => $this->expenseCategory->id,
            'amount' => 50000,
            'name' => 'Test Transaction',
            'transaction_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', $transactionData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_id']);
    }

    /**
     * Test: Account ID validation works
     */
    public function test_account_id_must_be_valid_uuid(): void
    {
        $transactionData = [
            'category_id' => $this->expenseCategory->id,
            'account_id' => 'invalid-uuid',
            'amount' => 50000,
            'name' => 'Test Transaction',
            'transaction_date' => now()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/transactions', $transactionData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_id']);
    }

    /**
     * Test: Transaction loads account relationship
     */
    public function test_transaction_loads_account_relationship(): void
    {
        $transaction = Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($this->expenseCategory->id)
            ->forAccount($this->account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'name',
                    'account' => [
                        'id',
                        'name',
                        'icon',
                        'color',
                    ],
                ],
            ]);

        $this->assertEquals($this->account->id, $response->json('data.account.id'));
    }
}
