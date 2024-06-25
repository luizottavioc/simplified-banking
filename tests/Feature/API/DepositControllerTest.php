<?php

namespace Tests\Feature\API;

use App\Contracts\ExternalAuthServiceInterface;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepositControllerTest extends TestCase
{
    use RefreshDatabase;

    public static $userUsual = null;
    public static $tokenUsual = null;

    public static function getHeaderUsualToken(): array
    {
        return ['Authorization' => 'Bearer ' . self::$tokenUsual];
    }

    public function getLoginToken(string $email, string $password): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password,
        ]);

        return $response->json()['data']['access_token'];
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(UserTypesSeeder::class);
        $this->seed(UserSeeder::class);

        self::$userUsual = User::factory()->usual(1000)->create();
        self::$tokenUsual = $this->getLoginToken(self::$userUsual->email, 'password');
    }

    public function externalAuthorizeTrue(): void
    {
        $this->mock(ExternalAuthServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getExternalAuth')->andReturn(true);
        });
    }

    public function externalAuthorizeFalse(): void
    {
        $this->mock(ExternalAuthServiceInterface::class, function ($mock) {
            $mock->shouldReceive('getExternalAuth')->andReturn(false);
        });
    }

    public function test_create_deposit(): void
    {
        $this->externalAuthorizeTrue();
        $initialWallet = self::$userUsual->wallet;

        $data = ['value' => 1299];
        $headers = self::getHeaderUsualToken();

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
        $updatedUser = $responseBody['data']['user'];

        $this->assertEquals($initialWallet + 1299, $updatedUser['wallet']);
    }

    public function test_error_on_create_deposit_as_admin(): void
    {
        $this->externalAuthorizeTrue();
        $tokenAdmin = $this->getLoginToken('admin@admin.com', 'admin');

        $data = ['value' => 1299];
        $headers = ['Authorization' => 'Bearer ' . $tokenAdmin];
        $response = $this->postJson('/api/deposit', $data, $headers);

        $response->assertStatus(403);
    }

    public function test_error_on_create_deposit_as_merchant(): void
    {
        $merchantUser = User::factory()->merchant()->create();
        $tokenMerchant = $this->getLoginToken($merchantUser->email, 'password');

        $data = ['value' => 1299];
        $headers = ['Authorization' => 'Bearer ' . $tokenMerchant];
        $response = $this->postJson('/api/deposit', $data, $headers);

        $response->assertStatus(403);
    }

    public function test_error_on_create_negative_deposit(): void
    {
        $data = ['value' => -1299];
        $headers = self::getHeaderUsualToken();

        $response = $this->postJson('/api/deposit', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_error_on_create_deposit_with_zero_value(): void
    {
        $data = ['value' => 0];
        $headers = self::getHeaderUsualToken();

        $response = $this->postJson('/api/deposit', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_create_withdraw(): void
    {
        $initialWallet = self::$userUsual->wallet;

        $data = ['value' => 500];
        $headers = self::getHeaderUsualToken();

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

    public function test_error_on_create_withdraw_as_admin(): void
    {
        $tokenAdmin = $this->getLoginToken('admin@admin.com', 'admin');

        $data = ['value' => 500];
        $headers = ['Authorization' => 'Bearer ' . $tokenAdmin];
        $responseWithdraw = $this->postJson('/api/deposit/withdraw', $data, $headers);

        $responseWithdraw->assertStatus(403);
    }

    public function test_error_on_create_withdraw_as_merchant(): void
    {
        $merchantUser = User::factory()->merchant()->create();
        $tokenMerchant = $this->getLoginToken($merchantUser->email, 'password');

        $data = ['value' => 500];
        $headers = ['Authorization' => 'Bearer ' . $tokenMerchant];
        $responseWithdraw = $this->postJson('/api/deposit/withdraw', $data, $headers);

        $responseWithdraw->assertStatus(403);
    }

    public function test_error_on_create_withdraw_negative_value(): void
    {
        $data = ['value' => -500];
        $headers = self::getHeaderUsualToken();
        $responseWithdraw = $this->postJson('/api/deposit/withdraw', $data, $headers);

        $responseWithdraw->assertStatus(422);
    }

    public function test_error_on_create_withdraw_zero_value(): void
    {
        $data = ['value' => 0];
        $headers = self::getHeaderUsualToken();
        $responseWithdraw = $this->postJson('/api/deposit/withdraw', $data, $headers);

        $responseWithdraw->assertStatus(422);
    }

    public function test_error_on_create_withdraw_without_enough_money(): void
    {
        $data = ['value' => 5000];
        $headers = self::getHeaderUsualToken();
        $responseWithdraw = $this->postJson('/api/deposit/withdraw', $data, $headers);

        $responseWithdraw->assertStatus(422);
    }

}
