<template>
  <Teleport to="body">
    <div
      class="modal-overlay"
      :class="{ open: isOpen }"
      @click.self="close"
      role="dialog"
      aria-modal="true"
    >
      <div class="modal" v-if="sheep">

        <!-- HEADER : image + infos de base -->
        <div class="modal-header">
          <!-- Galerie photos -->
          <div class="modal-gallery">
            <div class="gallery-main">
              <img
                v-if="activePhoto"
                :src="activePhoto.url"
                :alt="sheep.name"
              />
              <div v-else class="gallery-placeholder">🐑</div>
            </div>
            <div class="gallery-thumbs" v-if="sheep.photos?.length > 1">
              <button
                v-for="photo in sheep.photos"
                :key="photo.id"
                class="thumb"
                :class="{ active: activePhoto?.id === photo.id }"
                @click="activePhoto = photo"
              >
                <img :src="photo.url" :alt="photo.caption" />
              </button>
            </div>
          </div>

          <!-- Infos identité -->
          <div class="modal-identity">
            <button class="btn-close" @click="close" aria-label="Fermer">✕</button>

            <div class="identity-badge" :class="`badge-${sheep.status}`">
              {{ sheep.status_label }}
            </div>

            <p class="identity-breed">{{ sheep.breed?.name }}</p>
            <h2 class="identity-name">{{ sheep.name }}</h2>
            <p class="identity-ref">{{ sheep.reference }} · {{ sheep.gender_label }}</p>
            <p class="identity-ref">Né(e) le {{ formatDate(sheep.birth_date) }}</p>

            <!-- Specs grille -->
            <div class="info-grid">
              <div class="info-box">
                <span class="val">{{ sheep.age_formatted }}</span>
                <span class="lbl">Âge</span>
              </div>
              <div class="info-box">
                <span class="val">{{ sheep.current_weight }} kg</span>
                <span class="lbl">Poids actuel</span>
              </div>
              <div class="info-box" v-if="sheep.height">
                <span class="val">{{ sheep.height }} cm</span>
                <span class="lbl">Hauteur</span>
              </div>
              <div class="info-box">
                <span class="val">{{ sheep.health_label }}</span>
                <span class="lbl">Condition</span>
              </div>
            </div>

            <p v-if="sheep.physical_description" class="identity-desc">
              {{ sheep.physical_description }}
            </p>
          </div>
        </div>

        <!-- BODY : détails complets -->
        <div class="modal-body">

          <!-- Alimentation -->
          <div class="detail-section" v-if="sheep.current_diet">
            <h4 class="section-title">🌿 Alimentation actuelle</h4>
            <p class="diet-text">{{ sheep.current_diet }}</p>
          </div>

          <!-- Vaccinations -->
          <div class="detail-section" v-if="sheep.vaccinations?.length">
            <h4 class="section-title">💉 Historique des vaccinations</h4>
            <div class="vac-list">
              <div
                v-for="vac in sheep.vaccinations"
                :key="vac.id"
                class="vac-item"
                :class="`status-${vac.status}`"
              >
                <div class="vac-icon">💉</div>
                <div class="vac-info">
                  <span class="vac-name">{{ vac.vaccine_name }}</span>
                  <span class="vac-meta">
                    {{ vac.administered_at }}
                    <span v-if="vac.administered_by"> · Dr. {{ vac.administered_by }}</span>
                  </span>
                </div>
                <div class="vac-right">
                  <span class="vac-status" :class="`vac-${vac.status}`">
                    {{ vac.status_label }}
                  </span>
                  <span class="vac-next" v-if="vac.next_due_at">
                    Rappel : {{ vac.next_due_at }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Évolution du poids -->
          <div class="detail-section" v-if="sheep.weight_records?.length">
            <h4 class="section-title">📈 Évolution du poids</h4>

            <!-- Graphique simple en SVG -->
            <div class="weight-chart">
              <WeightChart :records="sheep.weight_records" />
            </div>

            <!-- Tableau -->
            <div class="weight-table-wrapper">
              <table class="weight-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Poids (kg)</th>
                    <th>GMQ (g/j)</th>
                    <th>Alimentation</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="r in sheep.weight_records" :key="r.id">
                    <td>{{ r.recorded_at }}</td>
                    <td class="weight-val">{{ r.weight }}</td>
                    <td class="gmq-val" :class="{ positive: r.daily_gain > 0 }">
                      {{ r.daily_gain ? '+' + r.daily_gain + ' g/j' : '—' }}
                    </td>
                    <td>{{ r.diet || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Parents -->
          <div class="detail-section parents-section" v-if="sheep.mother || sheep.father">
            <h4 class="section-title">🐑 Filiation</h4>
            <div class="parents-grid">
              <div class="parent-card" v-if="sheep.mother">
                <span class="parent-label">Mère</span>
                <span class="parent-name">{{ sheep.mother.name }}</span>
                <span class="parent-ref">{{ sheep.mother.reference }}</span>
              </div>
              <div class="parent-card" v-if="sheep.father">
                <span class="parent-label">Père</span>
                <span class="parent-name">{{ sheep.father.name }}</span>
                <span class="parent-ref">{{ sheep.father.reference }}</span>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div class="detail-section" v-if="sheep.notes">
            <h4 class="section-title">📝 Notes de l'éleveur</h4>
            <p class="notes-text">{{ sheep.notes }}</p>
          </div>

        </div>

        <!-- FOOTER : prix + actions -->
        <div class="modal-footer">
          <div class="footer-price">
            <span class="price-label">Prix de vente</span>
            <span class="price-amount">{{ sheep.price_formatted }}</span>
          </div>

          <div class="footer-actions">
            <button class="btn-secondary" @click="toggleFavorite">
              {{ isFav ? '❤️ Favori' : '🤍 Favori' }}
            </button>

            <button
              v-if="sheep.status === 'available'"
              class="btn-primary"
              :class="{ 'in-cart': inCart }"
              @click="handleCart"
            >
              {{ inCart ? '✓ Dans le panier' : '🛒 Commander' }}
            </button>

            <span v-else class="status-chip" :class="`chip-${sheep.status}`">
              {{ sheep.status_label }}
            </span>
          </div>
        </div>

      </div>

      <!-- Loader -->
      <div class="modal-loader" v-else-if="loading">
        <div class="spinner"></div>
        <p>Chargement de la fiche…</p>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useSheepStore } from '@/stores/sheep'
import { useCartStore } from '@/stores/cart'
import WeightChart from './WeightChart.vue'

const props = defineProps({
  sheepId: { type: Number, default: null },
  isOpen:  { type: Boolean, default: false },
})
const emit = defineEmits(['close'])

const sheepStore = useSheepStore()
const cartStore  = useCartStore()

const sheep       = computed(() => sheepStore.currentSheep)
const loading     = computed(() => sheepStore.loading)
const activePhoto = ref(null)
const favorites   = ref(JSON.parse(localStorage.getItem('favorites') || '[]'))

const inCart  = computed(() => sheep.value ? cartStore.hasItem(sheep.value.id) : false)
const isFav   = computed(() => sheep.value ? favorites.value.includes(sheep.value.id) : false)

// Charger la fiche quand le modal s'ouvre
watch(() => props.sheepId, async (id) => {
  if (id) {
    await sheepStore.fetchSheepDetail(id)
    activePhoto.value = sheep.value?.photos?.[0] ?? null
  }
}, { immediate: true })

// Bloquer le scroll du body
watch(() => props.isOpen, (open) => {
  document.body.style.overflow = open ? 'hidden' : ''
})

function close() {
  emit('close')
}

function handleCart() {
  if (!sheep.value) return
  inCart.value ? cartStore.removeSheep(sheep.value.id) : cartStore.addSheep(sheep.value)
}

function toggleFavorite() {
  if (!sheep.value) return
  const id  = sheep.value.id
  const idx = favorites.value.indexOf(id)
  if (idx === -1) {
    favorites.value.push(id)
  } else {
    favorites.value.splice(idx, 1)
  }
  localStorage.setItem('favorites', JSON.stringify(favorites.value))
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}
</script>

<style scoped>
/* Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(26, 18, 11, 0.65);
  backdrop-filter: blur(6px);
  z-index: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s ease;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }

/* Modal container */
.modal {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 860px;
  max-height: 92vh;
  overflow-y: auto;
  transform: translateY(20px) scale(0.97);
  transition: transform 0.3s ease;
  scrollbar-width: thin;
}
.modal-overlay.open .modal { transform: translateY(0) scale(1); }

/* Header */
.modal-header {
  display: grid;
  grid-template-columns: 300px 1fr;
  min-height: 300px;
}
@media (max-width: 680px) {
  .modal-header { grid-template-columns: 1fr; }
}

/* Gallery */
.modal-gallery { background: #f5ede0; border-radius: 20px 0 0 0; overflow: hidden; }
.gallery-main  { height: 240px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.gallery-main img { width: 100%; height: 100%; object-fit: cover; }
.gallery-placeholder { font-size: 100px; }
.gallery-thumbs { display: flex; gap: 6px; padding: 8px; background: rgba(0,0,0,0.04); overflow-x: auto; }
.thumb { width: 52px; height: 52px; border-radius: 6px; overflow: hidden; border: 2px solid transparent; cursor: pointer; flex-shrink: 0; transition: border-color 0.2s; padding: 0; }
.thumb.active { border-color: #8b5e3c; }
.thumb img { width: 100%; height: 100%; object-fit: cover; }

/* Identity */
.modal-identity { padding: 24px 24px 20px; position: relative; }
.btn-close { position: absolute; top: 16px; right: 16px; border: none; background: #f5ede0; width: 36px; height: 36px; border-radius: 50%; font-size: 1rem; cursor: pointer; color: #1a120b; transition: background 0.2s; }
.btn-close:hover { background: #e0d0c0; }

.identity-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; margin-bottom: 10px; }
.badge-available { background: #d4edda; color: #1e5631; }
.badge-reserved  { background: #fff3cd; color: #7d5a00; }
.badge-sold      { background: #f8d7da; color: #721c24; }

.identity-breed { font-size: 0.7rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #8b5e3c; margin-bottom: 4px; }
.identity-name  { font-family: 'Playfair Display', serif; font-size: 1.7rem; font-weight: 700; color: #1a120b; margin-bottom: 4px; }
.identity-ref   { font-size: 0.75rem; color: #a08070; margin-bottom: 2px; }
.identity-desc  { font-size: 0.82rem; color: #6b4c35; line-height: 1.6; margin-top: 10px; }

.info-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 16px 0; }
.info-box { background: #f5ede0; border-radius: 8px; padding: 10px 8px; text-align: center; }
.info-box .val { display: block; font-weight: 700; font-size: 0.9rem; color: #1a120b; }
.info-box .lbl { font-size: 0.62rem; color: #8b6b55; }

/* Body */
.modal-body { padding: 0 24px 10px; }
.detail-section { padding: 18px 0; border-top: 1px solid rgba(139, 94, 60, 0.1); }
.section-title { font-family: 'Playfair Display', serif; font-size: 0.95rem; font-weight: 700; color: #1a120b; margin-bottom: 14px; display: flex; align-items: center; gap: 6px; }

.diet-text { font-size: 0.85rem; color: #4a6741; font-weight: 500; background: rgba(74,103,65,0.08); padding: 8px 12px; border-radius: 8px; display: inline-block; }

/* Vaccinations */
.vac-list { display: flex; flex-direction: column; gap: 8px; }
.vac-item { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; border-left: 3px solid #4a6741; background: rgba(74,103,65,0.05); }
.vac-item.status-expired { border-left-color: #e53935; background: rgba(229,57,53,0.04); }
.vac-item.status-due_soon { border-left-color: #f59e0b; background: rgba(245,158,11,0.05); }
.vac-icon { font-size: 1.1rem; }
.vac-info { flex: 1; }
.vac-name { display: block; font-weight: 600; font-size: 0.83rem; color: #1a120b; }
.vac-meta { font-size: 0.72rem; color: #8b6b55; }
.vac-right { text-align: right; }
.vac-status { display: block; font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 10px; }
.vac-valid    { background: #d4edda; color: #1e5631; }
.vac-expired  { background: #f8d7da; color: #721c24; }
.vac-due_soon { background: #fff3cd; color: #7d5a00; }
.vac-next { font-size: 0.68rem; color: #a08070; display: block; margin-top: 2px; }

/* Poids */
.weight-chart { margin-bottom: 14px; }
.weight-table-wrapper { overflow-x: auto; }
.weight-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.weight-table th { text-align: left; padding: 8px 12px; color: #8b5e3c; font-weight: 600; background: #f5ede0; }
.weight-table td { padding: 8px 12px; border-top: 1px solid rgba(139,94,60,0.08); }
.weight-val  { font-weight: 700; color: #1a120b; }
.gmq-val.positive { color: #2d6a3f; font-weight: 600; }

/* Parents */
.parents-grid { display: flex; gap: 12px; }
.parent-card { flex: 1; background: #f5ede0; border-radius: 10px; padding: 14px; }
.parent-label { display: block; font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px; color: #8b6b55; margin-bottom: 4px; font-weight: 600; }
.parent-name  { display: block; font-weight: 700; color: #1a120b; font-size: 0.9rem; }
.parent-ref   { display: block; font-size: 0.72rem; color: #a08070; }

/* Notes */
.notes-text { font-size: 0.82rem; color: #6b4c35; line-height: 1.7; font-style: italic; background: #fdf8f2; padding: 12px 16px; border-radius: 8px; border-left: 3px solid #c8893a; }

/* Footer */
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-top: 1px solid rgba(139, 94, 60, 0.12);
  background: #f5ede0;
  border-radius: 0 0 20px 20px;
  flex-wrap: wrap;
  gap: 12px;
}
.price-label  { display: block; font-size: 0.72rem; color: #8b6b55; margin-bottom: 2px; }
.price-amount { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 900; color: #8b5e3c; }
.footer-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

.btn-primary { background: #8b5e3c; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-primary:hover { background: #1a120b; }
.btn-primary.in-cart { background: #4a6741; }
.btn-secondary { background: white; color: #8b5e3c; border: 2px solid #8b5e3c; padding: 12px 18px; border-radius: 10px; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: all 0.2s; }
.btn-secondary:hover { background: #8b5e3c; color: white; }

.status-chip { padding: 10px 18px; border-radius: 10px; font-size: 0.82rem; font-weight: 600; }
.chip-reserved { background: #fff3cd; color: #7d5a00; }
.chip-sold     { background: #f8d7da; color: #721c24; }

/* Loader */
.modal-loader { color: white; text-align: center; }
.spinner { width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 12px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
