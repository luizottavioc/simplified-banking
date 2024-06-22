<?php

namespace Tests\Feature\API;

use App\Models\User;
use Database\Factories\UserUsualFactory;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTypesSeeder;
use Database\Seeders\UserUsualSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepositControllerTest extends TestCase
{
    use RefreshDatabase;

    static $tokenTeller;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(UserTypesSeeder::class);
        $this->seed(UserSeeder::class);


        $response = $this->postJson('/api/auth/login', [
            'email' => 'teller@teller.com',
            'password' => 'teller',
        ]);

        $responseBody = $response->json();
        self::$tokenTeller = $responseBody['data']['access_token'];
    }

    public function test_create_deposit(): void
    {
        $userTo = User::factory()->usual()->create();
        $initialWallet = $userTo->wallet;

        $data = [
            'user_id' => $userTo->id,
            'value' => 1299,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . self::$tokenTeller,
        ];

        $response = $this->postJson('/api/deposit', $data, $headers);
        $response->assertStatus(201);

        $this->assertEquals($initialWallet + 1299, $userTo->refresh()->wallet);
    }

    public function test_error_create_deposit_to_admin(): void
    {
        $data = [
            'user_id' => 1,
            'value' => 1299,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . self::$tokenTeller,
        ];

        $response = $this->postJson('/api/deposit', $data, $headers);

        $response->assertStatus(422);
    }

    public function test_error_create_deposit_to_merchant(): void
    {
        $userTo = User::factory()->merchant()->create();

        $data = [
            'user_id' => $userTo->id,
            'value' => 1299,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . self::$tokenTeller,
        ];

        $response = $this->postJson('/api/deposit', $data, $headers);

        $response->assertStatus(422);
    }
}
