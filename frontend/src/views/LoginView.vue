<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function login() {
  error.value = ''
  loading.value = true
  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })
    localStorage.setItem('token', data.token)
    router.push('/')
  } catch (e) {
    const status = e.response?.status
    if (status === 401) {
      error.value = 'Kredensial salah atau tidak ditemukan.'
    } else if (status === 422) {
      const errors = e.response.data.errors || {}
      error.value = Object.values(errors)[0]?.[0] || 'Data yang dikirim tidak valid.'
    } else if (status === 429) {
      error.value = 'Terlalu banyak percobaan. Coba lagi sebentar.'
    } else {
      error.value = 'Tidak dapat terhubung ke server. Coba lagi.'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container">
    <div class="card">
      <div style="text-align: center; margin-bottom: 32px;">
        <div style="width: 48px; height: 48px; background: var(--primary-light); color: var(--primary); border-radius: var(--radius); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px;">
          <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg>
        </div>
        <h2>Masuk ke Akun</h2>
        <p style="margin-top: 4px; font-size: 0.95rem;">Lacak progres magangmu di satu tempat.</p>
      </div>

      <form @submit.prevent="login">
        <div class="form-group">
          <label>Email</label>
          <input v-model="email" type="email" name="email" class="form-control" placeholder="nama@email.com" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input v-model="password" type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div v-if="error" class="error-message" style="margin-bottom: 16px; display: block;">
          {{ error }}
        </div>

        <button type="submit" class="btn btn-primary" :disabled="loading">Masuk</button>
      </form>
    </div>
  </div>
</template>

<style scoped>
h2 { color: var(--text-main); font-weight: 600; letter-spacing: -0.02em; }
p { color: var(--text-muted); }
.container { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; line-height: 1.5; }
.card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 32px; width: 100%; max-width: 420px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); }
.form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 0.875rem; font-weight: 600; }
.form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.95rem; font-family: inherit; outline: none; transition: border-color 0.2s; }
.form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }
.error-message { color: var(--danger); font-size: 0.85rem; margin-top: 4px; display: none; font-weight: 500; }
.btn { display: inline-flex; align-items: center; justify-content: center; padding: 12px 20px; border-radius: var(--radius); font-weight: 600; font-family: inherit; border: none; cursor: pointer; transition: all 0.2s; width: 100%; font-size: 0.95rem; }
.btn-primary { background-color: var(--primary); color: white; }
.btn-primary:hover { background-color: var(--primary-hover); }
.btn:disabled { opacity: 0.7; cursor: not-allowed; }
</style>

