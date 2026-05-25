// resources/js/admin/stores/sheep.js
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from './auth'

export const useAdminSheepStore = defineStore('adminSheep', () => {
    const sheep    = ref([])
    const current  = ref(null)
    const loading  = ref(false)
    const error    = ref(null)
    const meta     = ref({ total: 0, current_page: 1, last_page: 1 })
    const counts   = ref({ available: 0, reserved: 0, sold: 0, total: 0 })

    const filters = ref({ status: '', search: '', page: 1 })

    async function fetchSheep() {
        loading.value = true
        error.value   = null
        try {
            const params = { ...filters.value }
            Object.keys(params).forEach(k => { if (!params[k]) delete params[k] })
            const { data } = await api.get('/api/v1/admin/sheep', { params })
            sheep.value  = data.data
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
            const { data } = await api.get(`/api/v1/admin/sheep/${id}`)
            current.value = data.data
            return data.data
        } catch (e) {
            error.value = 'Mouton introuvable.'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function create(payload) {
        const { data } = await api.post('/api/v1/admin/sheep', payload)
        return data
    }

    async function update(id, payload) {
        const { data } = await api.put(`/api/v1/admin/sheep/${id}`, payload)
        return data
    }

    async function remove(id) {
        await api.delete(`/api/v1/admin/sheep/${id}`)
        sheep.value = sheep.value.filter(s => s.id !== id)
        counts.value.total--
    }

    async function uploadPhotos(sheepId, formData) {
        const { data } = await api.post(
            `/api/v1/admin/sheep/${sheepId}/photos`,
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        )
        return data
    }

    async function deletePhoto(sheepId, photoId) {
        await api.delete(`/api/v1/admin/sheep/${sheepId}/photos/${photoId}`)
    }

    async function addVaccination(sheepId, payload) {
        const { data } = await api.post(`/api/v1/admin/sheep/${sheepId}/vaccinations`, payload)
        return data
    }

    async function addWeight(sheepId, payload) {
        const { data } = await api.post(`/api/v1/admin/sheep/${sheepId}/weight`, payload)
        return data
    }

    function setFilter(key, value) {
        filters.value[key] = value
        filters.value.page = 1
        fetchSheep()
    }

    return {
        sheep, current, loading, error, meta, counts, filters,
        fetchSheep, fetchOne, create, update, remove,
        uploadPhotos, deletePhoto, addVaccination, addWeight,
        setFilter,
    }
})
