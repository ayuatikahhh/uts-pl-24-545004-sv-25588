<?php

use App\Support\FailureReason;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Paling depan: semua log selama satu request membawa request_id yang sama.
        $middleware->prepend(\App\Http\Middleware\AddRequestContext::class);

        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Galat basis data dicatat dengan konteks yang aman, lalu laporan bawaan
        // dihentikan (return false). Laporan bawaan menulis pesan asli exception,
        // yang memuat SQL beserta nilainya, misalnya email pengguna.
        $exceptions->report(function (QueryException $e) {
            Log::error('Database operation failed', [
                'user_id' => Auth::hasUser() ? Auth::id() : null,
                'action' => class_basename(request()->route()?->getActionName() ?? 'unknown'),
                'reason' => FailureReason::describe($e),
                'exception' => class_basename($e),
            ]);

            return false;
        });
    })->create();