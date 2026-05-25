<template>
  <div class="order-detail">
    <div class="page-header">
      <RouterLink :to="{ name: 'admin.orders.index' }" class="btn-back">← Retour</RouterLink>
      <div class="header-actions" v-if="order">
        <button v-if="order.status === 'pending'" @click="confirmOrder" class="btn-confirm" :disabled="acting">✅ Confirmer</button>
        <button v-if="order.status === 'confirmed'" @click="deliverOrder" class="btn-deliver" :disabled="acting">🚚 Marquer livré</button>
        <button v-if="!['delivered','cancelled'].includes(order.status)" @click="cancelOrder" class="btn-cancel-order" :disabled="acting">❌ Annuler</button>
      </div>
    </div>

    <div v-if="loading" class="loading"><div class="spinner"></div> Chargement...</div>

    <div v-else-if="order" class="detail-grid">

      <!-- Statut -->
      <div class="status-banner" :class="`status-${order.status}`">
        <div class="status-left">
          <p class="order-num">{{ order.order_number }}</p>
          <span class="status-badge" :class="`badge-${order.status}`">{{ order.status_label ?? order.status }}</span>
        </div>
        <div class="status-right">
          <p class="status-date">Commande du {{ formatDate(order.created_at) }}</p>
          <p class="pay-status" :class="`pay-${order.payment_status}`">{{ order.payment_status_label ?? order.payment_status }}</p>
        </div>
      </div>

      <!-- Client -->
      <div class="form-card">
        <h3 class="card-title">👤 Client</h3>
        <div class="info-grid">
          <div class="info-item"><span class="info-label">Nom</span><span>{{ order.client_name }}</span></div>
          <div class="info-item"><span class="info-label">Téléphone</span><span>{{ order.client_phone }}</span></div>
          <div class="info-item"><span class="info-label">Email</span><span>{{ order.client_email }}</span></div>
          <div class="info-item"><span class="info-label">Ville</span><span>{{ order.client_city ?? '—' }}</span></div>
        </div>
        <div class="info-note" v-if="order.client_notes">
          <span class="info-label">Note client</span>
          <p>{{ order.client_notes }}</p>
        </div>
      </div>

      <!-- Livraison -->
      <div class="form-card">
        <h3 class="card-title">🚚 Livraison</h3>
        <div class="info-grid">
          <div class="info-item"><span class="info-label">Mode</span><span>{{ order.delivery_method === 'delivery' ? '🚚 Livraison à domicile' : '🏠 Retrait sur place' }}</span></div>
          <div class="info-item"><span class="info-label">Paiement</span><span>{{ order.payment_method_label ?? order.payment_method }}</span></div>
          <div class="info-item" v-if="order.delivery_city"><span class="info-label">Ville livraison</span><span>{{ order.delivery_city }}</span></div>
          <div class="info-item" v-if="order.desired_delivery_date"><span class="info-label">Date souhaitée</span><span>{{ formatDate(order.desired_delivery_date) }}</span></div>
        </div>
        <div class="info-note" v-if="order.delivery_address">
          <span class="info-label">Adresse</span>
          <p>{{ order.delivery_address }}</p>
        </div>
      </div>

      <!-- Moutons commandés -->
      <div class="form-card full">
        <h3 class="card-title">🐑 Moutons commandés</h3>
        <table class="mini-table">
          <thead><tr><th>Photo</th><th>Mouton</th><th>Race</th><th>Poids</th><th>Prix</th></tr></thead>
          <tbody>
            <tr v-for="item in order.items" :key="item.id">
              <td>
                <div class="sheep-photo">
                  <img v-if="item.sheep?.primary_photo_url" :src="item.sheep.primary_photo_url" :alt="item.sheep_name" />
                  <span v-else>🐑</span>
                </div>
              </td>
              <td>
                <p class="td-name">{{ item.sheep_name }}</p>
                <p class="td-sub">{{ item.sheep_reference }}</p>
              </td>
              <td>{{ item.breed_name }}</td>
              <td>{{ item.weight_at_order }} kg</td>
              <td class="td-amount">{{ fmt(item.unit_price) }} FCFA</td>
            </tr>
          </tbody>
        </table>

        <!-- Résumé financier -->
        <div class="financial-summary">
          <div class="fin-row"><span>Sous-total</span><span>{{ fmt(order.subtotal) }} FCFA</span></div>
          <div class="fin-row" v-if="order.delivery_fee > 0"><span>Frais de livraison</span><span>{{ fmt(order.delivery_fee) }} FCFA</span></div>
          <div class="fin-row total"><span>Total</span><span>{{ fmt(order.total_amount) }} FCFA</span></div>
          <div class="fin-row" v-if="order.deposit_amount > 0"><span>Acompte versé</span><span class="paid">{{ fmt(order.deposit_amount) }} FCFA</span></div>
          <div class="fin-row" v-if="order.remaining_amount > 0"><span>Reste à payer</span><span class="remaining">{{ fmt(order.remaining_amount) }} FCFA</span></div>
        </div>
      </div>

      <!-- Note admin -->
      <div class="form-card">
        <h3 class="card-title">📝 Note interne</h3>
        <textarea v-model="adminNote" rows="3" placeholder="Note visible seulement dans l'admin..." class="note-textarea"></textarea>
        <button @click="saveNote" class="btn-save-note" :disabled="savingNote">
          {{ savingNote ? 'Enregistrement...' : '💾 Sauvegarder' }}
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAdminOrderStore } from '@/admin/stores/orders'
import { api } from '@/admin/stores/auth'

