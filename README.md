# Internship Application Tracker

![CI](https://github.com/ayuatikahhh/uts-pl-24-545004-sv-25588/actions/workflows/ci.yml/badge.svg)

Aplikasi web untuk mencatat dan memantau lamaran magang di satu tempat: perusahaan, posisi, status, dan deadline. Dibangun sebagai proyek Ujian Tengah Semester dengan fokus pada praktik DevOps: pengelolaan repository, CI/CD, containerization, dan orkestrasi dengan Docker Compose.

> **Status: dalam pengerjaan.** Bagian bertanda 🚧 belum tersedia. Lihat [Status pengerjaan](#status-pengerjaan).

## Identitas

| | |
|---|---|
| Nama | Ayu Atikah |
| NIM | 24/545004/SV/25588 |
| Program Studi | Software Engineering |
| Universitas | Universitas Gadjah Mada |
| Mata Kuliah | Konstruksi dan Evolusi Perangkat Lunak |
| Keperluan | Ujian Tengah Semester, Semester Gasal 2026/2027 |

## Latar belakang

Lamaran magang sering tersebar di catatan, chat, dan email. Akibatnya deadline terlewat dan status tiap lamaran sulit dilacak. Aplikasi ini memusatkan data tersebut dan menyediakan REST API yang dilindungi autentikasi token, dengan antarmuka web sederhana.

## Fitur

- Login dan logout dengan token (Laravel Sanctum).
- Daftar, tambah, ubah, dan hapus data lamaran.
- Validasi input dan kode status HTTP yang konsisten (200, 201, 401, 404, 422).
- Data lamaran dibagi untuk semua pengguna yang login.

Di luar cakupan: registrasi, reset password, peran pengguna, data per-user, unggah CV, notifikasi email, pencarian, dan pagination.

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | Laravel (REST API), Laravel Sanctum |
| Frontend | Vue.js, Vue Router, Axios, Vite |
| Database | SQLite (lokal), MySQL (Docker Compose) |
| Container | Docker, Docker Compose |
| CI/CD | GitHub Actions |
| Registry | GitHub Container Registry (ghcr.io) |

## Arsitektur

```
Browser ──► frontend (Nginx, hasil build Vue)  :8080
                │  meneruskan /api/*  (nama service: backend)
                ▼
            backend (Laravel API)  :8000 ──► db (MySQL)  [named volume]
                                   (nama service: db)
```

Browser hanya berbicara dengan frontend. Nginx di container frontend meneruskan permintaan `/api/...` ke service `backend`, dan backend terhubung ke service `db`, keduanya lewat nama service di jaringan Docker Compose (bukan alamat IP laptop). Kode Vue memanggil alamat relatif `/api`, sehingga tidak ada alamat server yang tertanam di kode.

## Struktur repository

```
.
├── backend/                  # Laravel API
├── frontend/                 # Vue.js
├── .github/workflows/        # Workflow CI/CD (GitHub Actions)
├── docs/postman/             # Koleksi Postman
├── docker-compose.yml        # 🚧 Orkestrasi db, backend, frontend
├── .env.example              # 🚧 Contoh konfigurasi (tanpa nilai rahasia)
├── .gitignore
└── README.md
```

## Menjalankan backend (tanpa Docker)

Prasyarat: PHP 8.2 atau lebih baru (dengan ekstensi `pdo_mysql`), Composer, dan MySQL 8 yang sedang berjalan.

    mysql -u root -e "CREATE DATABASE pelacak_magang; CREATE DATABASE pelacak_magang_test;"
    cd backend
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate --seed
    php artisan serve

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `backend/.env` dengan MySQL-mu.

API berjalan di http://localhost:8000/api.

Akun demo dibuat oleh seeder (lihat backend/database/seeders/DatabaseSeeder.php)

## Menjalankan proyek

🚧 Bagian ini diisi setelah Docker Compose selesai. Rencananya:

```
cp .env.example .env
docker compose up -d --build
```

Setelah semua container berjalan:

| Layanan | Alamat |
|---|---|
| Frontend | http://localhost:8080 |
| Backend API | http://localhost:8000/api |

Akun untuk login dibuat oleh seeder dan akan dicantumkan di sini. Akun tersebut hanya untuk keperluan demo.

## Ringkasan API

Semua endpoint kecuali login memerlukan header `Authorization: Bearer <token>`. Saat menguji dengan Postman, sertakan header `Accept: application/json`.

| Method | Endpoint | Keterangan | Respons gagal |
|---|---|---|---|
| POST | `/api/login` | Login, mengembalikan token | 401, 422 |
| POST | `/api/logout` | Logout, mencabut token | 401 |
| GET | `/api/applications` | Daftar lamaran | 401 |
| POST | `/api/applications` | Tambah lamaran (201) | 401, 422 |
| GET | `/api/applications/{id}` | Detail lamaran | 401, 404 |
| PUT | `/api/applications/{id}` | Ubah lamaran | 401, 404, 422 |
| DELETE | `/api/applications/{id}` | Hapus lamaran | 401, 404 |

Data lamaran:

| Field | Tipe | Aturan |
|---|---|---|
| `company` | teks | wajib |
| `position` | teks | wajib |
| `status` | enum | opsional (default `applied`): `applied`, `interview` |
| `deadline` | tanggal | opsional, format `Y-m-d` |

## Pengujian

Feature test backend dijalankan di folder `backend/`:

    php artisan test

Test mencakup login, validasi, dan seluruh endpoint CRUD (kode 200, 201, 401, 404, 422), dan otomatis dijalankan oleh CI pada setiap Pull Request.

## Alur kerja Git

| Branch | Fungsi | Push langsung |
|---|---|---|
| `main` | Versi stabil | Tidak, hanya lewat Pull Request dari `dev` |
| `dev` | Integrasi seluruh fitur | Tidak, hanya lewat Pull Request dari branch kerja |
| `feat/…`, `fix/…`, `docs/…`, `ci/…`, `chore/…` | Pekerjaan harian | Ya |

Aturan:

- Branch kerja dibuat dari `dev`, lalu digabung kembali lewat Pull Request.
- Branch protection pada `main` dan `dev` mewajibkan Pull Request dan status check CI yang lulus sebelum merge.
- Tidak ada push langsung ke `main` dan `dev`.
- Pesan commit mengikuti [Conventional Commits](https://www.conventionalcommits.org/): `type: short description` (ditulis dalam bahasa Inggris), dengan tipe `feat`, `fix`, `docs`, `test`, `ci`, `chore`, dan `refactor`.

## CI/CD

Workflow berada di `.github/workflows/ci.yml`:

| Job | Fungsi |
|---|---|
| `backend-test` | Menjalankan test backend |
| `frontend-build` | Membangun frontend untuk produksi |

🚧 Job `deploy` akan ditambahkan. Job ini hanya berjalan pada push ke `main`: membangun image backend dan frontend lalu mengirimnya ke GitHub Container Registry dengan tag nomor commit.

## Keamanan

- Tidak ada nilai rahasia di repository. Konfigurasi dibaca dari berkas `.env`, yang tidak dilacak Git.
- `.env.example` hanya berisi nama variabel dan nilai contoh.
- Nilai rahasia pipeline disimpan di GitHub Secrets.
- Password akun seeder hanya untuk demo dan tidak dipakai di tempat lain.

## Status pengerjaan

| Komponen | Status |
|---|---|
| Repository, branch, dan branch protection | 🚧 |
| Backend API dan feature test | 🚧 |
| Frontend Vue | 🚧 |
| Workflow CI dua job | 🚧 |
| Dockerfile backend dan frontend | 🚧 |
| Docker Compose | 🚧 |
| Build dan push image ke ghcr.io | 🚧 |

## Lisensi

Proyek akademik. Tidak ada lisensi terbuka; seluruh hak dipegang penulis.