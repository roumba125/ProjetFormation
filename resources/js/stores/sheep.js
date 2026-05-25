// resources/js/stores/sheep.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/lib/axios'

export const useSheepStore = defineStore('sheep', () => {
    const sheep        = ref([])
    const currentSheep = ref(null)
    const breeds       = ref([])
    const loading      = ref(false)
    const error        = ref(null)

    const meta = ref({
    total: 0,
    per_page: 12,
    current_page: 1,
    last_page: 1,
    from: 0,
    to: 0,
})

    const filters = ref({
        breed_id: null, gender: null, status: 'available',
        min_price: null, max_price: null,
        min_weight: null, max_weight: null,
        sort_by: 'newest', search: '', per_page: 12, page: 1,
    })

    const featuredSheep = computed(() => sheep.value.filter(s => s.is_featured))
    const hasMorePages  = computed(() => meta.value.current_page < meta.value.last_page)

    async function fetchSheep(page = 1) {
        loading.value = true
        error.value   = null
        try {
            const params = { ...filters.value, page }
            Object.keys(params).forEach(k => {
                if (params[k] === null || params[k] === '' || params[k] === undefined) {
                    delete params[k]
                }
            })
            const { data } = await axios.get('/api/v1/sheep', { params })
                sheep.value = data.data ?? []
                meta.value  = data.meta ?? meta.value
        } catch (err) {
            error.value = 'Erreur lors du chargement du catalogue.'
        } finally {
            loading.value = false
        }
    }

    async function fetchSheepDetail(id) {
        loading.value      = true
        currentSheep.value = null
        error.value        = null
        try {
            const { data } = await axios.get(`/api/v1/sheep/${id}`)
            currentSheep.value = data.data
            return data.data
        } catch (err) {
            error.value = 'Impossible de charger la fiche.'
            throw err
        } finally {
            loading.value = false
        }
    }

    async function fetchBreeds() {
        try {
            const { data } = await axios.get('/api/v1/breeds')
            breeds.value = data.data
        } catch (err) {
            console.error('Breeds error:', err)
        }
    }

    async function fetchWeightHistory(sheepId) {
        const { data } = await axios.get(`/api/v1/sheep/${sheepId}/weight-history`)
        return data
    }

    function setFilter(key, value) {
        filters.value[key] = value
        fetchSheep(1)
    }

    function resetFilters() {
        filters.value = {
            breed_id: null, gender: null, status: 'available',
            min_price: null, max_price: null,
            min_weight: null, max_weight: null,
            sort_by: 'newest', search: '', per_page: 12, page: 1,
        }
        fetchSheep(1)
    }

    return {
        sheep, currentSheep, breeds, loading, error, meta, filters,
        featuredSheep, hasMorePages,
        fetchSheep, fetchSheepDetail, fetchBreeds, fetchWeightHistory,
        setFilter, resetFilters,
    }
})
