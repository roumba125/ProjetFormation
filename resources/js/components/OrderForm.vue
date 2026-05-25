<template>
  <div class="order-form-wrapper">
    <div class="order-form-container">

      <!-- Récapitulatif des moutons -->
      <div class="recap-section">
        <h3 class="section-title">🐑 Votre sélection ({{ cart.count }})</h3>
        <div class="recap-items">
          <div v-for="sheep in cart.items" :key="sheep.id" class="recap-item">
            <div class="recap-photo">
              <img v-if="sheep.primary_photo_url" :src="sheep.primary_photo_url" :alt="sheep.name" />
              <span v-else>🐑</span>
            </div>
            <div class="recap-info">
              <p class="recap-name">{{ sheep.name }} <span class="recap-ref">{{ sheep.reference }}</span></p>
              <p class="recap-breed">{{ sheep.breed?.name }} · {{ sheep.current_weight }} kg · {{ sheep.age_formatted }}</p>
            </div>
            <div class="recap-price">{{ sheep.price_formatted }}</div>
            <button class="recap-remove" @click="cart.removeSheep(sheep.id)" title="Retirer">✕</button>
          </div>
        </div>

        <!-- Total -->
        <div class="recap-total">
          <div class="total-row">
            <span>Sous-total moutons</span>
            <span>{{ cart.totalFormatted }}</span>
          </div>
          <div class="total-row" v-if="form.delivery_method === 'delivery' && deliveryFee > 0">
            <span>Frais de livraison</span>
            <span>{{ formatAmount(deliveryFee) }} FCFA</span>
          </div>
          <div class="total-row grand-total">
            <span>Total</span>
            <span>{{ formatAmount(grandTotal) }} FCFA</span>
          </div>
        </div>
      </div>

      <!-- Formulaire -->
      <form class="form-section" @submit.prevent="submitOrder" novalidate>
        <h3 class="section-title">👤 Vos informations</h3>

        <div class="form-grid">
          <div class="form-group">
            <label>Nom complet *</label>
            <input v-model="form.client_name" type="text" placeholder="Moussa Traoré" :class="{ error: errors.client_name }" />
            <span class="error-msg" v-if="errors.client_name">{{ errors.client_name[0] }}</span>
          </div>

          <div class="form-group">
            <label>Téléphone *</label>
            <input v-model="form.client_phone" type="tel" placeholder="+225 07 00 00 00 00" :class="{ error: errors.client_phone }" />
            <span class="error-msg" v-if="errors.client_phone">{{ errors.client_phone[0] }}</span>
          </div>

          <div class="form-group full">
            <label>Email *</label>
            <input v-model="form.client_email" type="email" placeholder="moussa@exemple.com" :class="{ error: errors.client_email }" />
            <span class="error-msg" v-if="errors.client_email">{{ errors.client_email[0] }}</span>
          </div>

          <div class="form-group">
            <label>Ville</label>
            <input v-model="form.client_city" type="text" placeholder="Abidjan" />
          </div>
        </div>

        <!-- Livraison -->
        <h3 class="section-title mt">🚚 Mode de réception</h3>

        <div class="delivery-options">
          <label class="delivery-option" :class="{ selected: form.delivery_method === 'pickup' }">
            <input type="radio" v-model="form.delivery_method" value="pickup" />
            <div class="option-content">
              <span class="option-icon">🏠</span>
              <div>
                <strong>Retrait à l'élevage</strong>
                <p>Venez chercher votre mouton sur place. Gratuit.</p>
              </div>
            </div>
          </label>

          <label class="delivery-option" :class="{ selected: form.delivery_method === 'delivery' }">
            <input type="radio" v-model="form.delivery_method" value="delivery" />
            <div class="option-content">
              <span class="option-icon">🚚</span>
              <div>
                <strong>Livraison à domicile</strong>
                <p>Frais selon votre ville (5 000 – 20 000 FCFA)</p>
              </div>
            </div>
          </label>
        </div>

        <div class="form-grid" v-if="form.delivery_method === 'delivery'">
          <div class="form-group full">
            <label>Adresse de livraison *</label>
            <textarea v-model="form.delivery_address" rows="2" placeholder="Quartier, rue, description..." :class="{ error: errors.delivery_address }"></textarea>
            <span class="error-msg" v-if="errors.delivery_address">{{ errors.delivery_address[0] }}</span>
          </div>
          <div class="form-group">
            <label>Ville de livraison *</label>
            <input v-model="form.delivery_city" type="text" placeholder="Abidjan" :class="{ error: errors.delivery_city }" />
            <span class="error-msg" v-if="errors.delivery_city">{{ errors.delivery_city[0] }}</span>
          </div>
          <div class="form-group">
            <label>Date souhaitée</label>
            <input v-model="form.desired_delivery_date" type="date" :min="minDate" />
          </div>
        </div>

        <!-- Paiement -->
        <h3 class="section-title mt">💳 Mode de paiement</h3>

        <div class="payment-grid">
          <label
            v-for="method in paymentMethods"
            :key="method.value"
            class="payment-option"
            :class="{ selected: form.payment_method === method.value }"
          >
            <input type="radio" v-model="form.payment_method" :value="method.value" />
            <span class="payment-icon">{{ method.icon }}</span>
            <span class="payment-label">{{ method.label }}</span>
          </label>
        </div>

        <!-- Acompte -->
        <div class="form-group mt">
          <label>Acompte à verser (optionnel)</label>
          <div class="acompte-input">
            <input
              v-model.number="form.deposit_amount"
              type="number"
              :max="grandTotal"
              min="0"
              step="1000"
              placeholder="0"
            />
            <span class="acompte-unit">FCFA</span>
          </div>
          <p class="field-hint" v-if="form.deposit_amount > 0">
            Reste à payer : <strong>{{ formatAmount(grandTotal - form.deposit_amount) }} FCFA</strong>
          </p>
        </div>

        <!-- Notes -->
        <div class="form-group mt">
          <label>Message / Notes (optionnel)</label>
          <textarea v-model="form.client_notes" rows="3" placeholder="Toute information utile pour votre commande…"></textarea>
        </div>

        <!-- Erreur globale -->
        <div class="global-error" v-if="globalError">
          ⚠️ {{ globalError }}
        </div>

        <!-- Bouton submit -->
        <button
          type="submit"
          class="btn-submit"
          :disabled="submitting || cart.isEmpty"
        >
          <span v-if="submitting">⏳ Envoi en cours…</span>
          <span v-else>✅ Confirmer la commande — {{ formatAmount(grandTotal) }} FCFA</span>
        </button>

        <p class="submit-note">
          Vous recevrez une confirmation par email et SMS sous 24h. Aucun paiement immédiat requis.
        </p>
      </form>

    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCartStore } from '@/stores/cart'
