<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api'

const router = useRouter()

const items = ref([])
const loading = ref(true)
const notice = ref('')
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const errors = ref({})
const formError = ref('')
const form = reactive({ company: '', position: '', status: 'applied', deadline: '' })

// API mengirim tanggal Y-m-d; tampilan memakai dd-mm-yyyy. Kosong ditampilkan "-".
function formatDate(value) {
  if (!value) return '-'
  const [y, m, d] = String(value).slice(0, 10).split('-')
  return `${d}-${m}-${y}`
}

function statusLabel(status) {
  return status.charAt(0).toUpperCase() + status.slice(1)
}

async function load() {
  try {
    const { data } = await api.get('/applications')
    items.value = data.data
  } catch (e) {
    if (e.response?.status !== 401) {
      notice.value = 'Tidak dapat terhubung ke server. Coba lagi.'
    }
  } finally {
    loading.value = false
  }
}

function openAdd() {
  editingId.value = null
  Object.assign(form, { company: '', position: '', status: 'applied', deadline: '' })
  errors.value = {}
  formError.value = ''
  showModal.value = true
}

function openEdit(item) {
  editingId.value = item.id
  Object.assign(form, {
    company: item.company,
    position: item.position,
    status: item.status,
    deadline: item.deadline ? String(item.deadline).slice(0, 10) : '',
  })
  errors.value = {}
  formError.value = ''
  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function save() {
  errors.value = {}
  formError.value = ''
  saving.value = true
  const payload = {
    company: form.company,
    position: form.position,
    status: form.status,
    deadline: form.deadline || null,
  }
  try {
    if (editingId.value) {
      await api.put(`/applications/${editingId.value}`, payload)
    } else {
      await api.post('/applications', payload)
    }
    notice.value = ''
    closeModal()
    await load()
  } catch (e) {
    const status = e.response?.status
    if (status === 422) {
      errors.value = e.response.data.errors || {}
    } else if (status === 404) {
      closeModal()
      notice.value = 'Data tidak ditemukan.'
      await load()
    } else if (status !== 401) {
      formError.value = 'Tidak dapat terhubung ke server. Coba lagi.'
    }
  } finally {
    saving.value = false
  }
}

async function remove(item) {
  if (!confirm('Hapus lamaran ini?')) return
  try {
    await api.delete(`/applications/${item.id}`)
    notice.value = ''
    await load()
  } catch (e) {
    const status = e.response?.status
    if (status === 404) {
      notice.value = 'Data tidak ditemukan.'
      await load()
    } else if (status !== 401) {
      notice.value = 'Tidak dapat terhubung ke server. Coba lagi.'
    }
  }
}

async function logout() {
  try {
    await api.post('/logout')
  } catch (e) {
    // Apa pun hasilnya, token di browser tetap dihapus.
  }
  localStorage.removeItem('token')
  router.push('/login')
}

onMounted(load)
</script>

<template>
  <div class="container">
    <header class="flex-between" style="margin-bottom: 32px;">
      <div>
        <h1>Daftar Lamaran</h1>
        <p style="margin-top: 4px;">Pantau status dan tenggat waktu aplikasimu.</p>
      </div>
      <button type="button" class="btn btn-outline" @click="logout">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"></path></svg>
        Keluar
      </button>
    </header>

    <div class="flex-between">
      <div></div>
      <button class="btn btn-primary" @click="openAdd">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path></svg>
        Tambah Lamaran
      </button>
    </div>

    <div v-if="notice" class="error-message" style="margin-top: 16px;">{{ notice }}</div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Perusahaan</th>
            <th>Posisi</th>
            <th>Status</th>
            <th>Deadline</th>
            <th style="text-align: right;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="5" style="color: var(--text-muted);">Memuat data...</td>
          </tr>
          <tr v-else-if="items.length === 0">
            <td colspan="5" style="color: var(--text-muted);">Belum ada lamaran.</td>
          </tr>
          <tr v-for="item in items" :key="item.id">
            <td data-label="Perusahaan" style="color: var(--text-main);"><strong>{{ item.company }}</strong></td>
            <td data-label="Posisi" style="color: var(--text-muted);">{{ item.position }}</td>
            <td data-label="Status"><span :class="['badge', item.status]">{{ statusLabel(item.status) }}</span></td>
            <td data-label="Deadline" style="color: var(--text-muted);">{{ formatDate(item.deadline) }}</td>
            <td data-label="Aksi" style="text-align: right;">
              <div class="gap-2" style="justify-content: flex-end;">
                <button class="btn-icon" title="Ubah" @click="openEdit(item)">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"></path></svg>
                </button>
                <button class="btn-icon danger" title="Hapus" @click="remove(item)">
                  <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path></svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Form -->
  <div class="modal-overlay" :class="{ active: showModal }">
    <div class="modal-content">
      <h2 style="margin-bottom: 24px;">Tambah / Ubah Lamaran</h2>
      <form @submit.prevent="save">
        <div class="form-group">
          <label>Perusahaan</label>
          <input v-model="form.company" type="text" class="form-control" placeholder="Contoh: PT GoTo" required>
          <div v-if="errors.company" class="error-message">{{ errors.company[0] }}</div>
        </div>
        <div class="form-group">
          <label>Posisi</label>
          <input v-model="form.position" type="text" class="form-control" placeholder="Contoh: Software Engineer" required>
          <div v-if="errors.position" class="error-message">{{ errors.position[0] }}</div>
        </div>
        <div class="form-group">
          <label>Status</label>
          <select v-model="form.status" class="form-control">
            <option value="applied">Applied</option>
            <option value="interview">Interview</option>
          </select>
          <div v-if="errors.status" class="error-message">{{ errors.status[0] }}</div>
        </div>
        <div class="form-group">
          <label>Deadline (Opsional)</label>
          <input v-model="form.deadline" type="date" class="form-control">
          <div v-if="errors.deadline" class="error-message">{{ errors.deadline[0] }}</div>
        </div>
        <div v-if="formError" class="error-message">{{ formError }}</div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" @click="closeModal">Batal</button>
          <button type="submit" class="btn btn-primary" :disabled="saving">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
h1, h2 { font-weight: 700; letter-spacing: -0.02em; }
p { color: var(--text-muted); }

/* Lebar konten diperbesar menjadi 1280px */
.container { max-width: 1280px; margin: 0 auto; padding: 32px 24px; }
.flex-between { display: flex; justify-content: space-between; align-items: center; }
.gap-2 { gap: 8px; display: flex; align-items: center; }

.btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: var(--radius); font-weight: 600; font-family: inherit; font-size: 0.95rem; border: 1px solid transparent; cursor: pointer; }
.btn-primary { background-color: var(--primary); color: white; }
.btn-primary:hover { background-color: var(--primary-hover); }
.btn-outline { background-color: transparent; border-color: var(--border); color: var(--text-main); }
.btn-outline:hover { background-color: var(--bg-body); }

