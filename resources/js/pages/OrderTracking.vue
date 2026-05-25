<template>
  <div class="tracking-page">
    <div class="tracking-container">
      <h1>📦 Suivre ma commande</h1>
      <p class="subtitle">Entrez votre numéro de commande et votre email.</p>

      <form @submit.prevent="track" class="tracking-form">
        <div class="form-group">
          <label>Numéro de commande</label>
          <input v-model="form.orderNumber" type="text" placeholder="CMD-2024-00001" required />
        </div>
        <div class="form-group">
          <label>Email</label>
          <input v-model="form.email" type="email" placeholder="votre@email.com" required />
        </div>
        <button type="submit" :disabled="loading" class="btn-track">
          {{ loading ? 'Recherche…' : '🔍 Rechercher' }}
        </button>
        <p class="error-msg" v-if="error">{{ error }}</p>
      </form>

      <!-- Résultat -->
      <div class="tracking-result" v-if="order">
        <div class="status-banner" :class="`status-${order.status}`">
          <span class="status-icon">{{ statusIcon }}</span>
          <div>
            <p class="status-title">{{ order.status_label }}</p>
            <p class="status-date">Commande du {{ order.created_at }}</p>
          </div>
        </div>

        <!-- Timeline -->
        <div class="timeline">
          <div
            v-for="step in steps"
            :key="step.key"
            class="timeline-step"
            :class="{ done: stepDone(step.key), current: stepCurrent(step.key) }"
          >
            <div class="step-dot"></div>
            <div class="step-content">
              <p class="step-title">{{ step.label }}</p>
              <p class="step-sub">{{ step.desc }}</p>
            </div>
          </div>
        </div>

        <!-- Moutons -->
        <h3 class="section-h">🐑 Moutons commandés</h3>
        <div class="items-list">
          <div v-for="item in order.items" :key="item.sheep_reference" class="order-item">
            <div class="item-photo">
              <img v-if="item.photo" :src="item.photo" :alt="item.sheep_name" />
              <span v-else>🐑</span>
            </div>
            <div class="item-info">
              <p class="item-name">{{ item.sheep_name }}</p>
              <p class="item-meta">{{ item.breed }} · {{ item.weight }}</p>
            </div>
            <div class="item-price">{{ item.price }}</div>
          </div>
        </div>

        <!-- Résumé -->
        <div class="financial-summary">
          <div class="fin-row">
            <span>Total</span>
            <span class="fin-amount">{{ fmt(order.total_amount) }} FCFA</span>
          </div>
          <div class="fin-row" v-if="order.deposit_paid > 0">
            <span>Acompte versé</span>
            <span>{{ fmt(order.deposit_paid) }} FCFA</span>
          </div>
          <div class="fin-row" v-if="order.remaining > 0">
            <span>Reste à payer</span>
            <span class="fin-amount">{{ fmt(order.remaining) }} FCFA</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from '@/lib/axios'

const route   = useRoute()
const loading = ref(false)
const error   = ref(null)
const order   = ref(null)

const form = ref({
  orderNumber: route.query.ref   || '',
  email:       route.query.email || '',
})

const steps = [
  { key: 'pending',   label: 'Commande reçue',  desc: 'Votre commande a été enregistrée.' },
  { key: 'confirmed', label: 'Confirmée',         desc: 'L\'éleveur a confirmé votre commande.' },
  { key: 'paid',      label: 'Paiement reçu',     desc: 'Le paiement a été enregistré.' },
  { key: 'ready',     label: 'Prête',             desc: 'Votre mouton est prêt.' },
  { key: 'delivered', label: 'Livrée',            desc: 'Commande livrée avec succès.' },
]

const statusOrder = ['pending', 'confirmed', 'paid', 'ready', 'delivered']

function stepDone(key) {
  if (!order.value) return false
  return statusOrder.indexOf(key) < statusOrder.indexOf(order.value.status)
}
function stepCurrent(key) {
  return order.value?.status === key
}

const statusIcon = computed(() => {
  const icons = { pending: '⏳', confirmed: '✅', paid: '💳', ready: '📦', delivered: '🏠', cancelled: '❌' }
  return icons[order.value?.status] ?? '📋'
})

