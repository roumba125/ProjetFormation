<template>
  <div class="sheep-list">

    <!-- Header -->
    <div class="page-header">
      <div class="header-stats">
        <span class="stat-pill green">✅ {{ counts.available }} disponibles</span>
        <span class="stat-pill orange">⏳ {{ counts.reserved }} réservés</span>
        <span class="stat-pill gray">🔴 {{ counts.sold }} vendus</span>
      </div>
      <RouterLink :to="{ name: 'admin.sheep.create' }" class="btn-primary">
        + Ajouter un mouton
      </RouterLink>
    </div>

    <!-- Filtres -->
    <div class="filters-bar">
      <input
        v-model="searchQuery"
        type="search"
        placeholder="Rechercher par nom ou référence..."
        class="search-input"
        @input="debouncedSearch"
      />
      <select v-model="statusFilter" @change="store.setFilter('status', statusFilter)" class="filter-select">
        <option value="">Tous les statuts</option>
        <option value="available">Disponibles</option>
        <option value="reserved">Réservés</option>
        <option value="sold">Vendus</option>
      </select>
    </div>

    <!-- Table -->
    <div class="card">
      <div v-if="store.loading" class="loading">
        <div class="spinner"></div> Chargement...
      </div>

      <table class="data-table" v-else-if="store.sheep.length">
        <thead>
          <tr>
            <th>Photo</th>
            <th>Mouton</th>
            <th>Race</th>
            <th>Âge / Poids</th>
            <th>Prix</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="sheep in store.sheep" :key="sheep.id">
            <td>
              <div class="sheep-photo">
                <img v-if="sheep.primary_photo_url" :src="sheep.primary_photo_url" :alt="sheep.name" />
                <span v-else>🐑</span>
              </div>
            </td>
            <td>
              <p class="sheep-name">{{ sheep.name }}</p>
              <p class="sheep-ref">{{ sheep.reference }}</p>
              <span class="gender-tag" :class="sheep.gender">{{ sheep.gender_label }}</span>
            </td>
            <td class="td-breed">{{ sheep.breed?.name }}</td>
            <td>
              <p>{{ sheep.age_formatted }}</p>
              <p class="td-sub">{{ sheep.current_weight }} kg</p>
            </td>
            <td class="td-price">{{ sheep.price_formatted }}</td>
            <td>
              <span class="status-badge" :class="`status-${sheep.status}`">
                {{ sheep.status_label }}
              </span>
              <span v-if="sheep.is_featured" class="featured-tag">⭐</span>
            </td>
            <td>
              <div class="action-btns">
                <RouterLink :to="{ name: 'admin.sheep.show', params: { id: sheep.id } }" class="btn-icon" title="Voir">👁️</RouterLink>
                <RouterLink :to="{ name: 'admin.sheep.edit', params: { id: sheep.id } }" class="btn-icon" title="Modifier">✏️</RouterLink>
                <button class="btn-icon danger" @click="confirmDelete(sheep)" title="Supprimer">🗑️</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="empty-state" v-else>
        <p>🐑</p>
        <p>Aucun mouton trouvé.</p>
        <RouterLink :to="{ name: 'admin.sheep.create' }" class="btn-primary">Ajouter un mouton</RouterLink>
      </div>

      <!-- Pagination -->
      <div class="pagination" v-if="store.meta.last_page > 1">
        <button
          v-for="p in store.meta.last_page"
          :key="p"
          :class="{ active: p === store.meta.current_page }"
          @click="store.setFilter('page', p)"
        >{{ p }}</button>
      </div>
    </div>

    <!-- Modal confirmation suppression -->
    <div class="modal-overlay" v-if="deleteTarget" @click.self="deleteTarget = null">
      <div class="confirm-modal">
        <h3>Supprimer {{ deleteTarget.name }} ?</h3>
        <p>Cette action est irréversible. Le mouton sera archivé.</p>
        <div class="confirm-actions">
          <button @click="deleteTarget = null" class="btn-cancel">Annuler</button>
          <button @click="doDelete" class="btn-danger" :disabled="deleting">
            {{ deleting ? 'Suppression...' : 'Supprimer' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAdminSheepStore } from '@/admin/stores/sheep'

const store = useAdminSheepStore()

const searchQuery  = ref('')
const statusFilter = ref('')
const deleteTarget = ref(null)
const deleting     = ref(false)

const counts = store.counts

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => store.setFilter('search', searchQuery.value), 400)
}

