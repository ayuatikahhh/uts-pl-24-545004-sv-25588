import axios from 'axios'

// Alamat API dibaca dari environment (VITE_API_URL). Bila tidak diisi, dipakai "/api".
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { Accept: 'application/json' },
})

// Setiap permintaan membawa token (kalau ada).
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Satu-satunya tempat yang menangani 401: token dihapus, pengguna dialihkan ke /login.
// Permintaan login sendiri dikecualikan, karena 401 di sana artinya kredensial salah.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const isLogin = error.config && error.config.url === '/login'
    if (error.response && error.response.status === 401 && !isLogin) {
      localStorage.removeItem('token')
      window.location.assign('/login')
    }
    return Promise.reject(error)
  },
)

export default api