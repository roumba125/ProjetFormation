<template>
  <div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <span>🐑</span>
          <span class="logo-text" v-if="!sidebarCollapsed">BergerPro</span>
        </div>
        <button class="btn-collapse" @click="sidebarCollapsed = !sidebarCollapsed">
          {{ sidebarCollapsed ? '→' : '←' }}
        </button>
      </div>

      <nav class="sidebar-nav">
        <RouterLink :to="{ name: 'admin.dashboard' }" class="nav-item">
          <span class="nav-icon">📊</span>
          <span class="nav-label" v-if="!sidebarCollapsed">Dashboard</span>
        </RouterLink>

        <RouterLink :to="{ name: 'admin.sheep.index' }" class="nav-item">
          <span class="nav-icon">🐑</span>
          <span class="nav-label" v-if="!sidebarCollapsed">Moutons</span>
          <span class="nav-badge" v-if="!sidebarCollapsed && sheepCounts.available > 0">
            {{ sheepCounts.available }}
          </span>
        </RouterLink>

        <RouterLink :to="{ name: 'admin.orders.index' }" class="nav-item">
          <span class="nav-icon">📦</span>
          <span class="nav-label" v-if="!sidebarCollapsed">Commandes</span>
          <span class="nav-badge urgent" v-if="!sidebarCollapsed && orderCounts.pending > 0">
            {{ orderCounts.pending }}
          </span>
        </RouterLink>
      </nav>

      <div class="sidebar-footer" v-if="!sidebarCollapsed">
        <div class="user-info">
          <span class="user-avatar">👤</span>
          <div>
            <p class="user-name">{{ auth.user?.name }}</p>
            <p class="user-role">Administrateur</p>
          </div>
        </div>
        <button class="btn-logout" @click="handleLogout">Déconnexion</button>
      </div>
      <button v-else class="btn-logout-icon" @click="handleLogout" title="Déconnexion">🚪</button>
    </aside>

    <!-- Main content -->
    <div class="main-wrapper">
      <!-- Top bar -->
      <header class="topbar">
        <div class="topbar-left">
          <h2 class="page-title">{{ pageTitle }}</h2>
        </div>
        <div class="topbar-right">
          <span class="topbar-date">{{ currentDate }}</span>
          <a href="http://bergerpro.test" target="_blank" class="btn-view-site">
            🌐 Voir le site
          </a>
        </div>
      </header>

      <!-- Page content -->
      <main class="content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/admin/stores/auth'
import { useAdminSheepStore } from '@/admin/stores/sheep'
import { useAdminOrderStore } from '@/admin/stores/orders'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const sheepStore = useAdminSheepStore()
const orderStore = useAdminOrderStore()

const sidebarCollapsed = ref(false)

const sheepCounts = computed(() => sheepStore.counts)
const orderCounts = computed(() => orderStore.counts)

const pageTitles = {
  'admin.dashboard':    'Dashboard',
  'admin.sheep.index':  'Gestion des moutons',
  'admin.sheep.create': 'Ajouter un mouton',
  'admin.sheep.show':   'Fiche mouton',
  'admin.sheep.edit':   'Modifier un mouton',
  'admin.orders.index': 'Gestion des commandes',
  'admin.orders.show':  'Détail commande',
}

const pageTitle = computed(() => pageTitles[route.name] ?? 'Admin')

const currentDate = computed(() => {
  return new Date().toLocaleDateString('fr-FR', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
  })
})

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'admin.login' })
}

onMounted(() => {
  sheepStore.fetchSheep()
  orderStore.fetchOrders()
})
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f8f9fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* SIDEBAR */
.sidebar {
  width: 240px;
  background: white;
  border-right: 1px solid #e9ecef;
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 100;
  transition: width 0.25s ease;
}
.sidebar.collapsed { width: 64px; }

.sidebar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 16px;
  border-bottom: 1px solid #e9ecef;
}
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 1.1rem;
  color: #1a1a2e;
  overflow: hidden;
}
.sidebar-logo span:first-child { font-size: 1.4rem; flex-shrink: 0; }
.logo-text { white-space: nowrap; }

.btn-collapse {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 6px;
  width: 28px;
  height: 28px;
  cursor: pointer;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: background 0.2s;
}
.btn-collapse:hover { background: #e9ecef; }

.sidebar-nav {
  flex: 1;
  padding: 16px 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  text-decoration: none;
  color: #495057;
  font-size: 0.88rem;
  font-weight: 500;
  transition: all 0.2s;
  position: relative;
  white-space: nowrap;
  overflow: hidden;
}
.nav-item:hover { background: #f8f9fa; color: #4361ee; }
.nav-item.router-link-active { background: #eef1fd; color: #4361ee; font-weight: 600; }

.nav-icon { font-size: 1.1rem; flex-shrink: 0; }
.nav-label { flex: 1; }
.nav-badge {
  background: #4361ee;
  color: white;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 10px;
  min-width: 20px;
  text-align: center;
}
.nav-badge.urgent { background: #e63946; }

.sidebar-footer {
  padding: 16px;
  border-top: 1px solid #e9ecef;
}
.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}
.user-avatar { font-size: 1.5rem; }
.user-name  { font-size: 0.82rem; font-weight: 600; color: #1a1a2e; }
.user-role  { font-size: 0.72rem; color: #6c757d; }

.btn-logout {
  width: 100%;
  background: #fff3f3;
  border: 1px solid #f8d7da;
  color: #e63946;
  padding: 8px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 500;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s;
}
.btn-logout:hover { background: #e63946; color: white; }

.btn-logout-icon {
  margin: 12px auto;
  display: block;
  background: none;
  border: none;
  font-size: 1.3rem;
  cursor: pointer;
  padding: 8px;
}

/* MAIN */
.main-wrapper {
  flex: 1;
  margin-left: 240px;
  transition: margin-left 0.25s ease;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.sidebar.collapsed ~ .main-wrapper { margin-left: 64px; }

.topbar {
  background: white;
  border-bottom: 1px solid #e9ecef;
  padding: 0 32px;
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
}
.page-title { font-size: 1.1rem; font-weight: 700; color: #1a1a2e; }
.topbar-right { display: flex; align-items: center; gap: 16px; }
.topbar-date { font-size: 0.8rem; color: #6c757d; }
.btn-view-site {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  color: #495057;
  padding: 7px 14px;
  border-radius: 6px;
  font-size: 0.8rem;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
}
.btn-view-site:hover { background: #4361ee; color: white; border-color: #4361ee; }

.content { padding: 32px; flex: 1; }
</style>
