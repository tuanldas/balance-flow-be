<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryIconTest extends TestCase
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

        // Use fake storage for testing
        Storage::fake('public');
    }

    /**
     * Test: User can get list of default icons
     */
    public function test_user_can_get_default_icons(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/category-icons');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['name', 'filename', 'url'],
                ],
            ]);

        $data = $response->json('data');
        $this->assertGreaterThan(0, count($data));

        // Check that default icons are returned
        $iconNames = array_column($data, 'name');
        $this->assertContains('wallet', $iconNames);
        $this->assertContains('food', $iconNames);
    }

    /**
     * Test: Unauthenticated user cannot access category icons API
     */
    public function test_unauthenticated_user_cannot_get_icons(): void
    {
        $response = $this->getJson('/api/category-icons');
        $response->assertStatus(401);
    }

    /**
     * Test: User can create category with default icon
     */
    public function test_user_can_create_category_with_default_icon(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'My Category',
            'category_type' => 'income',
            'icon' => 'wallet.png',
        ];

        $response = $this->actingAs($user)->postJson('/api/categories', $data);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Check that icon was copied to user storage
        $category = Category::where('user_id', $user->id)->where('name', 'My Category')->first();
        $this->assertNotNull($category);
        $this->assertStringContains("users/{$user->id}/category-icons/wallet.png", $category->getRawOriginal('icon'));

        // Check file exists in storage
        Storage::disk('public')->assertExists("users/{$user->id}/category-icons/wallet.png");
    }

    /**
     * Test: User can create category with uploaded icon
     */
    public function test_user_can_create_category_with_uploaded_icon(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('custom-icon.png', 100, 100);

        $response = $this->actingAs($user)->post('/api/categories', [
            'name' => 'My Category',
            'category_type' => 'expense',
            'icon_file' => $file,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        // Check that icon was uploaded to user storage
        $category = Category::where('user_id', $user->id)->where('name', 'My Category')->first();
        $this->assertNotNull($category);
        $this->assertStringContains("users/{$user->id}/category-icons/", $category->getRawOriginal('icon'));
    }

    /**
     * Test: User can update category icon with default icon
     */
    public function test_user_can_update_category_with_default_icon(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user->id)->expense()->create([
            'icon' => 'old-icon',
        ]);

        $data = [
            'icon' => 'food.svg',
        ];

        $response = $this->actingAs($user)->putJson("/api/categories/{$category->id}", $data);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Check that icon was updated
        $category->refresh();
        $this->assertStringContains("users/{$user->id}/category-icons/food.svg", $category->getRawOriginal('icon'));

        // Check file exists in storage
        Storage::disk('public')->assertExists("users/{$user->id}/category-icons/food.svg");
    }

    /**
     * Test: User can update category icon with uploaded file
     */
    public function test_user_can_update_category_with_uploaded_icon(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user->id)->expense()->create([
            'icon' => 'old-icon',
        ]);

        $file = UploadedFile::fake()->image('new-icon.png', 100, 100);

        $response = $this->actingAs($user)->post("/api/categories/{$category->id}", [
            '_method' => 'PUT',
            'icon_file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Check that icon was updated
        $category->refresh();
        $this->assertStringContains("users/{$user->id}/category-icons/", $category->getRawOriginal('icon'));
    }

    /**
     * Test: Validation rejects invalid icon file type
     */
    public function test_validation_rejects_invalid_icon_file_type(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('icon.txt', 100);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/categories', [
                'name' => 'My Category',
                'category_type' => 'expense',
                'icon_file' => $file,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test: Validation rejects icon file that is too large
     */
    public function test_validation_rejects_large_icon_file(): void
    {
        $user = User::factory()->create();

        // Create a file larger than 512KB
        $file = UploadedFile::fake()->create('icon.png', 1024);

        $response = $this->actingAs($user)
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/categories', [
                'name' => 'My Category',
                'category_type' => 'expense',
                'icon_file' => $file,
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test: Icon file takes priority over icon name
     */
    public function test_icon_file_takes_priority_over_icon_name(): void
    {
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('custom-icon.png', 100, 100);

        $response = $this->actingAs($user)->post('/api/categories', [
            'name' => 'My Category',
            'category_type' => 'expense',
            'icon' => 'wallet.png', // This should be ignored
            'icon_file' => $file,
        ]);

        $response->assertStatus(201);

        // Check that uploaded icon was used, not default icon
        $category = Category::where('user_id', $user->id)->where('name', 'My Category')->first();
        $this->assertStringNotContains('wallet.png', $category->getRawOriginal('icon'));
        $this->assertStringContains('.png', $category->getRawOriginal('icon'));
    }

    /**
     * Helper method to check if string contains substring
     */
    protected function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' contains '{$needle}'"
        );
    }

    /**
     * Helper method to check if string does not contain substring
     */
    protected function assertStringNotContains(string $needle, string $haystack): void
    {
        $this->assertFalse(
            str_contains($haystack, $needle),
            "Failed asserting that '{$haystack}' does not contain '{$needle}'"
        );
    }
}
