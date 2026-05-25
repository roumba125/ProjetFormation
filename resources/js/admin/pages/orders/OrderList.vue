<!-- ============================================================
  resources/js/admin/pages/orders/OrderList.vue
============================================================ -->
<template>
  <div class="order-list">

    <div class="page-header">
      <div class="header-stats">
        <span class="stat-pill orange">⏳ {{ store.counts.pending }} en attente</span>
        <span class="stat-pill blue">✅ {{ store.counts.confirmed }} confirmées</span>
        <span class="stat-pill green">🚚 {{ store.counts.delivered }} livrées</span>
      </div>
    </div>

    <div class="filters-bar">
      <input v-model="searchQuery" type="search" placeholder="N° commande, client, téléphone..." class="search-input" @input="debouncedSearch" />
      <select v-model="statusFilter" @change="store.setFilter('status', statusFilter)" class="filter-select">
        <option value="">Tous les statuts</option>
        <option value="pending">En attente</option>
        <option value="confirmed">Confirmées</option>
        <option value="paid">Payées</option>
        <option value="delivered">Livrées</option>
        <option value="cancelled">Annulées</option>
      </select>
    </div>

    <div class="card">
      <div v-if="store.loading" class="loading"><div class="spinner"></div> Chargement...</div>

      <table class="data-table" v-else-if="store.orders.length">
        <thead>
          <tr>
            <th>N° Commande</th>
            <th>Client</th>
            <th>Moutons</th>
            <th>Total</th>
            <th>Paiement</th>
            <th>Livraison</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in store.orders" :key="order.id">
            <td class="mono">{{ order.order_number }}</td>
            <td>
              <p class="td-name">{{ order.client_name }}</p>
              <p class="td-sub">{{ order.client_phone }}</p>
              <p class="td-sub">{{ order.client_city }}</p>
            </td>
            <td class="td-center">{{ order.items?.length ?? 0 }}</td>
            <td>
              <p class="td-amount">{{ fmt(order.total_amount) }} FCFA</p>
              <p class="td-sub" v-if="order.remaining_amount > 0">Reste: {{ fmt(order.remaining_amount) }}</p>
            </td>
            <td>
              <span class="badge" :class="`pay-${order.payment_status}`">{{ order.payment_status_label ?? order.payment_status }}</span>
            </td>
            <td>
              <span class="delivery-tag">{{ order.delivery_method === 'delivery' ? '🚚' : '🏠' }}</span>
              <p class="td-sub">{{ order.delivery_method === 'delivery' ? 'Livraison' : 'Retrait' }}</p>
            </td>
            <td>
              <span class="status-badge" :class="`status-${order.status}`">{{ order.status_label ?? order.status }}</span>
            </td>
            <td>
              <div class="action-btns">
                <RouterLink :to="{ name: 'admin.orders.show', params: { id: order.id } }" class="btn-icon" title="Voir">👁️</RouterLink>
                <button v-if="order.status === 'pending'" @click="confirmOrder(order.id)" class="btn-icon green" title="Confirmer">✅</button>
                <button v-if="order.status === 'confirmed'" @click="deliverOrder(order.id)" class="btn-icon blue" title="Marquer livré">🚚</button>
                <button v-if="!['delivered','cancelled'].includes(order.status)" @click="cancelOrder(order.id)" class="btn-icon danger" title="Annuler">❌</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="empty-state" v-else>
        <p>📦</p>
        <p>Aucune commande trouvée.</p>
      </div>

      <div class="pagination" v-if="store.meta.last_page > 1">
        <button v-for="p in store.meta.last_page" :key="p" :class="{ active: p === store.meta.current_page }" @click="store.setFilter('page', p)">{{ p }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAdminOrderStore } from '@/admin/stores/orders'

const store = useAdminOrderStore()
const searchQuery  = ref('')
const statusFilter = ref('')

let searchTimeout = null
function debouncedSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => store.setFilter('search', searchQuery.value), 400)
}

async function confirmOrder(id) {
  if (!confirm('Confirmer cette commande ? Les moutons seront réservés.')) return
  await store.confirm(id)
}
async function deliverOrder(id) {
  if (!confirm('Marquer comme livré ? Les moutons seront marqués vendus.')) return
  await store.deliver(id)
}
async function cancelOrder(id) {
  if (!confirm('Annuler cette commande ?')) return
  await store.cancel(id)
}

function fmt(n) { return new Intl.NumberFormat('fr-FR').format(n) }

onMounted(() => store.fetchOrders())
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.header-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.stat-pill { padding: 5px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
.stat-pill.orange { background: #fff3cd; color: #856404; }
.stat-pill.blue   { background: #d4edda; color: #155724; }
.stat-pill.green  { background: #d1ecf1; color: #0c5460; }

.filters-bar { display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.search-input { flex: 1; min-width: 200px; padding: 10px 14px; border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 0.88rem; font-family: inherit; outline: none; }
.search-input:focus { border-color: #4361ee; }
.filter-select { padding: 10px 14px; border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 0.88rem; font-family: inherit; background: white; cursor: pointer; outline: none; }

.card { background: white; border-radius: 12px; border: 1px solid #e9ecef; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.data-table th { text-align: left; padding: 12px 14px; color: #6c757d; font-weight: 600; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
.data-table td { padding: 12px 14px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.data-table tr:hover td { background: #f8f9fa; }
.mono { font-family: monospace; font-size: 0.78rem; }
.td-name   { font-weight: 600; color: #1a1a2e; }
.td-sub    { font-size: 0.72rem; color: #6c757d; margin-top: 1px; }
.td-amount { font-weight: 600; white-space: nowrap; }
.td-center { text-align: center; font-weight: 600; }

.badge { padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
.pay-unpaid  { background: #f8d7da; color: #721c24; }
.pay-partial { background: #fff3cd; color: #856404; }
.pay-paid    { background: #d4edda; color: #155724; }

.status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; white-space: nowrap; }
.status-pending   { background: #fff3cd; color: #856404; }
.status-confirmed { background: #d4edda; color: #155724; }
.status-paid      { background: #d1ecf1; color: #0c5460; }
.status-delivered { background: #d4edda; color: #155724; }
.status-cancelled { background: #e2e3e5; color: #383d41; }

.delivery-tag { font-size: 1rem; }

.action-btns { display: flex; gap: 4px; }
.btn-icon { width: 30px; height: 30px; border-radius: 6px; border: 1px solid #dee2e6; background: white; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; cursor: pointer; text-decoration: none; transition: all 0.2s; }
.btn-icon:hover { background: #f8f9fa; }
.btn-icon.green:hover { background: #d4edda; border-color: #c3e6cb; }
.btn-icon.blue:hover  { background: #d1ecf1; border-color: #bee5eb; }
.btn-icon.danger:hover { background: #fff3f3; border-color: #f8d7da; }

.empty-state { text-align: center; padding: 60px 20px; }
.empty-state p:first-child { font-size: 2.5rem; margin-bottom: 8px; }
.empty-state p { color: #6c757d; }

.pagination { display: flex; justify-content: center; gap: 6px; padding: 16px; }
.pagination button { width: 34px; height: 34px; border-radius: 6px; border: 1px solid #dee2e6; background: white; cursor: pointer; font-size: 0.82rem; transition: all 0.2s; }
.pagination button.active, .pagination button:hover { background: #4361ee; color: white; border-color: #4361ee; }

.loading { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 40px; color: #6c757d; }
.spinner { width: 24px; height: 24px; border: 2px solid #dee2e6; border-top-color: #4361ee; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