function confirmDelete(sheep) {
  deleteTarget.value = sheep
}

async function doDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await store.remove(deleteTarget.value.id)
    deleteTarget.value = null
  } finally {
    deleting.value = false
  }
}

onMounted(() => store.fetchSheep())
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}
.header-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.stat-pill {
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.78rem;
  font-weight: 600;
}
.stat-pill.green  { background: #d4edda; color: #155724; }
.stat-pill.orange { background: #fff3cd; color: #856404; }
.stat-pill.gray   { background: #e2e3e5; color: #383d41; }

.btn-primary {
  background: #4361ee;
  color: white;
  padding: 10px 20px;
  border-radius: 8px;
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 600;
  transition: background 0.2s;
  border: none;
  cursor: pointer;
  font-family: inherit;
}
.btn-primary:hover { background: #3451d1; }

.filters-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.search-input {
  flex: 1;
  min-width: 200px;
  padding: 10px 14px;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  transition: border-color 0.2s;
}
.search-input:focus { border-color: #4361ee; }
.filter-select {
  padding: 10px 14px;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  background: white;
  cursor: pointer;
}

.card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e9ecef;
  overflow: hidden;
}

.data-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.data-table th {
  text-align: left;
  padding: 12px 16px;
  color: #6c757d;
  font-weight: 600;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
}
.data-table td { padding: 12px 16px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #f8f9fa; }

.sheep-photo {
  width: 48px;
  height: 48px;
  border-radius: 8px;
  overflow: hidden;
  background: #f5ede0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}
.sheep-photo img { width: 100%; height: 100%; object-fit: cover; }

.sheep-name { font-weight: 600; color: #1a1a2e; }
.sheep-ref  { font-size: 0.72rem; color: #6c757d; font-family: monospace; }
.gender-tag { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 0.65rem; font-weight: 600; margin-top: 4px; }
.gender-tag.male   { background: #dbeafe; color: #1e40af; }
.gender-tag.female { background: #fce7f3; color: #9d174d; }

.td-breed { color: #495057; }
.td-sub   { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }
.td-price { font-weight: 600; color: #1a1a2e; white-space: nowrap; }

.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
.status-available { background: #d4edda; color: #155724; }
.status-reserved  { background: #fff3cd; color: #856404; }
.status-sold      { background: #e2e3e5; color: #383d41; }
.featured-tag { margin-left: 6px; font-size: 0.85rem; }

.action-btns { display: flex; gap: 4px; }
.btn-icon {
  width: 32px; height: 32px;
  border-radius: 6px;
  border: 1px solid #dee2e6;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-icon:hover { background: #f8f9fa; }
.btn-icon.danger:hover { background: #fff3f3; border-color: #f8d7da; }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-state p:first-child { font-size: 2.5rem; margin-bottom: 8px; }
.empty-state p { color: #6c757d; margin-bottom: 16px; }

.pagination { display: flex; justify-content: center; gap: 6px; padding: 16px; }
.pagination button { width: 34px; height: 34px; border-radius: 6px; border: 1px solid #dee2e6; background: white; cursor: pointer; font-size: 0.82rem; transition: all 0.2s; }
.pagination button.active, .pagination button:hover { background: #4361ee; color: white; border-color: #4361ee; }

.loading { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 40px; color: #6c757d; }
.spinner { width: 24px; height: 24px; border: 2px solid #dee2e6; border-top-color: #4361ee; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Modal */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 20px; }
.confirm-modal { background: white; border-radius: 12px; padding: 28px; max-width: 400px; width: 100%; }
.confirm-modal h3 { font-size: 1rem; font-weight: 600; color: #1a1a2e; margin-bottom: 8px; }
.confirm-modal p { font-size: 0.85rem; color: #6c757d; margin-bottom: 20px; }
.confirm-actions { display: flex; gap: 10px; justify-content: flex-end; }
.btn-cancel { background: #f8f9fa; border: 1px solid #dee2e6; color: #495057; padding: 9px 18px; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.85rem; }
.btn-danger { background: #e63946; color: white; border: none; padding: 9px 18px; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.85rem; font-weight: 600; }
.btn-danger:disabled { background: #adb5bd; cursor: not-allowed; }
</style>
