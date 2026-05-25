// resources/js/admin/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const api = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
})

api.interceptors.request.use((config) => {
    const token = document.head.querySelector('meta[name="csrf-token"]')
    if (token) config.headers['X-CSRF-TOKEN'] = token.content
    const authToken = localStorage.getItem('admin_token')
    if (authToken) config.headers['Authorization'] = `Bearer ${authToken}`
    return config
})

export const useAuthStore = defineStore('auth', () => {
    const user      = ref(null)
    const token     = ref(localStorage.getItem('admin_token'))
    const loading   = ref(false)
    const initialized = ref(false)

    const isLoggedIn = computed(() => !!token.value && !!user.value)
    const isAdmin    = computed(() => user.value?.role === 'admin')

    async function init() {
        if (initialized.value) return
        initialized.value = true

        if (!token.value) return

        try {
            const { data } = await api.get('/api/v1/admin/me')
            user.value = data.user
        } catch {
            logout()
        }
    }

    async function login(email, password) {
        loading.value = true
        try {
            const { data } = await api.post('/api/v1/admin/login', { email, password })
            token.value = data.token
            user.value  = data.user
            localStorage.setItem('admin_token', data.token)
            return { success: true }
        } catch (err) {
            return {
                success: false,
                message: err.response?.data?.message ?? 'Identifiants incorrects.'
            }
        } finally {
            loading.value = false
        }
    }

    async function logout() {
        try {
            await api.post('/api/v1/admin/logout')
        } catch {}
        token.value = null
        user.value  = null
        localStorage.removeItem('admin_token')
    }

    return { user, token, loading, isLoggedIn, isAdmin, init, login, logout }
})

export { api }
