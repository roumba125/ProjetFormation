<template>
  <div class="checkout-page">
    <div class="checkout-header">
      <RouterLink to="/catalogue" class="btn-back">← Retour</RouterLink>
      <h1>Finaliser ma commande</h1>
    </div>

    <!-- Panier vide -->
    <div class="empty-cart" v-if="cart.isEmpty">
      <p>🛒</p>
      <h2>Votre panier est vide</h2>
      <p>Ajoutez des moutons depuis le catalogue.</p>
      <RouterLink to="/catalogue" class="btn-primary">Voir le catalogue</RouterLink>
    </div>

    <!-- Formulaire -->
    <OrderForm v-else @success="onSuccess" />

    <!-- Overlay succès -->
    <div class="success-overlay" v-if="orderSuccess">
      <div class="success-card">
        <div class="success-icon">✅</div>
        <h2>Commande confirmée !</h2>
        <p class="order-num">{{ orderData.order_number }}</p>
        <p>Nous vous contacterons sous 24h.</p>
        <div class="success-details">
          <div class="detail-row">
            <span>Total</span>
            <span>{{ fmt(orderData.total_amount) }} FCFA</span>
          </div>
          <div class="detail-row" v-if="orderData.deposit_amount > 0">
            <span>Acompte</span>
            <span>{{ fmt(orderData.deposit_amount) }} FCFA</span>
          </div>
          <div class="detail-row" v-if="orderData.remaining > 0">
            <span>Reste à payer</span>
            <span>{{ fmt(orderData.remaining) }} FCFA</span>
          </div>
        </div>
        <div class="success-actions">
          <RouterLink to="/catalogue" class="btn-secondary">Continuer</RouterLink>
          <RouterLink :to="`/suivi?ref=${orderData.order_number}`" class="btn-primary">Suivre</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'
import OrderForm from '@/components/OrderForm.vue'

const cart         = useCartStore()
const orderSuccess = ref(false)
const orderData    = ref(null)

function onSuccess(data) {
  orderData.value    = data
  orderSuccess.value = true
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

function fmt(a) {
  return new Intl.NumberFormat('fr-FR').format(a)
}
</script>

<style scoped>
.checkout-page { max-width: 1060px; margin: 0 auto; padding: 100px 20px 60px; min-height: 100vh; }
.checkout-header { display: flex; align-items: center; gap: 20px; margin-bottom: 36px; }
.checkout-header h1 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #1a120b; }
.btn-back { color: #8b5e3c; text-decoration: none; font-weight: 500; font-size: 0.9rem; }

.empty-cart { text-align: center; padding: 80px 20px; }
.empty-cart p:first-child { font-size: 3.5rem; margin-bottom: 14px; }
.empty-cart h2 { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 8px; color: #1a120b; }
.btn-primary { display: inline-block; background: #8b5e3c; color: white; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 600; margin-top: 16px; }

.success-overlay {
  position: fixed; inset: 0;
  background: rgba(26,18,11,0.7);
  backdrop-filter: blur(6px);
  z-index: 600;
  display: flex; align-items: center; justify-content: center; padding: 20px;
}
.success-card { background: white; border-radius: 20px; padding: 40px; max-width: 480px; width: 100%; text-align: center; }
.success-icon { font-size: 3rem; margin-bottom: 16px; }
.success-card h2 { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #1a120b; margin-bottom: 8px; }
.order-num { font-size: 1rem; font-weight: 700; color: #8b5e3c; margin-bottom: 10px; letter-spacing: 1px; }
.success-details { background: #f5ede0; border-radius: 10px; padding: 16px; margin: 20px 0; text-align: left; }
.detail-row { display: flex; justify-content: space-between; font-size: 0.85rem; padding: 5px 0; border-bottom: 1px solid rgba(139,94,60,0.1); }
.success-actions { display: flex; gap: 12px; justify-content: center; margin-top: 20px; flex-wrap: wrap; }
.btn-secondary { display: inline-block; border: 2px solid #8b5e3c; color: #8b5e3c; padding: 11px 20px; border-radius: 10px; text-decoration: none; font-weight: 600; }
</style>
