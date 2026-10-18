# Frontend: Pelacak Lamaran Magang

Aplikasi Vue 3 (Vite) yang memanggil API Laravel lewat Axios.

## Menjalankan (tanpa Docker)

Prasyarat: Node.js 20.19 atau lebih baru (atau 22.12+) dan backend sudah berjalan di http://localhost:8000.

    cd frontend
    cp .env.example .env
    npm install
    npm run dev

Buka http://localhost:5173. Permintaan ke `/api` diteruskan Vite ke `http://localhost:8000`.

## Konfigurasi

`VITE_API_URL` menentukan alamat dasar API (bawaan `/api`). Nilainya dibaca saat build.

## Build produksi

    npm run build

Hasilnya ada di `dist/`.