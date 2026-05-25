<!-- =============================================================
  resources/js/pages/Checkout.vue — Page panier + commande
============================================================= -->
<template>
  <div class="checkout-page">
    <div class="checkout-header">
      <button class="btn-back" @click="$router.back()">← Retour</button>
      <h1>Finaliser ma commande</h1>
    </div>

    <!-- Panier vide -->
    <div class="empty-cart" v-if="cart.isEmpty">
      <p>🛒</p>
      <h2>Votre panier est vide</h2>
      <p>Ajoutez des moutons depuis le catalogue pour passer commande.</p>
      <RouterLink to="/catalogue" class="btn-primary">Voir le catalogue</RouterLink>
    </div>

    <!-- Formulaire de commande -->
    <div v-else>
      <OrderForm @success="onSuccess" />
    </div>

    <!-- Confirmation -->
    <div class="success-overlay" v-if="orderSuccess">
      <div class="success-card">
        <div class="success-icon">✅</div>
        <h2>Commande confirmée !</h2>
        <p class="order-num">{{ orderData.order_number }}</p>
        <p>Merci <strong>{{ orderData.client_name }}</strong> ! Nous vous contacterons sous 24h.</p>
        <div class="success-details">
          <div class="detail-row">
            <span>Total</span>
            <span>{{ formatAmount(orderData.total_amount) }} FCFA</span>
          </div>
          <div class="detail-row" v-if="orderData.deposit_amount > 0">
            <span>Acompte</span>
            <span>{{ formatAmount(orderData.deposit_amount) }} FCFA</span>
          </div>
          <div class="detail-row" v-if="orderData.deposit_amount > 0">
            <span>Reste à payer</span>
            <span>{{ formatAmount(orderData.remaining) }} FCFA</span>
          </div>
        </div>
        <p class="success-note">Un email de confirmation a été envoyé à votre adresse.</p>
        <div class="success-actions">
          <RouterLink to="/catalogue" class="btn-secondary">Continuer mes achats</RouterLink>
          <RouterLink :to="`/suivi?ref=${orderData.order_number}`" class="btn-primary">Suivre ma commande</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/sheep'
import OrderForm from '@/components/OrderForm.vue'

const cart         = useCartStore()
const orderSuccess = ref(false)
const orderData    = ref(null)