async function track() {
  loading.value = true
  error.value   = null
  order.value   = null
  try {
    const { data } = await axios.get(`/api/v1/orders/track/${form.value.orderNumber}`, {
      params: { email: form.value.email },
    })
    order.value = data.data
  } catch (err) {
    error.value = err.response?.status === 404
      ? 'Aucune commande trouvée. Vérifiez le numéro et l\'email.'
      : 'Erreur lors de la recherche. Réessayez.'
  } finally {
    loading.value = false
  }
}

function fmt(a) {
  return new Intl.NumberFormat('fr-FR').format(a)
}

onMounted(() => {
  if (form.value.orderNumber && form.value.email) track()
})
</script>

<style scoped>
.tracking-page { min-height: 100vh; padding: 100px 20px 60px; background: #fdf8f2; }
.tracking-container { max-width: 600px; margin: 0 auto; }
h1 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #1a120b; margin-bottom: 6px; }
.subtitle { color: #8b6b55; font-size: 0.85rem; margin-bottom: 32px; }

.tracking-form { background: white; border-radius: 16px; padding: 28px; border: 1px solid rgba(139,94,60,0.1); margin-bottom: 32px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 0.78rem; font-weight: 600; color: #5a3a20; margin-bottom: 6px; }
.form-group input { width: 100%; padding: 10px 14px; border: 1.5px solid rgba(139,94,60,0.2); border-radius: 8px; font-size: 0.85rem; font-family: inherit; background: #fdf8f2; outline: none; box-sizing: border-box; }
.form-group input:focus { border-color: #8b5e3c; }
.btn-track { width: 100%; padding: 13px; background: #8b5e3c; color: white; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: inherit; }
.btn-track:disabled { background: #c0a090; cursor: not-allowed; }
.error-msg { color: #e53935; font-size: 0.8rem; margin-top: 10px; text-align: center; }

.tracking-result { background: white; border-radius: 16px; padding: 24px; border: 1px solid rgba(139,94,60,0.1); }
.status-banner { display: flex; align-items: center; gap: 16px; padding: 18px; border-radius: 12px; margin-bottom: 28px; background: #d4edda; }
.status-banner.status-pending  { background: #fff3cd; }
.status-banner.status-cancelled { background: #f8d7da; }
.status-icon { font-size: 2rem; }
.status-title { font-weight: 700; font-size: 1rem; color: #1a120b; }
.status-date  { font-size: 0.75rem; color: #6b4c35; margin-top: 2px; }

.timeline { display: flex; flex-direction: column; padding-left: 12px; margin-bottom: 28px; }
.timeline-step { display: flex; gap: 16px; position: relative; padding-bottom: 20px; }
.timeline-step:not(:last-child)::before { content: ''; position: absolute; left: 7px; top: 18px; bottom: 0; width: 2px; background: #e8d5b0; }
.timeline-step.done::before { background: #4a6741; }
.step-dot { width: 16px; height: 16px; border-radius: 50%; background: #e8d5b0; flex-shrink: 0; margin-top: 2px; }
.timeline-step.done .step-dot    { background: #4a6741; }
.timeline-step.current .step-dot { background: #8b5e3c; box-shadow: 0 0 0 4px rgba(139,94,60,0.2); }
.step-title { font-weight: 600; font-size: 0.85rem; color: #1a120b; }
.step-sub   { font-size: 0.75rem; color: #8b6b55; margin-top: 2px; }

.section-h { font-family: 'Playfair Display', serif; font-size: 0.95rem; font-weight: 700; margin: 20px 0 12px; }
.items-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.order-item { display: flex; align-items: center; gap: 12px; background: #f5ede0; border-radius: 10px; padding: 12px; }
.item-photo { width: 48px; height: 48px; border-radius: 8px; background: #e8d5b0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; overflow: hidden; }
.item-photo img { width: 100%; height: 100%; object-fit: cover; }
.item-name  { font-weight: 600; font-size: 0.85rem; color: #1a120b; }
.item-meta  { font-size: 0.72rem; color: #8b6b55; margin-top: 2px; }
.item-price { font-weight: 700; color: #8b5e3c; font-size: 0.9rem; margin-left: auto; white-space: nowrap; }

.financial-summary { background: #f5ede0; border-radius: 10px; padding: 16px; }
.fin-row { display: flex; justify-content: space-between; font-size: 0.85rem; padding: 5px 0; border-bottom: 1px solid rgba(139,94,60,0.1); }
.fin-amount { font-weight: 700; }
</style>
