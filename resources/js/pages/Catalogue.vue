<template>
  <div class="catalogue-page">

    <!-- Barre de filtres -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h3>Filtres</h3>
        <button class="btn-reset" @click="sheepStore.resetFilters()">Réinitialiser</button>
      </div>

      <!-- Recherche -->
      <div class="filter-group">
        <label>Rechercher</label>
        <input
          type="search"
          placeholder="Nom, référence, race…"
          :value="sheepStore.filters.search"
          @input="debouncedSearch($event.target.value)"
        />
      </div>

      <!-- Statut -->
      <div class="filter-group">
        <label>Disponibilité</label>
        <div class="radio-group">
          <label v-for="s in statuses" :key="s.value" class="radio-option">
            <input
              type="radio"
              :value="s.value"
              :checked="sheepStore.filters.status === s.value"
              @change="sheepStore.setFilter('status', s.value)"
            />
            {{ s.label }}
          </label>
        </div>
      </div>

      <!-- Genre -->
      <div class="filter-group">
        <label>Genre</label>
        <div class="radio-group">
          <label class="radio-option">
            <input type="radio" value="" :checked="!sheepStore.filters.gender" @change="sheepStore.setFilter('gender', null)" />
            Tous
          </label>
          <label class="radio-option">
            <input type="radio" value="male" :checked="sheepStore.filters.gender === 'male'" @change="sheepStore.setFilter('gender', 'male')" />
            🐏 Béliers
          </label>
          <label class="radio-option">
            <input type="radio" value="female" :checked="sheepStore.filters.gender === 'female'" @change="sheepStore.setFilter('gender', 'female')" />
            🐑 Brebis
          </label>
        </div>
      </div>

      <!-- Races -->
      <div class="filter-group">
        <label>Race</label>
        <select :value="sheepStore.filters.breed_id" @change="sheepStore.setFilter('breed_id', $event.target.value || null)">
          <option value="">Toutes les races</option>
          <option v-for="b in sheepStore.breeds" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>
      </div>

      <!-- Prix -->
      <div class="filter-group">
        <label>Prix (FCFA)</label>
        <div class="range-row">
          <input
            type="number"
            placeholder="Min"
            :value="sheepStore.filters.min_price"
            @change="sheepStore.setFilter('min_price', $event.target.value || null)"
            step="5000"
          />
          <span>–</span>
          <input
            type="number"
            placeholder="Max"
            :value="sheepStore.filters.max_price"
            @change="sheepStore.setFilter('max_price', $event.target.value || null)"
            step="5000"
          />
        </div>
      </div>

      <!-- Poids -->
      <div class="filter-group">
        <label>Poids (kg)</label>
        <div class="range-row">
          <input type="number" placeholder="Min" :value="sheepStore.filters.min_weight" @change="sheepStore.setFilter('min_weight', $event.target.value || null)" />
          <span>–</span>
          <input type="number" placeholder="Max" :value="sheepStore.filters.max_weight" @change="sheepStore.setFilter('max_weight', $event.target.value || null)" />
        </div>
      </div>
    </aside>

    <!-- Contenu principal -->
    <main class="catalogue-main">

      <!-- Header résultats + tri -->
      <div class="results-header">
        <p class="results-count">
          <strong>{{ sheepStore.meta?.total ?? 0 }}</strong> mouton(s) trouvé(s)
        </p>
        <div class="sort-row">
          <label>Trier par :</label>
          <select :value="sheepStore.filters.sort_by" @change="sheepStore.setFilter('sort_by', $event.target.value)">
            <option value="newest">Plus récents</option>
            <option value="price_asc">Prix croissant</option>
            <option value="price_desc">Prix décroissant</option>
            <option value="weight_desc">Plus lourds</option>
            <option value="weight_asc">Plus légers</option>
          </select>
        </div>
      </div>

      <!-- Loader -->
      <div class="loader-wrapper" v-if="sheepStore.loading">
        <div class="skeleton-grid">
          <div class="skeleton-card" v-for="i in 6" :key="i"></div>
        </div>
      </div>

      <!-- Grille de cartes -->
      <div class="cards-grid" v-else-if="sheepStore.sheep.length">
        <SheepCard
          v-for="sheep in sheepStore.sheep"
          :key="sheep.id"
          :sheep="sheep"
          @open="openModal"
        />
      </div>

      <!-- Vide -->
      <div class="empty-state" v-else>
        <p>🐑</p>
        <h3>Aucun mouton trouvé</h3>
        <p>Essayez de modifier vos filtres.</p>
        <button @click="sheepStore.resetFilters()">Réinitialiser les filtres</button>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="sheepStore.meta.last_page > 1">
        <button
          v-for="page in sheepStore.meta.last_page"
          :key="page"
          :class="{ active: page === sheepStore.meta.current_page }"
          @click="sheepStore.fetchSheep(page)"
        >{{ page }}</button>
      </div>

    </main>

    <!-- Modal fiche mouton -->
    <SheepModal
      :sheep-id="selectedSheepId"
      :is-open="modalOpen"
      @close="closeModal"
    />

    <!-- Panier flottant -->
    <CartWidget v-if="cart.count > 0" />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useSheepStore } from '@/stores/sheep'
