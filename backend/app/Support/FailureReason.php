<?php

namespace App\Support;

use PDOException;
use Throwable;

/**
 * Menerjemahkan exception menjadi alasan singkat yang aman ditulis ke log
 * dan ke balasan /health.
 *
 * Pesan asli exception sengaja TIDAK diteruskan: pesan QueryException memuat
 * SQL lengkap beserta nilai binding (misalnya email pengguna), dan pesan
 * koneksi bisa memuat nama pengguna basis data.
 */
class FailureReason
{
    public static function describe(Throwable $e): string
    {
        $pdo = $e instanceof PDOException ? $e : $e->getPrevious();

        if ($pdo instanceof PDOException) {
            // Galat koneksi: errorInfo kosong, kodenya ada di getCode() (angka).
            // Galat query: kodenya ada di errorInfo[1].
            $code = (int) ($pdo->errorInfo[1] ?? (is_int($pdo->getCode()) ? $pdo->getCode() : 0));

            // Daftar kode: https://dev.mysql.com/doc/mysql-errors/8.0/en/
            return match ($code) {
                2002 => 'cannot connect to database server (host down or not found)',
                2006, 2013 => 'database connection lost',
                1045 => 'database access denied (check credentials)',
                1049 => 'database does not exist',
                default => 'database query failed',
            };
        }

        $message = strtolower($e->getMessage());

        if (str_contains($message, 'connection refused')
            || str_contains($message, 'getaddrinfo')
            || str_contains($message, 'name does not resolve')
            || str_contains($message, 'timed out')) {
            return 'cannot connect to server (host down or not found)';
        }

        return 'unexpected error: '.class_basename($e);
    }
}