import axios from '@/lib/axios'

const emit = defineEmits(['success'])

const cart      = useCartStore()
const submitting = ref(false)
const errors     = ref({})
const globalError = ref(null)

const deliveryFees = {
  abidjan: 5000, bouaké: 15000, yamoussoukro: 10000,
  daloa: 18000, korhogo: 20000,
}

const form = ref({
  client_name:          '',
  client_email:         '',
  client_phone:         '',
  client_city:          '',
  delivery_method:      'pickup',
  delivery_address:     '',
  delivery_city:        '',
  desired_delivery_date: '',
  payment_method:       'orange_money',
  deposit_amount:       0,
  client_notes:         '',
})

const paymentMethods = [
  { value: 'orange_money',  label: 'Orange Money', icon: '🟠' },
  { value: 'wave',          label: 'Wave',          icon: '💙' },
  { value: 'moov_money',    label: 'Moov Money',    icon: '🟣' },
  { value: 'bank_transfer', label: 'Virement',      icon: '🏦' },
  { value: 'cash',          label: 'Espèces',       icon: '💵' },
]

const deliveryFee = computed(() => {
  if (form.value.delivery_method !== 'delivery') return 0
  const city = (form.value.delivery_city || '').toLowerCase().trim()
  return deliveryFees[city] ?? 10000
})

const grandTotal = computed(() => cart.total + deliveryFee.value)

const minDate = computed(() => {
  const d = new Date()
  d.setDate(d.getDate() + 1)
  return d.toISOString().split('T')[0]
})

function formatAmount(amount) {
  return new Intl.NumberFormat('fr-FR').format(amount)
}

