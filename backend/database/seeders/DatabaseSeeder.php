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

        // Lamaran contoh, hanya bila tabel masih kosong. Datanya tetap (tanpa Faker)
        // karena Faker adalah paket dev yang tidak ada di image produksi.
        if (Application::count() === 0) {
            $samples = [
                ['company' => 'PT Teknologi Nusantara', 'position' => 'Backend Developer Intern', 'status' => 'applied', 'deadline' => now()->addDays(14)->toDateString()],
                ['company' => 'Startup Kreasi Digital', 'position' => 'Frontend Developer Intern', 'status' => 'interview', 'deadline' => now()->addDays(21)->toDateString()],
                ['company' => 'CV Solusi Data', 'position' => 'QA Engineer Intern', 'status' => 'applied', 'deadline' => now()->addDays(30)->toDateString()],
            ];

            foreach ($samples as $sample) {
                Application::create($sample);
            }
        }
    }
}