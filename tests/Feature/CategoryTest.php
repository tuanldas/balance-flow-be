<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /**
     * Test: Unauthenticated users cannot access category endpoints
     */
    public function test_unauthenticated_users_cannot_access_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401);
    }

    /**
     * Test: Authenticated user can list all accessible categories (system + own)
     */
    public function test_user_can_list_accessible_categories(): void
    {
        Passport::actingAs($this->user);

        // Create system categories
        $systemCategory = Category::factory()->system()->income()->create();

        // Create user's own categories
        $userCategory = Category::factory()->income()->create(['user_id' => $this->user->id]);

        // Create other user's categories (should not be accessible)
        Category::factory()->expense()->create(['user_id' => $this->otherUser->id]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'categories' => [
                        '*' => [
                            'id',
                            'name',
                            'original_name',
                            'type',
                            'icon_url',
                            'is_system',
                            'user_id',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ])
            ->assertJsonCount(2, 'data.categories');
    }

    /**
     * Test: User can filter categories by type
     */
    public function test_user_can_filter_categories_by_type(): void
    {
        Passport::actingAs($this->user);

        // Create categories of both types
        Category::factory()->income()->create(['user_id' => $this->user->id]);
        Category::factory()->income()->create(['user_id' => $this->user->id]);
        Category::factory()->expense()->create(['user_id' => $this->user->id]);

        // Filter by income
        $response = $this->getJson('/api/categories?type=income');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.categories');

        // Filter by expense
        $response = $this->getJson('/api/categories?type=expense');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.categories');
    }

    /**
     * Test: Invalid type filter returns validation error
     */
    public function test_invalid_type_filter_returns_error(): void
    {
        Passport::actingAs($this->user);

        $response = $this->getJson('/api/categories?type=invalid');

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test: User can create a category
     */
    public function test_user_can_create_category(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->user);

        $categoryData = [
            'name' => 'My Custom Category',
            'type' => 'income',
            'icon' => UploadedFile::fake()->image('icon.png', 25, 25),
        ];

        $response = $this->postJson('/api/categories', $categoryData);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category.name', 'My Custom Category')
            ->assertJsonPath('data.category.type', 'income')
            ->assertJsonPath('data.category.is_system', false)
            ->assertJsonPath('data.category.user_id', $this->user->id);

        $this->assertDatabaseHas('categories', [
            'name' => 'My Custom Category',
            'type' => 'income',
            'user_id' => $this->user->id,
            'is_system' => false,
        ]);

        // Assert file was stored
        $category = Category::where('name', 'My Custom Category')->first();
        Storage::disk('public')->assertExists($category->icon_path);
    }

    /**
     * Test: Creating category with invalid data returns validation errors
     */
    public function test_create_category_validation_errors(): void
    {
        Storage::fake('public');
        Passport::actingAs($this->user);

        // Missing required fields
        $response = $this->postJson('/api/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'icon']);

        // Invalid type
        $response = $this->postJson('/api/categories', [
            'name' => 'Test Category',
            'type' => 'invalid',
            'icon' => UploadedFile::fake()->image('icon.png'),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    /**
     * Test: User can view a category they have access to
     */
    public function test_user_can_view_category(): void
    {
        Passport::actingAs($this->user);

        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.category.name', $category->name);
    }

    /**
     * Test: User can view system categories
     */
    public function test_user_can_view_system_category(): void
    {
        Passport::actingAs($this->user);

        $systemCategory = Category::factory()->system()->create();

        $response = $this->getJson("/api/categories/{$systemCategory->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category.is_system', true);
    }

    /**
     * Test: User cannot view other user's categories
     */
    public function test_user_cannot_view_other_users_category(): void
    {
        Passport::actingAs($this->user);

        $otherCategory = Category::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->getJson("/api/categories/{$otherCategory->id}");

        $response->assertStatus(403);
    }

    /**
     * Test: User can update their own category
     */
    public function test_user_can_update_own_category(): void
    {
        Passport::actingAs($this->user);

        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Category Name',
            'type' => 'expense',
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.category.name', 'Updated Category Name')
            ->assertJsonPath('data.category.type', 'expense');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'type' => 'expense',
        ]);
    }

    /**
     * Test: User cannot update system categories
     */
    public function test_user_cannot_update_system_category(): void
    {
        Passport::actingAs($this->user);

        $systemCategory = Category::factory()->system()->create();

        $updateData = [
            'name' => 'Trying to Update System Category',
        ];

        $response = $this->putJson("/api/categories/{$systemCategory->id}", $updateData);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);

        // Verify database was not updated
        $this->assertDatabaseMissing('categories', [
            'id' => $systemCategory->id,
            'name' => 'Trying to Update System Category',
        ]);
    }

    /**
     * Test: User cannot update other user's categories
     */
    public function test_user_cannot_update_other_users_category(): void
    {
        Passport::actingAs($this->user);

        $otherCategory = Category::factory()->create(['user_id' => $this->otherUser->id]);

        $updateData = [
            'name' => 'Trying to Update',
        ];

        $response = $this->putJson("/api/categories/{$otherCategory->id}", $updateData);

        $response->assertStatus(403);
    }

    /**
     * Test: User can delete their own category without transactions
     */
    public function test_user_can_delete_own_category_without_transactions(): void
    {
        Passport::actingAs($this->user);

        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test: Cannot delete category with transactions without specifying transfer target
     */
    public function test_cannot_delete_category_with_transactions_without_transfer(): void
    {
        Passport::actingAs($this->user);

        $category = Category::factory()->create(['user_id' => $this->user->id, 'type' => 'income']);

        // Create transactions for this category
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'income',
        ]);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(400)
            ->assertJsonPath('success', false);

        // Verify category was not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test: User can delete category and transfer transactions to another category
     */
    public function test_user_can_delete_category_with_transaction_transfer(): void
    {
        Passport::actingAs($this->user);

        $oldCategory = Category::factory()->income()->create(['user_id' => $this->user->id]);
        $newCategory = Category::factory()->income()->create(['user_id' => $this->user->id]);

        // Create transactions for old category
        $transactions = Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'category_id' => $oldCategory->id,
            'type' => 'income',
        ]);

        $response = $this->deleteJson("/api/categories/{$oldCategory->id}", [
            'transfer_to_category_id' => $newCategory->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify old category was deleted
        $this->assertDatabaseMissing('categories', [
            'id' => $oldCategory->id,
        ]);

        // Verify transactions were transferred
        foreach ($transactions as $transaction) {
            $this->assertDatabaseHas('transactions', [
                'id' => $transaction->id,
                'category_id' => $newCategory->id,
            ]);
        }
    }

    /**
     * Test: Cannot transfer transactions to category of different type
     */
    public function test_cannot_transfer_to_category_of_different_type(): void
    {
        Passport::actingAs($this->user);

        $incomeCategory = Category::factory()->income()->create(['user_id' => $this->user->id]);
        $expenseCategory = Category::factory()->expense()->create(['user_id' => $this->user->id]);

        // Create income transactions
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'category_id' => $incomeCategory->id,
            'type' => 'income',
        ]);

        $response = $this->deleteJson("/api/categories/{$incomeCategory->id}", [
            'transfer_to_category_id' => $expenseCategory->id,
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);

        // Verify category was not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $incomeCategory->id,
        ]);
    }

    /**
     * Test: User cannot delete system categories
     */
    public function test_user_cannot_delete_system_category(): void
    {
        Passport::actingAs($this->user);

        $systemCategory = Category::factory()->system()->create();

        $response = $this->deleteJson("/api/categories/{$systemCategory->id}");

        $response->assertStatus(403)
            ->assertJsonPath('success', false);

        // Verify category was not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $systemCategory->id,
        ]);
    }

    /**
     * Test: User cannot delete other user's categories
     */
    public function test_user_cannot_delete_other_users_category(): void
    {
        Passport::actingAs($this->user);

        $otherCategory = Category::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->deleteJson("/api/categories/{$otherCategory->id}");

        $response->assertStatus(403);

        // Verify category was not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $otherCategory->id,
        ]);
    }

    /**
     * Test: Get transaction count for a category
     */
    public function test_get_transaction_count_for_category(): void
    {
        Passport::actingAs($this->user);

        $category = Category::factory()->create(['user_id' => $this->user->id, 'type' => 'income']);

        // Create transactions
        Transaction::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'income',
        ]);

        $response = $this->getJson("/api/categories/{$category->id}/transactions-count");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.count', 5);
    }

    /**
     * Test: Cannot transfer to non-existent category
     */
    public function test_cannot_transfer_to_non_existent_category(): void
    {
        Passport::actingAs($this->user);

        $category = Category::factory()->income()->create(['user_id' => $this->user->id]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'income',
        ]);

        $fakeUuid = '00000000-0000-0000-0000-000000000000';

        $response = $this->deleteJson("/api/categories/{$category->id}", [
            'transfer_to_category_id' => $fakeUuid,
        ]);

        $response->assertStatus(400);

        // Verify category was not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test: Cannot transfer to category user doesn't have access to
     */
    public function test_cannot_transfer_to_unauthorized_category(): void
    {
        Passport::actingAs($this->user);

        $userCategory = Category::factory()->income()->create(['user_id' => $this->user->id]);
        $otherCategory = Category::factory()->income()->create(['user_id' => $this->otherUser->id]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $userCategory->id,
            'type' => 'income',
        ]);

        $response = $this->deleteJson("/api/categories/{$userCategory->id}", [
            'transfer_to_category_id' => $otherCategory->id,
        ]);

        $response->assertStatus(403);

        // Verify category was not deleted
        $this->assertDatabaseHas('categories', [
            'id' => $userCategory->id,
        ]);
    }

    /**
     * Test: User can transfer to system category
     */
    public function test_user_can_transfer_to_system_category(): void
    {
        Passport::actingAs($this->user);

        $userCategory = Category::factory()->income()->create(['user_id' => $this->user->id]);
        $systemCategory = Category::factory()->system()->income()->create();

        $transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $userCategory->id,
            'type' => 'income',
        ]);

        $response = $this->deleteJson("/api/categories/{$userCategory->id}", [
            'transfer_to_category_id' => $systemCategory->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Verify transaction was transferred to system category
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $systemCategory->id,
        ]);
    }
}
