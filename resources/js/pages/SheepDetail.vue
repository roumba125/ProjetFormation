<template>
  <div class="detail-page">
    <RouterLink to="/catalogue" class="btn-back">← Retour au catalogue</RouterLink>
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Chargement de la fiche…</p>
    </div>
    <div v-else-if="error" class="error-state">
      <p>😕 {{ error }}</p>
      <RouterLink to="/catalogue" class="btn-primary">Retour au catalogue</RouterLink>
    </div>
    <div v-else-if="sheep">
      <SheepModal
        :sheep-id="Number($route.params.id)"
        :is-open="true"
        @close="$router.push('/catalogue')"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useSheepStore } from '@/stores/sheep'
import SheepModal from '@/components/SheepModal.vue'

const route      = useRoute()
const sheepStore = useSheepStore()
const loading    = ref(true)
const error      = ref(null)
const sheep      = ref(null)

onMounted(async () => {
  try {
    sheep.value = await sheepStore.fetchSheepDetail(route.params.id)
  } catch (e) {
    error.value = 'Ce mouton est introuvable ou n\'est plus disponible.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.detail-page {
  padding: 100px 40px 60px;
  max-width: 900px;
  margin: 0 auto;
  min-height: 100vh;
}
.btn-back {
  display: inline-block;
  color: #8b5e3c;
  text-decoration: none;
  font-weight: 500;
  margin-bottom: 24px;
  font-size: 0.9rem;
  transition: color 0.2s;
}
.btn-back:hover { color: #1a120b; }

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 100px 20px;
  color: #8b6b55;
}
.spinner {
  width: 40px; height: 40px;
  border: 3px solid rgba(139,94,60,0.2);
  border-top-color: #8b5e3c;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 16px;
}
@keyframes spin { to { transform: rotate(360deg); } }

.error-state {
  text-align: center;
  padding: 80px 20px;
  color: #8b6b55;
}
.error-state p { font-size: 1.1rem; margin-bottom: 20px; }
.btn-primary {
  display: inline-block;
  background: #8b5e3c;
  color: white;
  padding: 12px 24px;
  border-radius: 10px;
  text-decoration: none;
  font-weight: 600;
}
</style>
