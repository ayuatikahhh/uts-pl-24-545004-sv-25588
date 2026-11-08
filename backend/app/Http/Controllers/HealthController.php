<?php

namespace App\Http\Controllers;

use App\Support\FailureReason;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Health check yang benar-benar memeriksa dependensi:
 * basis data (query SELECT 1) dan cache (tulis, baca, hapus satu kunci).
 * 200 bila semuanya sehat, 503 bila ada yang gagal.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check('database', fn () => DB::connection()->select('select 1')),
            'cache' => $this->check('cache', function () {
                $key = 'health:probe:'.Str::random(8);
                Cache::put($key, 'ok', 10);
                $ok = Cache::get($key) === 'ok';
                Cache::forget($key);

                if (! $ok) {
                    throw new \RuntimeException('cache read-back mismatch');
                }
            }),
        ];

        $healthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    /**
     * Menjalankan satu pemeriksaan. Bila gagal, sebabnya dicatat ke log (error)
     * dan respons hanya menyebut bagian yang gagal beserta alasan singkat.
     *
     * @return array{status: string, latency_ms?: float, reason?: string}
     */
    private function check(string $component, callable $probe): array
    {
        $start = microtime(true);

        try {
            $probe();

            return ['status' => 'ok', 'latency_ms' => round((microtime(true) - $start) * 1000, 1)];
        } catch (Throwable $e) {
            $reason = FailureReason::describe($e);

            Log::error('Health check failed', [
                'action' => 'health.check',
                'component' => $component,
                'reason' => $reason,
                'exception' => class_basename($e),
            ]);

            return ['status' => 'fail', 'reason' => $reason];
        }
    }
}