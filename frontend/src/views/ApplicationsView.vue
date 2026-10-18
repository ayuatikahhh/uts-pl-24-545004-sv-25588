<script setup>
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

async function logout() {
  try {
    await api.post('/logout')
  } catch (e) {
    // Apa pun hasilnya, token di browser tetap dihapus.
  }
  localStorage.removeItem('token')
  router.push('/login')
}
</script>

<template>
  <div style="padding: 24px;">
    <h1>Daftar Lamaran</h1>
    <p>Halaman sementara. Isi lengkapnya dibuat di PR berikutnya.</p>
    <button type="button" @click="logout">Keluar</button>
  </div>
</template>