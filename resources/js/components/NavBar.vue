<template>
  <nav class="navbar">
    <RouterLink to="/" class="logo">Berger<span>Pro</span></RouterLink>

    <ul class="nav-links">
      <li><RouterLink to="/">Accueil</RouterLink></li>
      <li><RouterLink to="/catalogue">Catalogue</RouterLink></li>
      <li><RouterLink to="/suivi">Suivi commande</RouterLink></li>
    </ul>

    <RouterLink to="/commander" class="nav-cta" v-if="cart.count > 0">
      🛒 Panier ({{ cart.count }})
    </RouterLink>
    <RouterLink to="/catalogue" class="nav-cta" v-else>
      Voir les moutons
    </RouterLink>

    <!-- Menu burger mobile -->
    <button class="burger" @click="menuOpen = !menuOpen">☰</button>

    <!-- Menu mobile -->
    <div class="mobile-menu" :class="{ open: menuOpen }">
      <RouterLink to="/" @click="menuOpen = false">Accueil</RouterLink>
      <RouterLink to="/catalogue" @click="menuOpen = false">Catalogue</RouterLink>
      <RouterLink to="/suivi" @click="menuOpen = false">Suivi commande</RouterLink>
      <RouterLink to="/commander" @click="menuOpen = false">🛒 Panier ({{ cart.count }})</RouterLink>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue'
import { useCartStore } from '@/stores/cart'

const cart     = useCartStore()
const menuOpen = ref(false)
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 100;
  background: rgba(253, 248, 242, 0.95);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(139, 94, 60, 0.15);
  padding: 0 40px;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-sizing: border-box;
}

.logo {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 900;
  color: #8b5e3c;
  text-decoration: none;
  letter-spacing: -0.5px;
}
.logo span { color: #4a6741; }

.nav-links {
  display: flex;
  gap: 32px;
  list-style: none;
}
.nav-links a {
  text-decoration: none;
  color: #2d1f14;
  font-size: 0.9rem;
  font-weight: 500;
  transition: color 0.2s;
}
.nav-links a:hover,
.nav-links a.router-link-active { color: #8b5e3c; }

.nav-cta {
  background: #8b5e3c;
  color: white !important;
  padding: 10px 22px;
  border-radius: 6px;
  font-size: 0.88rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.2s;
}
.nav-cta:hover { background: #1a120b; }

/* Burger */
.burger {
  display: none;
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #8b5e3c;
}

/* Mobile menu */
.mobile-menu {
  display: none;
  position: fixed;
  top: 72px;
  left: 0;
  right: 0;
  background: #fdf8f2;
  flex-direction: column;
  padding: 20px;
  gap: 16px;
  border-bottom: 1px solid rgba(139,94,60,0.15);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}
.mobile-menu.open { display: flex; }
.mobile-menu a {
  text-decoration: none;
  color: #2d1f14;
  font-size: 1rem;
  font-weight: 500;
  padding: 8px 0;
  border-bottom: 1px solid rgba(139,94,60,0.1);
}
.mobile-menu a:hover { color: #8b5e3c; }

@media (max-width: 768px) {
  .nav-links, .nav-cta { display: none; }
  .burger { display: block; }
  .navbar { padding: 0 20px; }
}
</style>