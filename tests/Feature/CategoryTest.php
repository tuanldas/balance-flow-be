<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed system categories
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);

        // Set default headers for all requests in this test
        $this->withHeaders([
            'Accept-Language' => 'vi',
        ]);
    }

    /**
     * Test: User can get all categories (system + their custom) with pagination
     */
    public function test_user_can_get_all_categories(): void
    {
        $user = User::factory()->create();

        // Create some user categories
        Category::factory()->forUser($user->id)->income()->create(['name' => 'User Income']);
        Category::factory()->forUser($user->id)->expense()->create(['name' => 'User Expense']);

        $response = $this->actingAs($user)->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'category_type', 'icon', 'is_system'],
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

        $pagination = $response->json('pagination');

        // Should have pagination info
        $this->assertGreaterThan(15, $pagination['total']);
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(15, $pagination['per_page']);
    }

    /**
     * Test: Pagination works with custom per_page
     */
    public function test_pagination_with_custom_per_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/categories?per_page=5');

        $response->assertStatus(200);

        $data = $response->json('data');
        $pagination = $response->json('pagination');

        $this->assertLessThanOrEqual(5, count($data));
        $this->assertEquals(5, $pagination['per_page']);
    }

    /**
     * Test: User can filter categories by type with pagination
     */
    public function test_user_can_filter_categories_by_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/categories?type=income');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);

        $data = $response->json('data');

        // All categories should be income type
        foreach ($data as $category) {
            $this->assertEquals('income', $category['category_type']);
        }
    }

    /**
     * Test: User can get a single category
     */
    public function test_user_can_get_single_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user->id)->income()->create();

        $response = $this->actingAs($user)->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $category->id,
                    'name' => $category->name,
                ],
            ]);
    }

    /**
     * Test: User can create a new category
     */
    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'My Custom Category',
            'category_type' => 'expense',
            'icon' => 'pets',
        ];

        $response = $this->actingAs($user)->postJson('/api/categories', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Tạo danh mục thành công.',
            ]);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'My Custom Category',
            'category_type' => 'expense',
            'is_system' => false,
        ]);
    }

    /**
     * Test: User can create a subcategory
     */
    public function test_user_can_create_subcategory(): void
    {
        $user = User::factory()->create();

        // Create parent category
        $parent = Category::factory()->forUser($user->id)->expense()->create();

        $data = [
            'name' => 'Subcategory',
            'category_type' => 'expense',
            'parent_id' => $parent->id,
            'icon' => 'subdirectory_arrow_right',
        ];

        $response = $this->actingAs($user)->postJson('/api/categories', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Subcategory',
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Test: Cannot create subcategory with different type than parent
     */
    public function test_cannot_create_subcategory_with_different_type(): void
    {
        $user = User::factory()->create();

        // Create income parent
        $parent = Category::factory()->forUser($user->id)->income()->create();

        $data = [
            'name' => 'Expense Subcategory',
            'category_type' => 'expense', // Different type!
            'parent_id' => $parent->id,
        ];

        $response = $this->actingAs($user)->postJson('/api/categories', $data);

        $response->assertStatus(422);
    }

    /**
     * Test: Validation fails for invalid data
     */
    public function test_validation_fails_for_invalid_data(): void
    {
        $user = User::factory()->create();

        // Missing required fields
        $response = $this->actingAs($user)->postJson('/api/categories', []);
        $response->assertStatus(422);

        // Invalid category_type
        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'Test',
            'category_type' => 'invalid',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test: User can update their own category
     */
    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user->id)->expense()->create();

        $data = [
            'name' => 'Updated Category Name',
            'icon' => 'new_icon',
        ];

        $response = $this->actingAs($user)->putJson("/api/categories/{$category->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công.',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'icon' => 'new_icon',
        ]);
    }

    /**
     * Test: User cannot update system categories
     */
    public function test_user_cannot_update_system_categories(): void
    {
        $user = User::factory()->create();
        $systemCategory = Category::where('is_system', true)->first();

        $data = ['name' => 'Trying to update system category'];

        $response = $this->actingAs($user)->putJson("/api/categories/{$systemCategory->id}", $data);

        $response->assertStatus(422);
    }

    /**
     * Test: User can delete their own category
     */
    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user->id)->expense()->create();

        $response = $this->actingAs($user)->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Xóa danh mục thành công.',
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    /**
     * Test: User cannot delete system categories
     */
    public function test_user_cannot_delete_system_categories(): void
    {
        $user = User::factory()->create();
        $systemCategory = Category::where('is_system', true)->first();

        $response = $this->actingAs($user)->deleteJson("/api/categories/{$systemCategory->id}");

        $response->assertStatus(422);

        // System category should still exist
        $this->assertDatabaseHas('categories', [
            'id' => $systemCategory->id,
        ]);
    }

    /**
     * Test: User cannot delete category with subcategories
     */
    public function test_user_cannot_delete_category_with_subcategories(): void
    {
        $user = User::factory()->create();

        // Create parent and child
        $parent = Category::factory()->forUser($user->id)->expense()->create();
        Category::factory()->forUser($user->id)->expense()->subcategory($parent->id)->create();

        $response = $this->actingAs($user)->deleteJson("/api/categories/{$parent->id}");

        $response->assertStatus(422);

        // Parent should still exist
        $this->assertDatabaseHas('categories', [
            'id' => $parent->id,
        ]);
    }

    /**
     * Test: User can get subcategories of a category
     */
    public function test_user_can_get_subcategories(): void
    {
        $user = User::factory()->create();

        // Find a system category with subcategories (Ăn uống)
        $parent = Category::where('name', 'Ăn uống')->where('is_system', true)->first();

        $response = $this->actingAs($user)->getJson("/api/categories/{$parent->id}/subcategories");

        $response->assertStatus(200);

        $data = $response->json('data');

        // Should have subcategories like Ăn sáng, Ăn trưa, etc.
        $this->assertGreaterThan(0, count($data));

        // All should have the same parent_id
        foreach ($data as $subcategory) {
            $this->assertEquals($parent->id, $subcategory['parent_id']);
        }
    }

    /**
     * Test: Unauthenticated user cannot access API
     */
    public function test_unauthenticated_user_cannot_access_api(): void
    {
        $response = $this->getJson('/api/categories');
        $response->assertStatus(401);

        $response = $this->postJson('/api/categories', []);
        $response->assertStatus(401);
    }

    /**
     * Test: System categories are seeded correctly
     */
    public function test_system_categories_are_seeded(): void
    {
        // Check for income categories
        $this->assertDatabaseHas('categories', [
            'name' => 'Lương',
            'category_type' => 'income',
            'is_system' => true,
        ]);

        // Check for expense categories
        $this->assertDatabaseHas('categories', [
            'name' => 'Ăn uống',
            'category_type' => 'expense',
            'is_system' => true,
        ]);

        // Check for subcategories
        $parent = Category::where('name', 'Ăn uống')->first();
        $this->assertDatabaseHas('categories', [
            'name' => 'Ăn sáng',
            'parent_id' => $parent->id,
            'is_system' => true,
        ]);
    }
}
