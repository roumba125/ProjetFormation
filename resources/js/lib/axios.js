// resources/js/lib/axios.js
import axios from 'axios'

const instance = axios.create({
    baseURL: '/',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
})

instance.interceptors.request.use((config) => {
    const token = document.head.querySelector('meta[name="csrf-token"]')
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token.content
    }
    return config
})

instance.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            window.location.href = '/login'
        }
        return Promise.reject(error)
    }
)

export default instance
