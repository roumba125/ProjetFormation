<template>
  <div class="dashboard">

    <!-- Stat cards -->
    <div class="stats-grid">
      <div class="stat-card" v-for="stat in stats" :key="stat.label" :class="stat.color">
        <div class="stat-icon">{{ stat.icon }}</div>
        <div class="stat-body">
          <p class="stat-value">{{ stat.value }}</p>
          <p class="stat-label">{{ stat.label }}</p>
        </div>
        <div class="stat-trend" v-if="stat.trend">{{ stat.trend }}</div>
      </div>
    </div>

    <div class="dashboard-grid">

      <!-- Dernières commandes -->
      <div class="card">
        <div class="card-header">
          <h3>Dernières commandes</h3>
          <RouterLink :to="{ name: 'admin.orders.index' }" class="btn-link">Tout voir →</RouterLink>
        </div>
        <div class="card-body">
          <div v-if="loadingDash" class="loading">Chargement...</div>
          <table class="data-table" v-else-if="dashData?.recent_orders?.length">
            <thead>
              <tr>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Total</th>
                <th>Statut</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in dashData.recent_orders" :key="order.order_number">
                <td class="mono">{{ order.order_number }}</td>
                <td>
                  <p class="td-name">{{ order.client_name }}</p>
                  <p class="td-sub">{{ order.client_phone }}</p>
                </td>
                <td class="td-amount">{{ fmt(order.total) }} FCFA</td>
                <td><span class="badge" :class="`badge-${order.status}`">{{ order.status }}</span></td>
                <td class="td-date">{{ order.date }}</td>
              </tr>
            </tbody>
          </table>
          <p class="empty" v-else>Aucune commande pour le moment.</p>
        </div>
      </div>

      <!-- Vaccins à rappeler -->
      <div class="card">
        <div class="card-header">
          <h3>⚠️ Vaccins à rappeler</h3>
          <span class="badge badge-warning">30 prochains jours</span>
        </div>
        <div class="card-body">
          <div v-if="loadingDash" class="loading">Chargement...</div>
          <div v-else-if="dashData?.upcoming_vaccinations?.length" class="vac-list">
            <div v-for="v in dashData.upcoming_vaccinations" :key="v.sheep_ref + v.vaccine" class="vac-item">
              <div class="vac-icon">💉</div>
              <div class="vac-info">
                <p class="vac-sheep">{{ v.sheep_name }} <span>{{ v.sheep_ref }}</span></p>
                <p class="vac-name">{{ v.vaccine }}</p>
              </div>
              <div class="vac-due">
                <p class="due-date">{{ v.due_date }}</p>
                <p class="due-days" :class="v.days_left <= 7 ? 'urgent' : ''">
                  Dans {{ v.days_left }}j
                </p>
              </div>
            </div>
          </div>
          <p class="empty" v-else>✅ Aucun rappel dans les 30 prochains jours.</p>
        </div>
      </div>

      <!-- Répartition moutons -->
      <div class="card">
        <div class="card-header">
          <h3>🐑 Répartition du stock</h3>
        </div>
        <div class="card-body">
          <div class="stock-bars">
            <div class="stock-bar-item" v-for="item in stockItems" :key="item.label">
              <div class="bar-header">
                <span class="bar-label">{{ item.label }}</span>
                <span class="bar-value">{{ item.value }}</span>
              </div>
              <div class="bar-track">
                <div class="bar-fill" :class="item.color" :style="{ width: item.pct + '%' }"></div>
              </div>
            </div>
          </div>
          <div class="stock-total">
            Total : <strong>{{ dashData?.sheep?.total ?? 0 }} moutons</strong>
          </div>
        </div>
      </div>

      <!-- Revenus -->
      <div class="card">
        <div class="card-header">
          <h3>💰 Revenus</h3>
        </div>
        <div class="card-body">
          <div class="revenue-stats">
            <div class="rev-item">
              <p class="rev-label">Revenus totaux</p>
              <p class="rev-value">{{ fmt(dashData?.orders?.total_revenue ?? 0) }} FCFA</p>
            </div>
            <div class="rev-item">
              <p class="rev-label">En attente de paiement</p>
              <p class="rev-value pending">{{ fmt(dashData?.orders?.pending_revenue ?? 0) }} FCFA</p>
            </div>
            <div class="rev-item">
              <p class="rev-label">Commandes livrées</p>
              <p class="rev-value">{{ dashData?.orders?.delivered ?? 0 }}</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '@/admin/stores/auth'

const loadingDash = ref(true)
const dashData    = ref(null)

const stats = computed(() => [
  {
    icon: '🐑', label: 'Disponibles', color: 'green',
    value: dashData.value?.sheep?.available ?? 0,
    trend: `${dashData.value?.sheep?.total ?? 0} total`,
  },
  {
    icon: '⏳', label: 'Réservés', color: 'orange',
    value: dashData.value?.sheep?.reserved ?? 0,
  },
  {
    icon: '📦', label: 'Commandes en attente', color: 'blue',
    value: dashData.value?.orders?.pending ?? 0,
  },
  {
    icon: '💰', label: 'Moutons vendus', color: 'purple',
    value: dashData.value?.sheep?.sold ?? 0,
  },
])

