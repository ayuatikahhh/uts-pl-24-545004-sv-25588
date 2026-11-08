<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StructuredLogTest extends TestCase
{
    use RefreshDatabase;

    /** Kunci yang tidak boleh pernah muncul di konteks log. */
    private const FORBIDDEN_KEYS = ['password', 'token', 'authorization', 'email', 'name'];

    private function assertNoSensitiveKeys(array $context): bool
    {
        return array_intersect(array_map('strtolower', array_keys($context)), self::FORBIDDEN_KEYS) === [];
    }

    public function test_tambah_lamaran_menulis_log_info_berkonteks(): void
    {
        Log::spy();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/applications', ['company' => 'PT A', 'position' => 'Intern'])->assertCreated();

        Log::shouldHaveReceived('info')
            ->withArgs(fn ($message, $context) => $message === 'Application created'
                && $context['user_id'] === $user->id
                && $context['action'] === 'application.create'
                && $this->assertNoSensitiveKeys($context))
            ->once();
    }

    public function test_validasi_gagal_menulis_log_warning_hanya_nama_kolom(): void
    {
        Log::spy();
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/applications', ['company' => 'Rahasia Corp'])->assertStatus(422);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn ($message, $context) => $message === 'Application validation failed'
                && $context['failed_fields'] === ['position']
                && ! str_contains(json_encode($context), 'Rahasia Corp')
                && $this->assertNoSensitiveKeys($context))
            ->once();
    }

    public function test_hapus_lamaran_menulis_log_info(): void
    {
        Log::spy();
        Sanctum::actingAs(User::factory()->create());
        $application = Application::factory()->create();

        $this->deleteJson("/api/applications/{$application->id}")->assertOk();

        Log::shouldHaveReceived('info')
            ->withArgs(fn ($message, $context) => $message === 'Application deleted'
                && $context['application_id'] === $application->id)
            ->once();
    }
}