const route = useRoute()
const store = useAdminOrderStore()

const order      = ref(null)
const loading    = ref(true)
const acting     = ref(false)
const adminNote  = ref('')
const savingNote = ref(false)

async function load() {
  loading.value = true
  try {
    order.value = await store.fetchOne(route.params.id)
    adminNote.value = order.value.admin_notes ?? ''
  } finally {
    loading.value = false
  }
}

async function confirmOrder() {
  acting.value = true
  try { await store.confirm(order.value.id); await load() }
  finally { acting.value = false }
}

async function deliverOrder() {
  acting.value = true
  try { await store.deliver(order.value.id); await load() }
  finally { acting.value = false }
}

async function cancelOrder() {
  if (!confirm('Annuler cette commande ?')) return
  acting.value = true
  try { await store.cancel(order.value.id); await load() }
  finally { acting.value = false }
}

async function saveNote() {
  savingNote.value = true
  try {
    await api.patch(`/api/v1/admin/orders/${order.value.id}/note`, { admin_notes: adminNote.value })
  } finally {
    savingNote.value = false
  }
}

function fmt(n) { return new Intl.NumberFormat('fr-FR').format(n ?? 0) }
function formatDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('fr-FR') }

onMounted(load)
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.btn-back { color: #4361ee; text-decoration: none; font-size: 0.85rem; font-weight: 500; }
.header-actions { display: flex; gap: 10px; }
.btn-confirm { background: #2ecc71; color: white; border: none; padding: 9px 18px; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.85rem; font-weight: 600; }
.btn-deliver { background: #4361ee; color: white; border: none; padding: 9px 18px; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.85rem; font-weight: 600; }
.btn-cancel-order { background: #fff3f3; color: #e63946; border: 1px solid #f8d7da; padding: 9px 18px; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.85rem; font-weight: 600; }

.loading { display: flex; align-items: center; gap: 10px; padding: 40px; color: #6c757d; justify-content: center; }
.spinner { width: 24px; height: 24px; border: 2px solid #dee2e6; border-top-color: #4361ee; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } }

.status-banner { grid-column: 1 / -1; background: white; border-radius: 12px; padding: 20px 24px; border: 1px solid #e9ecef; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.order-num { font-family: monospace; font-size: 1.1rem; font-weight: 700; color: #1a1a2e; margin-bottom: 6px; }
.status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
.badge-pending   { background: #fff3cd; color: #856404; }
.badge-confirmed { background: #d4edda; color: #155724; }
.badge-delivered { background: #d1ecf1; color: #0c5460; }
.badge-cancelled { background: #e2e3e5; color: #383d41; }
.status-date { font-size: 0.82rem; color: #6c757d; margin-bottom: 4px; }
.pay-status { font-size: 0.8rem; font-weight: 600; }
.pay-unpaid  { color: #e63946; }
.pay-partial { color: #f39c12; }
.pay-paid    { color: #2ecc71; }

.form-card { background: white; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef; }
.form-card.full { grid-column: 1 / -1; }
.card-title { font-size: 0.95rem; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.info-item { background: #f8f9fa; padding: 10px 12px; border-radius: 8px; }
.info-label { display: block; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; margin-bottom: 2px; }
.info-note { margin-top: 12px; background: #f8f9fa; padding: 10px 12px; border-radius: 8px; }
.info-note p { font-size: 0.83rem; color: #495057; margin-top: 4px; }

.sheep-photo { width: 44px; height: 44px; border-radius: 8px; overflow: hidden; background: #f5ede0; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.sheep-photo img { width: 100%; height: 100%; object-fit: cover; }
.mini-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; margin-bottom: 16px; }
.mini-table th { text-align: left; padding: 8px 12px; color: #6c757d; font-size: 0.7rem; text-transform: uppercase; border-bottom: 1px solid #e9ecef; background: #f8f9fa; }
.mini-table td { padding: 10px 12px; border-bottom: 1px solid #f8f9fa; vertical-align: middle; }
.td-name { font-weight: 600; }
.td-sub  { font-size: 0.72rem; color: #6c757d; font-family: monospace; }
.td-amount { font-weight: 600; }

.financial-summary { background: #f8f9fa; border-radius: 10px; padding: 16px; }
.fin-row { display: flex; justify-content: space-between; font-size: 0.85rem; padding: 6px 0; border-bottom: 1px solid #e9ecef; }
.fin-row:last-child { border-bottom: none; }
.fin-row.total { font-weight: 700; font-size: 0.95rem; border-top: 2px solid #dee2e6; padding-top: 10px; margin-top: 4px; }
.paid      { color: #2ecc71; font-weight: 600; }
.remaining { color: #e63946; font-weight: 600; }

.note-textarea { width: 100%; padding: 10px 12px; border: 1.5px solid #dee2e6; border-radius: 8px; font-size: 0.85rem; font-family: inherit; outline: none; resize: vertical; box-sizing: border-box; }
.note-textarea:focus { border-color: #4361ee; }
.btn-save-note { margin-top: 10px; background: #4361ee; color: white; border: none; padding: 9px 18px; border-radius: 8px; cursor: pointer; font-family: inherit; font-size: 0.85rem; font-weight: 600; }
.btn-save-note:disabled { background: #adb5bd; cursor: not-allowed; }
</style>
