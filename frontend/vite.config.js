import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// Saat pengembangan lokal, semua permintaan ke /api diteruskan ke backend Laravel.
// Di Docker, tugas ini diambil alih oleh Nginx (lihat file 05 dan 06).
export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    proxy: {
      '/api': 'http://localhost:8000',
    },
  },
})