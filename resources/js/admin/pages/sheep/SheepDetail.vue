<template>
  <div class="sheep-detail">
    <div class="page-header">
      <RouterLink :to="{ name: 'admin.sheep.index' }" class="btn-back">← Retour</RouterLink>
      <div class="header-actions" v-if="sheep">
        <RouterLink :to="{ name: 'admin.sheep.edit', params: { id: sheep.id } }" class="btn-edit">✏️ Modifier</RouterLink>
      </div>
    </div>

    <div v-if="loading" class="loading"><div class="spinner"></div> Chargement...</div>

    <div v-else-if="sheep" class="detail-grid">

      <!-- Infos principales -->
      <div class="form-card">
        <h3 class="card-title">🐑 {{ sheep.name }} — {{ sheep.reference }}</h3>
        <div class="info-grid">
          <div class="info-item"><span class="info-label">Race</span><span>{{ sheep.breed?.name }}</span></div>
          <div class="info-item"><span class="info-label">Genre</span><span>{{ sheep.gender_label }}</span></div>
          <div class="info-item"><span class="info-label">Âge</span><span>{{ sheep.age_formatted }}</span></div>
          <div class="info-item"><span class="info-label">Poids</span><span>{{ sheep.current_weight }} kg</span></div>
          <div class="info-item"><span class="info-label">Prix</span><span class="price">{{ sheep.price_formatted }}</span></div>
          <div class="info-item"><span class="info-label">Statut</span>
            <span class="status-badge" :class="`status-${sheep.status}`">{{ sheep.status_label }}</span>
          </div>
          <div class="info-item"><span class="info-label">Condition</span><span>{{ sheep.health_label }}</span></div>
          <div class="info-item"><span class="info-label">Alimentation</span><span>{{ sheep.current_diet ?? '—' }}</span></div>
        </div>
      </div>

      <!-- Photos -->
      <div class="form-card">
        <h3 class="card-title">📸 Photos</h3>
        <div class="photos-grid">
          <div v-for="photo in sheep.photos" :key="photo.id" class="photo-thumb">
            <img :src="photo.url" :alt="photo.caption" />
            <button @click="deletePhoto(photo.id)" class="btn-delete-photo">✕</button>
            <span v-if="photo.is_primary" class="photo-primary">⭐</span>
          </div>
          <label class="photo-upload">
            <input type="file" multiple accept="image/*" @change="uploadPhotos" ref="photoInput" />
            <span>+ Ajouter</span>
          </label>
        </div>
      </div>

      <!-- Ajouter vaccination -->
      <div class="form-card">
        <h3 class="card-title">💉 Vaccinations</h3>
        <div class="vac-list" v-if="sheep.vaccinations?.length">
          <div v-for="v in sheep.vaccinations" :key="v.id" class="vac-item" :class="`vac-${v.status}`">
            <div>
              <p class="vac-name">{{ v.vaccine_name }}</p>
              <p class="vac-meta">{{ v.administered_at }} — {{ v.administered_by ?? '—' }}</p>
            </div>
            <span class="vac-badge" :class="`badge-${v.status}`">{{ v.status_label }}</span>
          </div>
        </div>
        <p class="empty" v-else>Aucune vaccination enregistrée.</p>

        <!-- Formulaire ajout -->
        <div class="add-form" v-if="showVacForm">
          <div class="fields-grid-2">
            <div class="field">
              <label>Vaccin *</label>
              <input v-model="vacForm.vaccine_name" type="text" placeholder="Ex: PPR" required />
            </div>
            <div class="field">
              <label>Date *</label>
              <input v-model="vacForm.administered_at" type="date" :max="today" required />
            </div>
            <div class="field">
              <label>Prochain rappel</label>
              <input v-model="vacForm.next_due_at" type="date" />
            </div>
            <div class="field">
              <label>Vétérinaire</label>
              <input v-model="vacForm.administered_by" type="text" placeholder="Dr. ..." />
            </div>
          </div>
          <div class="add-actions">
            <button @click="showVacForm = false" class="btn-cancel-sm">Annuler</button>
            <button @click="submitVaccination" class="btn-add-sm" :disabled="submittingVac">
              {{ submittingVac ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </div>
        <button v-else @click="showVacForm = true" class="btn-add-item">+ Ajouter une vaccination</button>
      </div>

      <!-- Historique poids -->
      <div class="form-card">
        <h3 class="card-title">⚖️ Historique des poids</h3>
        <table class="mini-table" v-if="sheep.weight_records?.length">
          <thead><tr><th>Date</th><th>Poids</th><th>GMQ</th><th>Alimentation</th></tr></thead>
          <tbody>
            <tr v-for="w in sheep.weight_records" :key="w.id">
              <td>{{ w.recorded_at }}</td>
              <td><strong>{{ w.weight }} kg</strong></td>
              <td class="gmq" :class="{ positive: w.daily_gain > 0 }">{{ w.daily_gain ? '+' + w.daily_gain + ' g/j' : '—' }}</td>
              <td>{{ w.diet ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
        <p class="empty" v-else>Aucune pesée enregistrée.</p>

        <!-- Ajouter pesée -->
        <div class="add-form" v-if="showWeightForm">
          <div class="fields-grid-2">
            <div class="field">
              <label>Poids (kg) *</label>
              <input v-model.number="weightForm.weight" type="number" step="0.1" min="1" required />
            </div>
            <div class="field">
              <label>Date *</label>
              <input v-model="weightForm.recorded_at" type="date" :max="today" required />
            </div>
            <div class="field">
              <label>Alimentation</label>
              <input v-model="weightForm.diet" type="text" placeholder="Ex: Foin + Concentré" />
            </div>
            <div class="field">
              <label>Pesé par</label>
              <input v-model="weightForm.measured_by" type="text" />
            </div>
          </div>
          <div class="add-actions">
            <button @click="showWeightForm = false" class="btn-cancel-sm">Annuler</button>
            <button @click="submitWeight" class="btn-add-sm" :disabled="submittingWeight">
              {{ submittingWeight ? 'Enregistrement...' : 'Enregistrer' }}
            </button>
          </div>
        </div>
        <button v-else @click="showWeightForm = true" class="btn-add-item">+ Ajouter une pesée</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAdminSheepStore } from '@/admin/stores/sheep'

const route = useRoute()
const store = useAdminSheepStore()

const sheep   = ref(null)
const loading = ref(true)
const today   = new Date().toISOString().split('T')[0]

const showVacForm    = ref(false)
const submittingVac  = ref(false)
const vacForm = ref({ vaccine_name: '', administered_at: '', next_due_at: '', administered_by: '' })

const showWeightForm    = ref(false)
const submittingWeight  = ref(false)
const weightForm = ref({ weight: '', recorded_at: today, diet: '', measured_by: '' })

async function load() {
  loading.value = true
  try {
    sheep.value = await store.fetchOne(route.params.id)
  } finally {
    loading.value = false
  }
}

async function uploadPhotos(e) {
  const files = e.target.files
  if (!files.length) return
  const formData = new FormData()
  Array.from(files).forEach(f => formData.append('photos[]', f))
  await store.uploadPhotos(sheep.value.id, formData)
  await load()
}

async function deletePhoto(photoId) {
  if (!confirm('Supprimer cette photo ?')) return
  await store.deletePhoto(sheep.value.id, photoId)
  await load()
}

async function submitVaccination() {
  submittingVac.value = true
  try {
    await store.addVaccination(sheep.value.id, vacForm.value)
    vacForm.value = { vaccine_name: '', administered_at: '', next_due_at: '', administered_by: '' }
    showVacForm.value = false
    await load()
  } finally {
    submittingVac.value = false
  }
}

async function submitWeight() {
  submittingWeight.value = true
  try {
    await store.addWeight(sheep.value.id, weightForm.value)
    weightForm.value = { weight: '', recorded_at: today, diet: '', measured_by: '' }
    showWeightForm.value = false
    await load()
  } finally {
    submittingWeight.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.btn-back  { color: #4361ee; text-decoration: none; font-size: 0.85rem; font-weight: 500; }
.btn-edit  { background: #4361ee; color: white; padding: 9px 18px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; font-weight: 600; }

.loading { display: flex; align-items: center; gap: 10px; padding: 40px; color: #6c757d; justify-content: center; }
.spinner { width: 24px; height: 24px; border: 2px solid #dee2e6; border-top-color: #4361ee; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
@media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } }

.form-card { background: white; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef; }
.card-title { font-size: 0.95rem; font-weight: 600; color: #1a1a2e; margin-bottom: 16px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.info-item { display: flex; flex-direction: column; gap: 2px; background: #f8f9fa; padding: 10px 12px; border-radius: 8px; }
.info-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; font-weight: 600; }
.price { font-weight: 700; color: #1a1a2e; font-size: 1rem; }
.status-badge { padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; width: fit-content; }
.status-available { background: #d4edda; color: #155724; }
.status-reserved  { background: #fff3cd; color: #856404; }
.status-sold      { background: #e2e3e5; color: #383d41; }

.photos-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
.photo-thumb { position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; }
.photo-thumb img { width: 100%; height: 100%; object-fit: cover; }
.btn-delete-photo { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.6); color: white; border: none; width: 20px; height: 20px; border-radius: 50%; font-size: 0.65rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.photo-primary { position: absolute; bottom: 4px; left: 4px; font-size: 0.8rem; }
.photo-upload { aspect-ratio: 1; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #6c757d; font-size: 0.8rem; font-weight: 500; transition: all 0.2s; }
.photo-upload:hover { border-color: #4361ee; color: #4361ee; }
.photo-upload input { display: none; }

.vac-list { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
.vac-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-radius: 8px; background: #f8f9fa; border-left: 3px solid #dee2e6; }
.vac-item.vac-valid    { border-left-color: #2ecc71; background: #f0fff4; }
.vac-item.vac-expired  { border-left-color: #e63946; background: #fff3f3; }
.vac-item.vac-due_soon { border-left-color: #f39c12; background: #fffbf0; }
.vac-name { font-weight: 600; font-size: 0.83rem; color: #1a1a2e; }
.vac-meta { font-size: 0.72rem; color: #6c757d; }
.vac-badge { padding: 3px 8px; border-radius: 10px; font-size: 0.68rem; font-weight: 600; }
.badge-valid    { background: #d4edda; color: #155724; }
.badge-expired  { background: #f8d7da; color: #721c24; }
.badge-due_soon { background: #fff3cd; color: #856404; }

.mini-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; margin-bottom: 12px; }
.mini-table th { text-align: left; padding: 6px 10px; color: #6c757d; font-size: 0.7rem; text-transform: uppercase; border-bottom: 1px solid #e9ecef; }
.mini-table td { padding: 8px 10px; border-bottom: 1px solid #f8f9fa; }
.gmq.positive { color: #2ecc71; font-weight: 600; }

.add-form { background: #f8f9fa; border-radius: 10px; padding: 16px; margin-top: 12px; }
.fields-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
.field { display: flex; flex-direction: column; gap: 4px; }
.field label { font-size: 0.75rem; font-weight: 600; color: #495057; }
.field input { padding: 8px 10px; border: 1.5px solid #dee2e6; border-radius: 6px; font-size: 0.83rem; font-family: inherit; outline: none; }
.field input:focus { border-color: #4361ee; }

.add-actions { display: flex; gap: 8px; justify-content: flex-end; }
.btn-cancel-sm { background: white; border: 1px solid #dee2e6; color: #495057; padding: 7px 14px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-family: inherit; }
.btn-add-sm { background: #4361ee; color: white; border: none; padding: 7px 14px; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-family: inherit; font-weight: 600; }
.btn-add-sm:disabled { background: #adb5bd; cursor: not-allowed; }
.btn-add-item { background: none; border: 1.5px dashed #dee2e6; color: #6c757d; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 0.82rem; font-family: inherit; width: 100%; margin-top: 8px; transition: all 0.2s; }
.btn-add-item:hover { border-color: #4361ee; color: #4361ee; }
.empty { color: #6c757d; font-size: 0.82rem; text-align: center; padding: 16px 0; }
</style>