const stockItems = computed(() => {
  const total = dashData.value?.sheep?.total || 1
  return [
    { label: 'Disponibles', value: dashData.value?.sheep?.available ?? 0, color: 'green', pct: ((dashData.value?.sheep?.available ?? 0) / total) * 100 },
    { label: 'Réservés',    value: dashData.value?.sheep?.reserved ?? 0,  color: 'orange', pct: ((dashData.value?.sheep?.reserved ?? 0) / total) * 100 },
    { label: 'Vendus',      value: dashData.value?.sheep?.sold ?? 0,       color: 'gray',  pct: ((dashData.value?.sheep?.sold ?? 0) / total) * 100 },
  ]
})

function fmt(n) {
  return new Intl.NumberFormat('fr-FR').format(n)
}

onMounted(async () => {
  try {
    const { data } = await api.get('/api/v1/admin/dashboard')
    dashData.value = data
  } catch (e) {
    console.error('Dashboard error:', e)
  } finally {
    loadingDash.value = false
  }
})
</script>

<style scoped>
.dashboard {}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 28px;
}
@media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 8px rgba(0,0,0,0.04);
}
.stat-icon { font-size: 2rem; }
.stat-value { font-size: 1.8rem; font-weight: 700; color: #1a1a2e; line-height: 1; }
.stat-label { font-size: 0.78rem; color: #6c757d; margin-top: 4px; }
.stat-trend { margin-left: auto; font-size: 0.72rem; color: #6c757d; white-space: nowrap; }

.stat-card.green  { border-left: 4px solid #2ecc71; }
.stat-card.orange { border-left: 4px solid #f39c12; }
.stat-card.blue   { border-left: 4px solid #4361ee; }
.stat-card.purple { border-left: 4px solid #9b59b6; }

/* Dashboard grid */
.dashboard-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}
@media (max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }

.card {
  background: white;
  border-radius: 12px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 8px rgba(0,0,0,0.04);
  overflow: hidden;
}
.card:first-child { grid-column: 1 / -1; }

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px;
  border-bottom: 1px solid #e9ecef;
}
.card-header h3 { font-size: 0.95rem; font-weight: 600; color: #1a1a2e; }
.btn-link { font-size: 0.8rem; color: #4361ee; text-decoration: none; font-weight: 500; }
.card-body { padding: 20px; }

/* Table */
.data-table { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.data-table th { text-align: left; padding: 8px 12px; color: #6c757d; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e9ecef; }
.data-table td { padding: 12px 12px; border-bottom: 1px solid #f8f9fa; color: #1a1a2e; vertical-align: middle; }
.data-table tr:last-child td { border-bottom: none; }
.td-name  { font-weight: 500; }
.td-sub   { font-size: 0.72rem; color: #6c757d; }
.td-amount { font-weight: 600; }
.td-date  { color: #6c757d; font-size: 0.78rem; white-space: nowrap; }
.mono { font-family: monospace; font-size: 0.8rem; }

/* Badges */
.badge { padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
.badge-En\ attente { background: #fff3cd; color: #856404; }
.badge-Confirmée   { background: #d4edda; color: #155724; }
.badge-Livrée      { background: #d1ecf1; color: #0c5460; }
.badge-Annulée     { background: #f8d7da; color: #721c24; }
.badge-warning     { background: #fff3cd; color: #856404; }

/* Vaccinations */
.vac-list { display: flex; flex-direction: column; gap: 10px; }
.vac-item { display: flex; align-items: center; gap: 12px; padding: 10px; background: #fff8e1; border-radius: 8px; border-left: 3px solid #f39c12; }
.vac-icon { font-size: 1.2rem; }
.vac-info { flex: 1; }
.vac-sheep { font-weight: 600; font-size: 0.83rem; }
.vac-sheep span { color: #6c757d; font-size: 0.72rem; margin-left: 6px; }
.vac-name { font-size: 0.75rem; color: #6c757d; }
.vac-due { text-align: right; }
.due-date { font-size: 0.78rem; font-weight: 600; }
.due-days { font-size: 0.7rem; color: #f39c12; }
.due-days.urgent { color: #e63946; font-weight: 700; }

/* Stock bars */
.stock-bars { display: flex; flex-direction: column; gap: 14px; margin-bottom: 16px; }
.bar-header { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.82rem; }
.bar-label { color: #495057; font-weight: 500; }
.bar-value { font-weight: 600; color: #1a1a2e; }
.bar-track { height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
.bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
.bar-fill.green  { background: #2ecc71; }
.bar-fill.orange { background: #f39c12; }
.bar-fill.gray   { background: #adb5bd; }
.stock-total { font-size: 0.82rem; color: #6c757d; text-align: right; }

/* Revenue */
.revenue-stats { display: flex; flex-direction: column; gap: 16px; }
.rev-item { padding-bottom: 16px; border-bottom: 1px solid #f8f9fa; }
.rev-item:last-child { border-bottom: none; padding-bottom: 0; }
.rev-label { font-size: 0.78rem; color: #6c757d; margin-bottom: 4px; }
.rev-value { font-size: 1.3rem; font-weight: 700; color: #1a1a2e; }
.rev-value.pending { color: #f39c12; }

.loading { color: #6c757d; font-size: 0.85rem; padding: 20px 0; text-align: center; }
.empty   { color: #6c757d; font-size: 0.85rem; padding: 20px 0; text-align: center; }
</style>