async function submitOrder() {
  if (cart.isEmpty) return

  submitting.value = true
  errors.value     = {}
  globalError.value = null

  try {
    const payload = {
      sheep_ids: cart.items.map(s => s.id),
      ...form.value,
    }

    const { data } = await axios.post('/api/v1/orders', payload)

    cart.clear()
    emit('success', data.data)

  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {}
      globalError.value = err.response.data.message ?? 'Veuillez corriger les erreurs ci-dessous.'
    } else {
      globalError.value = 'Une erreur est survenue. Veuillez réessayer ou nous contacter directement.'
    }
    window.scrollTo({ top: 0, behavior: 'smooth' })
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.order-form-wrapper { max-width: 960px; margin: 0 auto; padding: 40px 20px; }

.order-form-container {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 32px;
  align-items: start;
}
@media (max-width: 768px) { .order-form-container { grid-template-columns: 1fr; } }

.section-title { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 700; color: #1a120b; margin-bottom: 16px; }
.section-title.mt { margin-top: 28px; }

/* Recap */
.recap-section { background: white; border-radius: 16px; padding: 24px; border: 1px solid rgba(139,94,60,0.1); box-shadow: 0 2px 16px rgba(0,0,0,0.05); position: sticky; top: 90px; }
.recap-items { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; }
.recap-item { display: flex; align-items: center; gap: 10px; padding: 10px; background: #f5ede0; border-radius: 10px; }
.recap-photo { width: 44px; height: 44px; border-radius: 8px; overflow: hidden; background: #e8d5b0; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
.recap-photo img { width: 100%; height: 100%; object-fit: cover; }
.recap-info { flex: 1; min-width: 0; }
.recap-name  { font-weight: 600; font-size: 0.85rem; color: #1a120b; }
.recap-ref   { font-size: 0.7rem; color: #a08070; margin-left: 6px; }
.recap-breed { font-size: 0.72rem; color: #8b6b55; margin-top: 2px; }
.recap-price { font-family: 'Playfair Display', serif; font-size: 0.95rem; font-weight: 700; color: #8b5e3c; white-space: nowrap; }
.recap-remove { background: none; border: none; font-size: 0.75rem; color: #c0a090; cursor: pointer; padding: 4px; transition: color 0.2s; }
.recap-remove:hover { color: #e53935; }

.recap-total { border-top: 1px solid rgba(139,94,60,0.15); padding-top: 14px; }
.total-row { display: flex; justify-content: space-between; font-size: 0.85rem; color: #6b4c35; margin-bottom: 8px; }
.grand-total { font-weight: 700; font-size: 1rem; color: #1a120b; border-top: 1px solid rgba(139,94,60,0.15); padding-top: 10px; margin-top: 4px; }

/* Form */
.form-section { background: white; border-radius: 16px; padding: 28px; border: 1px solid rgba(139,94,60,0.1); box-shadow: 0 2px 16px rgba(0,0,0,0.05); }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { font-size: 0.78rem; font-weight: 600; color: #5a3a20; }
.form-group input,
.form-group textarea { padding: 10px 14px; border: 1.5px solid rgba(139,94,60,0.2); border-radius: 8px; font-size: 0.85rem; font-family: 'DM Sans', sans-serif; color: #1a120b; transition: border-color 0.2s; outline: none; background: #fdf8f2; }
.form-group input:focus,
.form-group textarea:focus { border-color: #8b5e3c; background: white; }
.form-group input.error,
.form-group textarea.error { border-color: #e53935; }
.error-msg { font-size: 0.72rem; color: #e53935; }
.field-hint { font-size: 0.75rem; color: #4a6741; margin-top: 4px; }

/* Livraison */
.delivery-options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
.delivery-option { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 2px solid rgba(139,94,60,0.15); border-radius: 10px; cursor: pointer; transition: all 0.2s; }
.delivery-option input { display: none; }
.delivery-option.selected { border-color: #8b5e3c; background: rgba(139,94,60,0.04); }
.option-content { display: flex; align-items: center; gap: 12px; }
.option-icon { font-size: 1.5rem; }
.option-content strong { display: block; font-size: 0.85rem; color: #1a120b; margin-bottom: 2px; }
.option-content p { font-size: 0.75rem; color: #8b6b55; }

/* Paiement */
.payment-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 8px; }
@media (max-width: 500px) { .payment-grid { grid-template-columns: repeat(2, 1fr); } }
.payment-option { display: flex; flex-direction: column; align-items: center; gap: 4px; padding: 12px 8px; border: 2px solid rgba(139,94,60,0.15); border-radius: 10px; cursor: pointer; transition: all 0.2s; text-align: center; }
.payment-option input { display: none; }
.payment-option.selected { border-color: #8b5e3c; background: rgba(139,94,60,0.06); }
.payment-icon  { font-size: 1.4rem; }
.payment-label { font-size: 0.72rem; font-weight: 600; color: #1a120b; }

/* Acompte */
.acompte-input { display: flex; align-items: center; border: 1.5px solid rgba(139,94,60,0.2); border-radius: 8px; overflow: hidden; background: #fdf8f2; }
.acompte-input input { flex: 1; border: none; padding: 10px 14px; font-size: 0.85rem; font-family: 'DM Sans', sans-serif; background: transparent; outline: none; }
.acompte-unit { padding: 10px 14px; background: #f5ede0; font-size: 0.8rem; color: #8b5e3c; font-weight: 600; }
.form-group.mt { margin-top: 8px; }

/* Submit */
.global-error { background: #fde8e8; border: 1px solid #f5c6c6; color: #721c24; padding: 12px 16px; border-radius: 8px; font-size: 0.83rem; margin: 16px 0; }
.btn-submit { width: 100%; padding: 15px; background: #8b5e3c; color: white; border: none; border-radius: 10px; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: all 0.2s; margin-top: 20px; font-family: 'DM Sans', sans-serif; }
.btn-submit:hover:not(:disabled) { background: #1a120b; }
.btn-submit:disabled { background: #c0a090; cursor: not-allowed; }
.submit-note { font-size: 0.72rem; color: #8b6b55; text-align: center; margin-top: 10px; }
</style>
