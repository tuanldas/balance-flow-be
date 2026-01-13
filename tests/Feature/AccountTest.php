<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected AccountType $accountType;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed account types
        $this->artisan('db:seed', ['--class' => 'AccountTypeSeeder']);

        // Set default headers for all requests in this test
        $this->withHeaders([
            'Accept-Language' => 'vi',
        ]);

        // Create a test user
        $this->user = User::factory()->create();

        // Get an account type for testing
        $this->accountType = AccountType::first();
    }

    /**
     * Test: User can get all their accounts with pagination
     */
    public function test_user_can_get_all_accounts(): void
    {
        Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->count(5)
            ->create();

        $response = $this->actingAs($this->user)->getJson('/api/accounts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'account_type_id',
                        'name',
                        'balance',
                        'currency',
                        'icon',
                        'color',
                        'description',
                        'created_at',
                        'updated_at',
                        'account_type',
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
     * Test: User can get a single account
     */
    public function test_user_can_get_single_account(): void
    {
        $account = Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->create(['name' => 'Test Account']);

        $response = $this->actingAs($this->user)
            ->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $account->id,
                    'name' => 'Test Account',
                ],
            ]);
    }

    /**
     * Test: User cannot get another user's account
     */
    public function test_user_cannot_get_another_users_account(): void
    {
        $otherUser = User::factory()->create();
        $account = Account::factory()
            ->forUser($otherUser)
            ->forAccountType($this->accountType)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/accounts/{$account->id}");

        $response->assertStatus(404);
    }

    /**
     * Test: User can create an account
     */
    public function test_user_can_create_account(): void
    {
        $data = [
            'account_type_id' => $this->accountType->id,
            'name' => 'My New Account',
            'balance' => 1000000,
            'currency' => 'VND',
            'icon' => 'account_balance',
            'color' => '#2196F3',
            'description' => 'Test account',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/accounts', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Tạo tài khoản thành công.',
            ]);

        $this->assertDatabaseHas('accounts', [
            'user_id' => $this->user->id,
            'name' => 'My New Account',
            'balance' => 1000000,
        ]);
    }

    /**
     * Test: Account creation requires account_type_id
     */
    public function test_account_creation_requires_account_type_id(): void
    {
        $data = [
            'name' => 'My New Account',
        ];

        $response = $this->actingAs($this->user)->postJson('/api/accounts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['account_type_id']);
    }

    /**
     * Test: Account creation requires name
     */
    public function test_account_creation_requires_name(): void
    {
        $data = [
            'account_type_id' => $this->accountType->id,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/accounts', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: User can update their own account
     */
    public function test_user_can_update_account(): void
    {
        $account = Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->create();

        $data = [
            'name' => 'Updated Account Name',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/accounts/{$account->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật tài khoản thành công.',
            ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Updated Account Name',
        ]);
    }

    /**
     * Test: User cannot update another user's account
     */
    public function test_user_cannot_update_another_users_account(): void
    {
        $otherUser = User::factory()->create();
        $account = Account::factory()
            ->forUser($otherUser)
            ->forAccountType($this->accountType)
            ->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/accounts/{$account->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertStatus(404);
    }

    /**
     * Test: Balance cannot be directly updated
     */
    public function test_balance_cannot_be_directly_updated(): void
    {
        $account = Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->withBalance(1000000)
            ->create();

        $originalBalance = $account->balance;

        $response = $this->actingAs($this->user)
            ->putJson("/api/accounts/{$account->id}", [
                'balance' => 999999999,
                'name' => 'Updated Name',
            ]);

        $response->assertStatus(200);

        // Balance should remain unchanged
        $account->refresh();
        $this->assertEquals($originalBalance, $account->balance);
    }

    /**
     * Test: User can delete their own account
     */
    public function test_user_can_delete_account(): void
    {
        $account = Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Xóa tài khoản thành công.',
            ]);

        $this->assertDatabaseMissing('accounts', [
            'id' => $account->id,
        ]);
    }

    /**
     * Test: User cannot delete another user's account
     */
    public function test_user_cannot_delete_another_users_account(): void
    {
        $otherUser = User::factory()->create();
        $account = Account::factory()
            ->forUser($otherUser)
            ->forAccountType($this->accountType)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(404);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
        ]);
    }

    /**
     * Test: User can filter accounts by account type
     */
    public function test_user_can_filter_accounts_by_account_type(): void
    {
        $accountType2 = AccountType::where('id', '!=', $this->accountType->id)->first();

        Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->count(3)
            ->create();

        Account::factory()
            ->forUser($this->user)
            ->forAccountType($accountType2)
            ->count(2)
            ->create();

        $response = $this->actingAs($this->user)
            ->getJson("/api/accounts?account_type_id={$this->accountType->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    /**
     * Test: User can get total balance
     */
    public function test_user_can_get_total_balance(): void
    {
        Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->withBalance(1000000)
            ->create(['currency' => 'VND']);

        Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->withBalance(2000000)
            ->create(['currency' => 'VND']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/accounts/balance/total');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'total_balance' => 3000000,
                    'currency' => 'VND',
                ],
            ]);
    }

    /**
     * Test: Guest cannot access accounts
     */
    public function test_guest_cannot_access_accounts(): void
    {
        $response = $this->getJson('/api/accounts');
        $response->assertStatus(401);

        $response = $this->postJson('/api/accounts', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/accounts/balance/total');
        $response->assertStatus(401);
    }

    /**
     * Test: User can only see own accounts
     */
    public function test_user_can_only_see_own_accounts(): void
    {
        $otherUser = User::factory()->create();

        Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->count(3)
            ->create();

        Account::factory()
            ->forUser($otherUser)
            ->forAccountType($this->accountType)
            ->count(5)
            ->create();

        $response = $this->actingAs($this->user)->getJson('/api/accounts');

        $response->assertStatus(200);

        $this->assertEquals(3, $response->json('pagination.total'));
    }

    /**
     * Test: Account not found returns 404
     */
    public function test_account_not_found_returns_404(): void
    {
        $fakeId = '00000000-0000-0000-0000-000000000000';

        $response = $this->actingAs($this->user)
            ->getJson("/api/accounts/{$fakeId}");

        $response->assertStatus(404);
    }

    /**
     * Test: Account with transactions cannot be deleted
     */
    public function test_account_with_transactions_cannot_be_deleted(): void
    {
        // Seed categories first
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);

        $account = Account::factory()
            ->forUser($this->user)
            ->forAccountType($this->accountType)
            ->create();

        // Create a transaction for this account
        $category = \App\Models\Category::where('is_system', true)->first();
        Transaction::factory()
            ->forUser($this->user->id)
            ->forCategory($category->id)
            ->forAccount($account->id)
            ->create();

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/accounts/{$account->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Không thể xóa tài khoản có giao dịch.',
            ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
        ]);
    }
}
