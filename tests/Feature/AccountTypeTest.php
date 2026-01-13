<?php

namespace Tests\Feature;

use App\Models\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Accept-Language' => 'vi',
        ]);
    }

    /**
     * Test: User can get list of account types
     */
    public function test_user_can_get_account_types(): void
    {
        $user = User::factory()->create();
        AccountType::factory()->count(3)->create();

        $response = $this->actingAs($user)->getJson('/api/account-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'icon', 'color'],
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);

        $data = $response->json('data');
        $this->assertCount(3, $data);
    }

    /**
     * Test: API returns empty array when no account types exist
     */
    public function test_api_returns_empty_array_when_no_account_types(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/account-types');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [],
            ]);
    }

    /**
     * Test: Unauthenticated user cannot access account types API
     */
    public function test_unauthenticated_user_cannot_get_account_types(): void
    {
        $response = $this->getJson('/api/account-types');

        $response->assertStatus(401);
    }

    /**
     * Test: Account type response contains correct fields
     */
    public function test_account_type_response_contains_correct_fields(): void
    {
        $user = User::factory()->create();
        $accountType = AccountType::factory()->create([
            'name' => 'Tiền mặt',
            'icon' => 'payments',
            'color' => '#4CAF50',
        ]);

        $response = $this->actingAs($user)->getJson('/api/account-types');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Tiền mặt', $data[0]['name']);
        $this->assertEquals('payments', $data[0]['icon']);
        $this->assertEquals('#4CAF50', $data[0]['color']);
    }
}