.btn-icon { padding: 8px; background: transparent; color: var(--text-muted); border-radius: var(--radius); border: none; cursor: pointer; transition: 0.2s; }
.btn-icon:hover { background-color: var(--bg-body); color: var(--primary); }
.btn-icon.danger:hover { color: var(--danger); background-color: #fee2e2; }

.table-container { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); overflow-x: auto; margin-top: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
table { width: 100%; border-collapse: collapse; text-align: left; }
th { background-color: var(--bg-body); padding: 16px 24px; font-size: 0.85rem; font-weight: 700; color: var(--text-muted); border-bottom: 1px solid var(--border); text-transform: uppercase; letter-spacing: 0.05em; }
td { padding: 16px 24px; border-bottom: 1px solid var(--border); font-size: 0.95rem; font-weight: 500; }
tr:last-child td { border-bottom: none; }

.badge { padding: 6px 14px; border-radius: 99px; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
.badge.applied { background: var(--status-applied-bg); color: var(--status-applied-text); }
.badge.interview { background: var(--status-interview-bg); color: var(--status-interview-text); }

/* Modal Styles */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.4); display: none; align-items: center; justify-content: center; z-index: 50; padding: 24px; backdrop-filter: blur(4px); }
.modal-overlay.active { display: flex; }
.modal-content { background: var(--surface); width: 100%; max-width: 480px; border-radius: var(--radius); padding: 32px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
.form-group { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 0.875rem; font-weight: 600; }
.form-control { width: 100%; padding: 12px 16px; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.95rem; font-family: inherit; font-weight: 500; outline: none; }
.form-control:focus { border-color: var(--primary); }
.modal-footer { margin-top: 32px; display: flex; justify-content: flex-end; gap: 12px; }

/* Pesan error: gaya yang sama dengan halaman Login (tanpa display: none) */
.error-message { color: var(--danger); font-size: 0.85rem; margin-top: 4px; font-weight: 500; }
.btn:disabled { opacity: 0.7; cursor: not-allowed; }

/* Responsif (tambahan di luar desain): di layar sempit tabel menjadi kartu per lamaran */
@media (max-width: 640px) {
  .container { padding: 24px 16px; }
  .flex-between { gap: 12px; }
  .flex-between h1 { font-size: 1.5rem; }
  .flex-between > .btn-primary { flex: 1; justify-content: center; }
  .flex-between > div:empty { display: none; }
  .modal-overlay { padding: 16px; }
  .modal-content { padding: 24px; max-height: 100%; overflow-y: auto; }
  .table-container { overflow-x: visible; background: transparent; border: none; box-shadow: none; }
  table, tbody { display: block; }
  thead { display: none; }
  tr { display: block; background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 6px 16px; margin-bottom: 12px; }
  .table-container tr td { display: flex; justify-content: space-between; align-items: center; gap: 16px; padding: 12px 0; text-align: right; border-bottom: 1px solid var(--border); }
  .table-container tr td:last-child { border-bottom: none; }
  td::before { content: attr(data-label); font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; text-align: left; flex-shrink: 0; }
  td[colspan] { display: block; text-align: left; }
  td[colspan]::before { content: none; }
}
</style>