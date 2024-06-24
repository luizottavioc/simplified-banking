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
        $response->assertJsonStructure([
            'message',
            'data' => [
                'user',
                'deposit'
            ]
        ]);

        $responseBody = $response->json();
        $newUser = $responseBody['data']['user'];

        $this->assertEquals($initialWallet + 1299, $newUser['wallet']);
    }

    public function test_error_create_deposit_by_non_teller(): void
    {
        $usualUser1 = User::factory()->usual()->create();
        $responseLogin = $this->postJson('/api/auth/login', [
            'email' => $usualUser1->email,
            'password' => 'password',
        ]);

        $responseBody = $responseLogin->json();
        $token = $responseBody['data']['access_token'];

        $headers = [
            'Authorization' => 'Bearer ' . $token,
        ];

        $usualUser2 = User::factory()->usual()->create();
        $data = [
            'user_id' => $usualUser2->id,
            'value' => 1299,
        ];

        $response = $this->postJson('/api/deposit', $data, $headers);

        $response->assertStatus(403);
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

    public function test_error_create_deposit_to_teller(): void
    {
        $userTellerTo = User::factory()->teller()->create();
        $data = [
            'user_id' => $userTellerTo->id,
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

    public function test_error_create_negative_deposit(): void
    {
        $userTo = User::factory()->usual()->create();
        $data = [
            'user_id' => $userTo->id,
            'value' => -1299,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . self::$tokenTeller,
        ];

        $response = $this->postJson('/api/deposit', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_withdraw(): void
    {
        $initialWallet = 1000;
        $usualUser = User::create([
            'name' => 'User To',
            'cpf' => '11111111111',
            'email' => 'user@user.com',
            'password' => bcrypt('password'),
            'user_type_id' => 4,
            'wallet' => $initialWallet,
        ]);

        $responseLogin = $this->postJson('/api/auth/login', [
            'email' => $usualUser->email,
            'password' => 'password',
        ]);

        $responseBodyLogin = $responseLogin->json();
        $usualToken = $responseBodyLogin['data']['access_token'];

        $data = [
            'value' => 500,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $usualToken,
        ];

        $responseWithdraw = $this->postJson('/api/deposit/withdraw', $data, $headers);

        $responseWithdraw->assertStatus(201);
        $responseWithdraw->assertJsonStructure([
            'message',
            'data' => [
                'user',
                'withdraw'
            ]
        ]);

        $responseWithdrawBody = $responseWithdraw->json();
        $newUser = $responseWithdrawBody['data']['user'];
        $this->assertEquals($initialWallet - 500, $newUser['wallet']);
    }
}