import { useCartStore } from '@/stores/cart'
import SheepCard  from '@/components/SheepCard.vue'
import SheepModal from '@/components/SheepModal.vue'
import CartWidget from '@/components/CartWidget.vue'

const sheepStore = useSheepStore()
const cart       = useCartStore()

const modalOpen      = ref(false)
const selectedSheepId = ref(null)

const statuses = [
  { value: null,        label: 'Tous' },
  { value: 'available', label: '✅ Disponibles' },
  { value: 'reserved',  label: '⏳ Réservés' },
  { value: 'sold',      label: '🔴 Vendus' },
]

onMounted(() => {
  sheepStore.fetchBreeds()
  sheepStore.fetchSheep()
})

function openModal(sheep) {
  selectedSheepId.value = sheep.id
  modalOpen.value       = true
}

function closeModal() {
  modalOpen.value = false
  setTimeout(() => { selectedSheepId.value = null }, 300)
}

// Debounce pour la recherche
let searchTimeout = null
function debouncedSearch(value) {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => sheepStore.setFilter('search', value), 400)
}
</script>

<style scoped>
.catalogue-page {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 32px;
  max-width: 1280px;
  margin: 0 auto;
  padding: 100px 40px 60px;
  min-height: 100vh;
}
@media (max-width: 768px) { .catalogue-page { grid-template-columns: 1fr; padding: 90px 16px 40px; } }

/* Sidebar */
.sidebar {
  background: white;
  border-radius: 16px;
  padding: 20px;
  height: fit-content;
  position: sticky;
  top: 90px;
  border: 1px solid rgba(139,94,60,0.1);
  box-shadow: 0 2px 16px rgba(0,0,0,0.04);
}
.sidebar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.sidebar-header h3 { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 700; }
.btn-reset { font-size: 0.75rem; color: #8b5e3c; background: none; border: none; cursor: pointer; text-decoration: underline; }

.filter-group { margin-bottom: 20px; }
.filter-group label { display: block; font-size: 0.75rem; font-weight: 600; color: #5a3a20; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.filter-group input[type="search"],
.filter-group input[type="number"],
.filter-group select {
  width: 100%;
  padding: 8px 12px;
  border: 1.5px solid rgba(139,94,60,0.2);
  border-radius: 8px;
  font-size: 0.82rem;
  font-family: 'DM Sans', sans-serif;
  background: #fdf8f2;
  outline: none;
  color: #1a120b;
  transition: border-color 0.2s;
}
.filter-group input:focus, .filter-group select:focus { border-color: #8b5e3c; }

.radio-group { display: flex; flex-direction: column; gap: 6px; }
.radio-option { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; color: #2d1f14; cursor: pointer; }
.radio-option input { accent-color: #8b5e3c; }

.range-row { display: flex; gap: 8px; align-items: center; }
.range-row span { color: #a08070; font-size: 0.85rem; }
.range-row input { flex: 1; }

/* Main */
.results-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
.results-count { font-size: 0.85rem; color: #6b4c35; }
.sort-row { display: flex; align-items: center; gap: 8px; font-size: 0.82rem; }
.sort-row select { padding: 6px 12px; border: 1.5px solid rgba(139,94,60,0.2); border-radius: 8px; font-family: 'DM Sans', sans-serif; font-size: 0.82rem; background: white; cursor: pointer; }

.cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }

/* Skeleton loader */
.skeleton-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
.skeleton-card { height: 380px; background: linear-gradient(90deg, #f0e6d3 25%, #f8f0e8 50%, #f0e6d3 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 16px; }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Empty */
.empty-state { text-align: center; padding: 80px 20px; color: #8b6b55; }
.empty-state p:first-child { font-size: 3rem; margin-bottom: 12px; }
.empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; margin-bottom: 8px; color: #1a120b; }
.empty-state button { margin-top: 16px; background: #8b5e3c; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-family: 'DM Sans', sans-serif; }

/* Pagination */
.pagination { display: flex; justify-content: center; gap: 8px; margin-top: 40px; }
.pagination button { width: 36px; height: 36px; border-radius: 8px; border: 1.5px solid rgba(139,94,60,0.2); background: white; color: #2d1f14; font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all 0.2s; font-size: 0.85rem; }
.pagination button:hover, .pagination button.active { background: #8b5e3c; color: white; border-color: #8b5e3c; }
</style>
