<?php

namespace Tests\Feature\API;

use App\Contracts\ExternalAuthServiceInterface;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransferControllerTest extends TestCase
{
    use RefreshDatabase;

    public static function getHeaderToken(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
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

    public function test_create_transfer(): void
    {
        $this->externalAuthorizeTrue();

        $initialPayerWallet = 1000;
        $initialPayeeWallet = 1000;

        $payerUser = User::factory()->usual($initialPayerWallet)->create();
        $payeeUser = User::factory()->usual($initialPayeeWallet)->create();

        $tokenPayer = $this->getLoginToken($payerUser->email, 'password');

        $data = ['payee_id' => $payeeUser->id, 'value' => 500];
        $headers = ['Authorization' => 'Bearer ' . $tokenPayer];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(201);
    }

    public function test_transfer_changes_users_wallets(): void
    {
        $this->externalAuthorizeTrue();

        $initialPayerWallet = 1000;
        $initialPayeeWallet = 1000;
        $transferValue = 500;

        $payerUser = User::factory()->usual($initialPayerWallet)->create();
        $payeeUser = User::factory()->usual($initialPayeeWallet)->create();

        $tokenPayer = $this->getLoginToken($payerUser->email, 'password');

        $data = ['payee_id' => $payeeUser->id, 'value' => $transferValue];
        $headers = ['Authorization' => 'Bearer ' . $tokenPayer];

        $response = $this->postJson('/api/transfer', $data, $headers);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'transfer',
                'payer',
                'payee'
            ]
        ]);

        $responseBody = $response->json();
        $updatedPayer = $responseBody['data']['payer'];
        $updatedPayee = $responseBody['data']['payee'];

        $this->assertEquals($initialPayeeWallet - $transferValue, $updatedPayer['wallet']);
        $this->assertEquals($initialPayerWallet + $transferValue, $updatedPayee['wallet']);
    }

    public function test_error_on_create_transfer_as_admin(): void
    {
        $this->externalAuthorizeTrue();

        $tokenAdmin = $this->getLoginToken('admin@admin.com', 'admin');
        $headers = $this->getHeaderToken($tokenAdmin);
        $data = ['payee_id' => 1, 'value' => 500];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(403);
    }

    public function test_error_on_create_transfer_as_merchant(): void
    {
        $this->externalAuthorizeTrue();

        $usualUser = User::factory()->usual()->create();

        $merchantUser = User::factory()->merchant(1000)->create();
        $tokenMerchant = $this->getLoginToken($merchantUser->email, 'password');

        $headers = $this->getHeaderToken($tokenMerchant);
        $data = ['payee_id' => $usualUser->id, 'value' => 500];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(403);
    }

    public function test_error_on_create_transfer_to_yourself(): void
    {
        $this->externalAuthorizeTrue();

        $usualUser = User::factory()->usual()->create();
        $tokenPayer = $this->getLoginToken($usualUser->email, 'password');
        $headers = $this->getHeaderToken($tokenPayer);
        $data = ['payee_id' => $usualUser->id, 'value' => 500];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_error_on_create_transfer_with_negative_value(): void
    {
        $this->externalAuthorizeTrue();

        $payer = User::factory()->usual()->create();
        $payee = User::factory()->usual()->create();

        $token = $this->getLoginToken($payer->email, 'password');
        $headers = $this->getHeaderToken($token);
        $data = ['payee_id' => $payee->id, 'value' => -500];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_error_on_create_transfer_with_zero_value(): void
    {
        $this->externalAuthorizeTrue();

        $payer = User::factory()->usual()->create();
        $payee = User::factory()->usual()->create();

        $token = $this->getLoginToken($payer->email, 'password');
        $headers = $this->getHeaderToken($token);
        $data = ['payee_id' => $payee->id, 'value' => 0];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_error_on_create_transfer_with_insufficient_funds(): void
    {
        $this->externalAuthorizeTrue();

        $payer = User::factory()->usual(1000)->create();
        $payee = User::factory()->usual()->create();

        $token = $this->getLoginToken($payer->email, 'password');
        $headers = $this->getHeaderToken($token);
        $data = ['payee_id' => $payee->id, 'value' => 1001];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(422);
    }

    public function test_error_on_create_transfer_with_invalid_payee_id(): void
    {
        $this->externalAuthorizeTrue();

        $payer = User::factory()->usual()->create();
        $token = $this->getLoginToken($payer->email, 'password');
        $headers = $this->getHeaderToken($token);
        $data = ['payee_id' => 0, 'value' => 500];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(404);
    }

    public function test_error_on_create_transfer_with_nonexistent_payee_id(): void
    {
        $this->externalAuthorizeTrue();

        $payer = User::factory()->usual(1000)->create();
        $token = $this->getLoginToken($payer->email, 'password');
        $headers = $this->getHeaderToken($token);
        $data = ['payee_id' => 123123, 'value' => 500];

        $response = $this->postJson('/api/transfer', $data, $headers);
        $response->assertStatus(404);
    }
}