function onSuccess(data) {
  orderData.value    = data
  orderSuccess.value = true
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function formatAmount(a) {
  return new Intl.NumberFormat('fr-FR').format(a)
}
</script>

<style scoped>
.checkout-page { max-width: 1060px; margin: 0 auto; padding: 100px 20px 60px; min-height: 100vh; }
.checkout-header { display: flex; align-items: center; gap: 20px; margin-bottom: 36px; }
.checkout-header h1 { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: #1a120b; }
.btn-back { background: none; border: none; font-size: 0.9rem; color: #8b5e3c; cursor: pointer; font-family: 'DM Sans', sans-serif; font-weight: 500; }

.empty-cart { text-align: center; padding: 80px 20px; }
.empty-cart p:first-child { font-size: 3.5rem; margin-bottom: 14px; }
.empty-cart h2 { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 8px; }
.btn-primary { display: inline-block; background: #8b5e3c; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600; margin-top: 16px; }

/* Succès */
.success-overlay {
  position: fixed; inset: 0;
  background: rgba(26,18,11,0.7);
  backdrop-filter: blur(6px);
  z-index: 600;
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.success-card {
  background: white; border-radius: 20px;
  padding: 40px; max-width: 480px; width: 100%;
  text-align: center;
}
.success-icon { font-size: 3rem; margin-bottom: 16px; }
.success-card h2 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #1a120b; margin-bottom: 8px; }
.order-num { font-size: 1rem; font-weight: 700; color: #8b5e3c; margin-bottom: 10px; letter-spacing: 1px; }

.success-details { background: #f5ede0; border-radius: 10px; padding: 16px; margin: 20px 0; text-align: left; }
.detail-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #2d1f14; padding: 5px 0; }
.detail-row:not(:last-child) { border-bottom: 1px solid rgba(139,94,60,0.1); }

.success-note { font-size: 0.75rem; color: #8b6b55; margin-bottom: 24px; }
.success-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn-secondary { display: inline-block; background: white; color: #8b5e3c; border: 2px solid #8b5e3c; padding: 11px 20px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
</style>

---

<!-- =============================================================
  resources/js/pages/OrderTracking.vue — Suivi de commande
============================================================= -->
<template>
  <div class="tracking-page">
    <div class="tracking-container">
      <h1>📦 Suivre ma commande</h1>
      <p class="subtitle">Entrez votre numéro de commande et votre email pour consulter l'état.</p>

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
        <!-- Statut -->
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
            :class="{
              done:    stepDone(step.key),
              current: stepCurrent(step.key),
            }"
          >
            <div class="step-dot"></div>
            <div class="step-content">
              <p class="step-title">{{ step.label }}</p>
              <p class="step-sub">{{ step.desc }}</p>
            </div>
          </div>
        </div>

        <!-- Moutons commandés -->
        <h3 class="section-h">🐑 Moutons commandés</h3>
        <div class="items-list">
          <div v-for="item in order.items" :key="item.sheep_reference" class="order-item">
            <div class="item-photo">
              <img v-if="item.photo" :src="item.photo" :alt="item.sheep_name" />
              <span v-else>🐑</span>
            </div>
            <div class="item-info">
              <p class="item-name">{{ item.sheep_name }}</p>
              <p class="item-meta">{{ item.breed }} · {{ item.weight }} · {{ item.sheep_reference }}</p>
            </div>
            <div class="item-price">{{ item.price }}</div>
          </div>
        </div>

        <!-- Résumé financier -->
        <div class="financial-summary">
          <div class="fin-row">
            <span>Total commande</span>
            <span class="fin-amount">{{ formatAmount(order.total_amount) }} FCFA</span>
          </div>
          <div class="fin-row" v-if="order.deposit_paid > 0">
            <span>Acompte versé</span>
            <span>{{ formatAmount(order.deposit_paid) }} FCFA</span>
          </div>
          <div class="fin-row remaining" v-if="order.remaining > 0">
            <span>Reste à payer</span>
            <span class="fin-amount">{{ formatAmount(order.remaining) }} FCFA</span>
          </div>
          <div class="fin-row paid" v-else>
            <span>Paiement</span>
            <span>✅ Entièrement payé</span>
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
  orderNumber: route.query.ref || '',
  email:       route.query.email || '',
})

const steps = [
  { key: 'pending',   label: 'Commande reçue',   desc: 'Votre commande a été enregistrée.' },
  { key: 'confirmed', label: 'Confirmée',          desc: 'L\'éleveur a confirmé votre commande.' },
  { key: 'paid',      label: 'Paiement reçu',      desc: 'Le paiement a été enregistré.' },
  { key: 'ready',     label: 'Prête',              desc: 'Votre mouton est prêt à être récupéré/livré.' },
  { key: 'delivered', label: 'Livrée',             desc: 'Commande livrée avec succès.' },
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

function formatAmount(a) {
  return new Intl.NumberFormat('fr-FR').format(a)
}

// Auto-tracker si les params sont dans l'URL
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
.form-group input { width: 100%; padding: 10px 14px; border: 1.5px solid rgba(139,94,60,0.2); border-radius: 8px; font-size: 0.85rem; font-family: 'DM Sans', sans-serif; background: #fdf8f2; outline: none; }
.form-group input:focus { border-color: #8b5e3c; }
.btn-track { width: 100%; padding: 13px; background: #8b5e3c; color: white; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: background 0.2s; }
.btn-track:hover:not(:disabled) { background: #1a120b; }
.btn-track:disabled { background: #c0a090; cursor: not-allowed; }
.error-msg { color: #e53935; font-size: 0.8rem; margin-top: 10px; text-align: center; }

/* Résultat */
.tracking-result { background: white; border-radius: 16px; padding: 24px; border: 1px solid rgba(139,94,60,0.1); }
.status-banner { display: flex; align-items: center; gap: 16px; padding: 18px; border-radius: 12px; margin-bottom: 28px; }
.status-banner.status-pending   { background: #fff3cd; }
.status-banner.status-confirmed { background: #d4edda; }
.status-banner.status-delivered { background: #d4edda; }
.status-banner.status-cancelled { background: #f8d7da; }
.status-icon  { font-size: 2rem; }
.status-title { font-weight: 700; font-size: 1rem; color: #1a120b; }
.status-date  { font-size: 0.75rem; color: #6b4c35; margin-top: 2px; }

/* Timeline */
.timeline { display: flex; flex-direction: column; gap: 0; margin-bottom: 28px; padding-left: 12px; }
.timeline-step { display: flex; gap: 16px; position: relative; padding-bottom: 20px; }
.timeline-step:not(:last-child)::before { content: ''; position: absolute; left: 7px; top: 18px; bottom: 0; width: 2px; background: #e8d5b0; }
.timeline-step.done::before { background: #4a6741; }
.step-dot { width: 16px; height: 16px; border-radius: 50%; background: #e8d5b0; flex-shrink: 0; margin-top: 2px; transition: background 0.2s; }
.timeline-step.done .step-dot    { background: #4a6741; }
.timeline-step.current .step-dot { background: #8b5e3c; box-shadow: 0 0 0 4px rgba(139,94,60,0.2); }
.step-title { font-weight: 600; font-size: 0.85rem; color: #1a120b; }
.step-sub   { font-size: 0.75rem; color: #8b6b55; margin-top: 2px; }

.section-h { font-family: 'Playfair Display', serif; font-size: 0.95rem; font-weight: 700; margin: 20px 0 12px; }
.items-list { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.order-item { display: flex; align-items: center; gap: 12px; background: #f5ede0; border-radius: 10px; padding: 12px; }
.item-photo { width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #e8d5b0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.item-photo img { width: 100%; height: 100%; object-fit: cover; }
.item-name  { font-weight: 600; font-size: 0.85rem; color: #1a120b; }
.item-meta  { font-size: 0.72rem; color: #8b6b55; margin-top: 2px; }
.item-price { font-family: 'Playfair Display', serif; font-weight: 700; color: #8b5e3c; font-size: 0.9rem; margin-left: auto; white-space: nowrap; }

.financial-summary { background: #f5ede0; border-radius: 10px; padding: 16px; }
.fin-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #2d1f14; padding: 5px 0; }
.fin-row:not(:last-child) { border-bottom: 1px solid rgba(139,94,60,0.12); }
.fin-amount { font-weight: 700; }
.remaining .fin-amount { color: #e53935; }
.paid span:last-child { color: #2d6a3f; font-weight: 600; }
</style>
