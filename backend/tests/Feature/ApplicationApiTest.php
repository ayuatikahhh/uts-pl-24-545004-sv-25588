<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApplicationApiTest extends TestCase
{
    use RefreshDatabase;

    private function login(): void
    {
        Sanctum::actingAs(User::factory()->create());
    }

    // ---------- 401: tanpa token ----------

    public function test_semua_endpoint_ditolak_401_tanpa_token(): void
    {
        $application = Application::factory()->create();

        $this->getJson('/api/applications')->assertStatus(401);
        $this->postJson('/api/applications', [])->assertStatus(401);
        $this->getJson("/api/applications/{$application->id}")->assertStatus(401);
        $this->putJson("/api/applications/{$application->id}", [])->assertStatus(401);
        $this->deleteJson("/api/applications/{$application->id}")->assertStatus(401);
    }

    public function test_error_tetap_json_walau_tanpa_header_accept(): void
    {
        // Memakai get() biasa (bukan getJson) untuk membuktikan middleware ForceJsonResponse bekerja.
        $this->get('/api/applications')->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    // ---------- daftar & detail ----------

    public function test_daftar_lamaran_berhasil_200(): void
    {
        $this->login();
        Application::factory()->count(3)->create();

        $this->getJson('/api/applications')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_detail_lamaran_berhasil_200(): void
    {
        $this->login();
        $application = Application::factory()->create(['company' => 'PT A']);

        $this->getJson("/api/applications/{$application->id}")
            ->assertOk()
            ->assertJsonPath('data.company', 'PT A');
    }

    public function test_detail_lamaran_tidak_ada_404(): void
    {
        $this->login();

        $this->getJson('/api/applications/999')->assertNotFound();
    }

    // ---------- tambah ----------

    public function test_tambah_lamaran_berhasil_201(): void
    {
        $this->login();

        $this->postJson('/api/applications', [
            'company' => 'PT A',
            'position' => 'Intern',
            'status' => 'interview',
            'deadline' => '2026-10-20',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.company', 'PT A')
            ->assertJsonPath('data.status', 'interview')
            ->assertJsonPath('data.deadline', '2026-10-20');

        $this->assertDatabaseHas('applications', ['company' => 'PT A', 'position' => 'Intern']);
    }

    public function test_tambah_lamaran_tanpa_status_memakai_bawaan_applied(): void
    {
        $this->login();

        $this->postJson('/api/applications', [
            'company' => 'PT B',
            'position' => 'Intern',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'applied');
    }

    public function test_tambah_lamaran_tanpa_field_wajib_gagal_422(): void
    {
        $this->login();

        $this->postJson('/api/applications', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['company', 'position']);
    }

    public function test_tambah_lamaran_dengan_status_tidak_sah_gagal_422(): void
    {
        $this->login();

        // 'offer' sengaja ditolak: aplikasi ini hanya mengenal applied dan interview.
        $this->postJson('/api/applications', [
            'company' => 'PT A',
            'position' => 'Intern',
            'status' => 'offer',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ---------- ubah ----------

    public function test_ubah_lamaran_berhasil_200(): void
    {
        $this->login();
        $application = Application::factory()->create(['status' => 'applied']);

        $this->putJson("/api/applications/{$application->id}", [
            'company' => 'PT Baru',
            'position' => 'Backend Intern',
            'status' => 'interview',
        ])
            ->assertOk()
            ->assertJsonPath('data.company', 'PT Baru')
            ->assertJsonPath('data.status', 'interview');

        $this->assertDatabaseHas('applications', ['id' => $application->id, 'status' => 'interview']);
    }

    public function test_ubah_lamaran_dengan_data_tidak_valid_gagal_422(): void
    {
        $this->login();
        $application = Application::factory()->create();

        $this->putJson("/api/applications/{$application->id}", ['company' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['company', 'position']);
    }

    public function test_ubah_lamaran_tidak_ada_404(): void
    {
        $this->login();

        $this->putJson('/api/applications/999', [
            'company' => 'PT A',
            'position' => 'Intern',
        ])->assertNotFound();
    }

    // ---------- hapus ----------

    public function test_hapus_lamaran_berhasil_200(): void
    {
        $this->login();
        $application = Application::factory()->create();

        $this->deleteJson("/api/applications/{$application->id}")
            ->assertOk()
            ->assertJson(['message' => 'Lamaran dihapus.']);

        $this->assertDatabaseMissing('applications', ['id' => $application->id]);
    }

    public function test_hapus_lamaran_tidak_ada_404(): void
    {
        $this->login();

        $this->deleteJson('/api/applications/999')->assertNotFound();
    }
}