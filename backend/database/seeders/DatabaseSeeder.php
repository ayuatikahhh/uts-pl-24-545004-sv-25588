<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun demo. updateOrCreate: aman dijalankan berulang (tidak menggandakan akun).
        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => 'password'],
        );

        // Contoh data lamaran, hanya diisi bila tabel masih kosong.
        if (Application::count() === 0) {
            Application::factory()->count(3)->create();
        }
    }
}