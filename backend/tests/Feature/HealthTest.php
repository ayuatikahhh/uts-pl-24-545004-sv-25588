<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_200_saat_database_dan_cache_sehat(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_health_503_dan_menyebut_database_saat_database_mati(): void
    {
        Log::spy();

        // Arahkan koneksi bawaan ke basis data yang tidak ada.
        config([
            'database.connections.broken' => ['driver' => 'sqlite', 'database' => '/nonexistent/db.sqlite', 'prefix' => ''],
            'database.default' => 'broken',
        ]);

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'fail')
            ->assertJsonPath('checks.cache.status', 'ok');

        Log::shouldHaveReceived('error')
            ->withArgs(fn ($message, $context) => $message === 'Health check failed'
                && $context['component'] === 'database')
            ->once();
    }

    public function test_health_503_dan_menyebut_cache_saat_cache_mati(): void
    {
        Cache::shouldReceive('put')->andThrow(new RuntimeException('Connection refused'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'fail')
            ->assertJsonPath('checks.cache.reason', 'cannot connect to server (host down or not found)');
    }
}