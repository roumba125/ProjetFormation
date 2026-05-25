// resources/js/stores/cart.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useCartStore = defineStore('cart', () => {
    const items = ref([])

    const total          = computed(() => items.value.reduce((sum, s) => sum + Number(s.price), 0))
    const totalFormatted = computed(() => new Intl.NumberFormat('fr-FR').format(total.value) + ' FCFA')
    const count          = computed(() => items.value.length)
    const isEmpty        = computed(() => items.value.length === 0)

    function addSheep(sheep) {
        if (items.value.some(s => s.id === sheep.id)) return
        items.value.push(sheep)
    }

    function removeSheep(sheepId) {
        items.value = items.value.filter(s => s.id !== sheepId)
    }

    function hasItem(sheepId) {
        return items.value.some(s => s.id === sheepId)
    }

    function clear() {
        items.value = []
    }

    return { items, total, totalFormatted, count, isEmpty, addSheep, removeSheep, hasItem, clear }
})
