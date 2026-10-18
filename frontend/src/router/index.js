import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import ApplicationsView from '../views/ApplicationsView.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/login', name: 'login', component: LoginView, meta: { title: 'Masuk - Pelacak Internship' } },
    { path: '/', name: 'applications', component: ApplicationsView, meta: { title: 'Daftar Lamaran - Pelacak Internship' } },
  ],
})

// Penjaga rute: tanpa token tidak boleh ke "/", dan yang sudah punya token tidak perlu ke "/login".
router.beforeEach((to) => {
  const hasToken = !!localStorage.getItem('token')
  if (to.name !== 'login' && !hasToken) return { name: 'login' }
  if (to.name === 'login' && hasToken) return { name: 'applications' }
})

// Judul tab mengikuti judul di desain tiap halaman.
router.afterEach((to) => {
  document.title = to.meta.title || 'Pelacak Internship'
})

export default router