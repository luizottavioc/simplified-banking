<?php

namespace Tests\Feature\API;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Database\Seeders\UserTypesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(UserTypesSeeder::class);
        $this->seed(UserSeeder::class);
    }

    public function test_register_usual(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'cpf' => '12345678901',
            'email' => 'email@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_register_merchant(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'cnpj' => '12345678901000',
            'email' => 'email@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201);
    }

    public function test_register_small_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'cpf' => '12345678901',
            'email' => 'email@example.com',
            'password' => 'pass',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_error_response_without_cpf_or_cnpj(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'email' => 'email@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_error_response_with_cpf_and_cnpj(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'cpf' => '12345678901',
            'cnpj' => '12345678901000',
            'email' => 'email@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }
    
    public function test_login_admin(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);

        $response->assertStatus(200);
    }

    public function test_login_merchant(): void
    {
        $responseRegister = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'cnpj' => '12345678901000',
            'email' => 'merchant@merchant.com',
            'password' => 'merchant',
        ]);

        $responseRegister->assertStatus(201);

        $responseLogin = $this->postJson('/api/auth/login', [
            'email' => 'merchant@merchant.com',
            'password' => 'merchant',
        ]);

        $responseLogin->assertStatus(200);
    }

    public function test_login_usual(): void
    {
        $responseRegister = $this->postJson('/api/auth/register', [
            'name' => 'name test',
            'cpf' => '12345678901',
            'email' => 'usual@usual.com',
            'password' => 'password',
        ]);

        $responseRegister->assertStatus(201);

        $responseLogin = $this->postJson('/api/auth/login', [
            'email' => 'usual@usual.com',
            'password' => 'password',
        ]);

        $responseLogin->assertStatus(200);
    }

    public function test_login_wrong_password_error_response(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_wrong_email_error_response(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'wrongemail@admin.com',
            'password' => 'admin',
        ]);

        $response->assertStatus(401);
    }

    public function test_get_token_login(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'access_token',
                'token_type',
                'user'
            ]
        ]);
    }

    public function test_get_me(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);

        $response->assertStatus(200);

        $responseBody = $response->json();
        $token = $responseBody['data']['access_token'];
        $response = $this->getJson('/api/auth/me', [
            'Authorization' => "Bearer $token" 
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'data' => [
                'id',
                'name',
                'email',
                'cpf',
                'cnpj',
                'user_type_id',
                'wallet',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ]);
    }

    public function test_logout(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);

        $response->assertStatus(200);
        $responseBody = $response->json();
        $token = $responseBody['data']['access_token'];
        
        $response = $this->getJson('/api/auth/logout', [
            'Authorization' => "Bearer $token" 
        ]);

        $response->assertStatus(200);
    }
}
