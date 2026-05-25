// resources/js/admin/stores/orders.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from './auth'

export const useAdminOrderStore = defineStore('adminOrders', () => {
    const orders  = ref([])
    const current = ref(null)
    const loading = ref(false)
    const error   = ref(null)
    const meta    = ref({ total: 0, current_page: 1, last_page: 1 })
    const counts  = ref({ pending: 0, confirmed: 0, delivered: 0 })

    const filters = ref({ status: '', search: '', page: 1 })

    async function fetchOrders() {
        loading.value = true
        try {
            const params = { ...filters.value }
            Object.keys(params).forEach(k => { if (!params[k]) delete params[k] })
            const { data } = await api.get('/api/v1/admin/orders', { params })
            orders.value = data.data
            meta.value   = data.meta
            counts.value = data.counts
        } catch (e) {
            error.value = 'Erreur lors du chargement.'
        } finally {
            loading.value = false
        }
    }

    async function fetchOne(id) {
        loading.value = true
        current.value = null
        try {
            const { data } = await api.get(`/api/v1/admin/orders/${id}`)
            current.value = data.data
            return data.data
        } finally {
            loading.value = false
        }
    }

    async function confirm(id) {
        await api.patch(`/api/v1/admin/orders/${id}/confirm`)
        await fetchOrders()
    }

    async function cancel(id, reason = '') {
        await api.patch(`/api/v1/admin/orders/${id}/cancel`, { reason })
        await fetchOrders()
    }

    async function deliver(id) {
        await api.patch(`/api/v1/admin/orders/${id}/deliver`)
        await fetchOrders()
    }

    function setFilter(key, value) {
        filters.value[key] = value
        filters.value.page = 1
        fetchOrders()
    }

    return {
        orders, current, loading, error, meta, counts, filters,
        fetchOrders, fetchOne, confirm, cancel, deliver, setFilter,
    }
})
