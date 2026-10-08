<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Services\ClientAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientAccountStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_client_cannot_login_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $user->id,
            'company_name' => 'Test Co',
            'whatsapp' => '081234567890',
            'status' => 'inactive',
            'devices_max' => 3,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'inactive@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'account_inactive' => true,
            ]);
    }

    public function test_active_verified_client_can_login_via_api(): void
    {
        $user = User::factory()->create([
            'email' => 'active@test.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        Client::create([
            'user_id' => $user->id,
            'company_name' => 'Test Co',
            'status' => 'active',
            'whatsapp' => '081234567890',
            'devices_max' => 3,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'active@test.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_unverified_client_gets_verification_message_not_inactive(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@test.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'email_verified_at' => null,
        ]);

        Client::create([
            'user_id' => $user->id,
            'company_name' => 'Test Co',
            'whatsapp' => '081234567890',
            'status' => 'active',
            'devices_max' => 3,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'unverified@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'requires_verification' => true,
            ]);
    }

    public function test_inactive_client_api_token_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'token@test.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        $client = Client::create([
            'user_id' => $user->id,
            'company_name' => 'Test Co',
            'whatsapp' => '081234567890',
            'status' => 'active',
            'devices_max' => 3,
        ]);

        $auth = app(ClientAuthService::class);
        $session = $auth->createApiToken($user);
        $token = $session['token'];

        $client->update(['status' => 'inactive']);

        $response = $this->getJson('/api/auth/me', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'account_inactive' => true,
            ]);
    }
}
