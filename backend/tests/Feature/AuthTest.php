<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_berhasil_mengembalikan_token(): void
    {
        User::factory()->create(['email' => 'demo@example.com']); // password bawaan factory: "password"

        $response = $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_dengan_password_salah_ditolak_401(): void
    {
        User::factory()->create(['email' => 'demo@example.com']);

        $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'salah',
        ])->assertStatus(401);
    }

    public function test_login_dengan_email_tidak_terdaftar_ditolak_401(): void
    {
        $this->postJson('/api/login', [
            'email' => 'tidak-ada@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    }

    public function test_login_tanpa_field_wajib_gagal_422(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_logout_tanpa_token_ditolak_401(): void
    {
        $this->postJson('/api/logout')->assertStatus(401);
    }

    public function test_logout_dengan_token_berhasil(): void
    {
        User::factory()->create(['email' => 'demo@example.com']);

        $token = $this->postJson('/api/login', [
            'email' => 'demo@example.com',
            'password' => 'password',
        ])->json('token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        // Token benar-benar dihapus dari database, jadi tidak bisa dipakai lagi.
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}