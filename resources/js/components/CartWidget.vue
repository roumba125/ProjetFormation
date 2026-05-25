<template>
  <div class="cart-widget" @click="$router.push('/commander')">
    <span class="cart-icon">🛒</span>
    <span class="cart-badge">{{ cart.count }}</span>
    <div class="cart-tooltip">
      <p>{{ cart.count }} mouton(s)</p>
      <p class="tooltip-total">{{ cart.totalFormatted }}</p>
    </div>
  </div>
</template>

<script setup>
import { useCartStore } from '@/stores/cart'
const cart = useCartStore()
</script>

<style scoped>
.cart-widget {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 58px;
  height: 58px;
  background: #8b5e3c;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 6px 24px rgba(139, 94, 60, 0.4);
  z-index: 400;
  transition: transform 0.2s;
}
.cart-widget:hover { transform: scale(1.08); }
.cart-widget:hover .cart-tooltip { opacity: 1; pointer-events: all; }

.cart-icon { font-size: 1.5rem; }

.cart-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: #e53935;
  color: white;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  font-size: 0.72rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid white;
}

.cart-tooltip {
  position: absolute;
  bottom: 68px;
  right: 0;
  background: #1a120b;
  color: white;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 0.8rem;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
}
.tooltip-total { font-weight: 700; color: #d4a574; margin-top: 2px; }
</style>
