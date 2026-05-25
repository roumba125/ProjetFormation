<template>
  <div class="sheep-form">

    <div class="form-header">
      <RouterLink :to="{ name: 'admin.sheep.index' }" class="btn-back">← Retour</RouterLink>
      <h2>{{ isEdit ? 'Modifier le mouton' : 'Ajouter un mouton' }}</h2>
    </div>

    <form @submit.prevent="handleSubmit" class="form-grid">

      <!-- Identité -->
      <div class="form-card">
        <h3 class="card-title">🐑 Identité</h3>
        <div class="fields-grid">
          <div class="field">
            <label>Nom *</label>
            <input v-model="form.name" type="text" placeholder="Ex: Alpha" required />
            <span class="error" v-if="errors.name">{{ errors.name[0] }}</span>
          </div>
          <div class="field">
            <label>Race *</label>
            <select v-model="form.breed_id" required>
              <option value="">Choisir une race</option>
              <option v-for="b in breeds" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <span class="error" v-if="errors.breed_id">{{ errors.breed_id[0] }}</span>
          </div>
          <div class="field">
            <label>Genre *</label>
            <select v-model="form.gender" required>
              <option value="">Choisir</option>
              <option value="male">🐏 Bélier</option>
              <option value="female">🐑 Brebis</option>
            </select>
          </div>
          <div class="field">
            <label>Date de naissance *</label>
            <input v-model="form.birth_date" type="date" required :max="today" />
          </div>
          <div class="field">
            <label>Boucle auriculaire</label>
            <input v-model="form.ear_tag" type="text" placeholder="Ex: BF-2024-001" />
          </div>
          <div class="field">
            <label>Couleur de la robe</label>
            <input v-model="form.coat_color" type="text" placeholder="Ex: Brun clair" />
          </div>
        </div>
        <div class="field full">
          <label>Description physique</label>
          <textarea v-model="form.physical_description" rows="2" placeholder="Description de l'animal..."></textarea>
        </div>
      </div>

      <!-- Physique & santé -->
      <div class="form-card">
        <h3 class="card-title">⚖️ Physique & Santé</h3>
        <div class="fields-grid">
          <div class="field">
            <label>Poids actuel (kg) *</label>
            <input v-model.number="form.current_weight" type="number" step="0.1" min="1" required placeholder="Ex: 45.5" />
            <span class="error" v-if="errors.current_weight">{{ errors.current_weight[0] }}</span>
          </div>
          <div class="field">
            <label>Hauteur (cm)</label>
            <input v-model.number="form.height" type="number" step="0.5" placeholder="Ex: 75" />
          </div>
          <div class="field">
            <label>Condition sanitaire *</label>
            <select v-model="form.health_condition" required>
              <option value="excellent">⭐ Excellent</option>
              <option value="good">✅ Bon</option>
              <option value="fair">⚠️ Passable</option>
              <option value="poor">❌ Mauvais</option>
            </select>
          </div>
          <div class="field">
            <label>Dernier contrôle vétérinaire</label>
            <input v-model="form.last_vet_checkup" type="date" :max="today" />
          </div>
        </div>
        <div class="field full">
          <label>Alimentation actuelle</label>
          <input v-model="form.current_diet" type="text" placeholder="Ex: Foin + Concentré + Sel minéral" />
        </div>
      </div>

      <!-- Vente -->
      <div class="form-card">
        <h3 class="card-title">💰 Vente</h3>
        <div class="fields-grid">
          <div class="field">
            <label>Prix (FCFA) *</label>
            <input v-model.number="form.price" type="number" min="0" required placeholder="Ex: 85000" />
            <span class="error" v-if="errors.price">{{ errors.price[0] }}</span>
          </div>
          <div class="field">
            <label>Prix négociable (FCFA)</label>
            <input v-model.number="form.negotiable_price" type="number" min="0" placeholder="Optionnel" />
          </div>
          <div class="field">
            <label>Statut *</label>
            <select v-model="form.status" required>
              <option value="available">✅ Disponible</option>
              <option value="reserved">⏳ Réservé</option>
              <option value="sold">🔴 Vendu</option>
            </select>
          </div>
          <div class="field">
            <label>Mise en avant</label>
            <div class="toggle-field">
              <input type="checkbox" v-model="form.is_featured" id="featured" />
              <label for="featured">Afficher en vedette sur le site</label>
            </div>
          </div>
        </div>
        <div class="field full">
          <label>Notes internes</label>
          <textarea v-model="form.notes" rows="2" placeholder="Notes visibles seulement dans l'admin..."></textarea>
        </div>
      </div>

      <!-- Erreur globale -->
      <div class="global-error" v-if="globalError">
        ⚠️ {{ globalError }}
      </div>

      <!-- Actions -->
      <div class="form-actions">
        <RouterLink :to="{ name: 'admin.sheep.index' }" class="btn-cancel">Annuler</RouterLink>
        <button type="submit" class="btn-submit" :disabled="submitting">
          {{ submitting ? 'Enregistrement...' : (isEdit ? '💾 Mettre à jour' : '✅ Créer le mouton') }}
        </button>
      </div>

    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAdminSheepStore } from '@/admin/stores/sheep'
import { api } from '@/admin/stores/auth'

const route  = useRoute()
const router = useRouter()
const store  = useAdminSheepStore()

const isEdit    = computed(() => !!route.params.id)
const submitting = ref(false)
const errors     = ref({})
const globalError = ref(null)
const breeds     = ref([])
const today      = new Date().toISOString().split('T')[0]

const form = ref({
  name: '', breed_id: '', gender: '', birth_date: '',
  ear_tag: '', coat_color: '', physical_description: '',
  current_weight: '', height: '', health_condition: 'good',
  last_vet_checkup: '', current_diet: '',
  price: '', negotiable_price: '', status: 'available',
  is_featured: false, notes: '',
})

async function loadBreeds() {
  const { data } = await api.get('/api/v1/breeds')
  breeds.value = data.data
}

async function loadSheep() {
  if (!isEdit.value) return
  const sheep = await store.fetchOne(route.params.id)
  form.value = {
    name:                 sheep.name,
    breed_id:             sheep.breed?.id ?? '',
    gender:               sheep.gender,
    birth_date:           sheep.birth_date,
    ear_tag:              sheep.ear_tag ?? '',
    coat_color:           sheep.coat_color ?? '',
    physical_description: sheep.physical_description ?? '',
    current_weight:       sheep.current_weight,
    height:               sheep.height ?? '',
    health_condition:     sheep.health_condition,
    last_vet_checkup:     sheep.last_vet_checkup ?? '',
    current_diet:         sheep.current_diet ?? '',
    price:                sheep.price,
    negotiable_price:     sheep.negotiable_price ?? '',
    status:               sheep.status,
    is_featured:          sheep.is_featured,
    notes:                sheep.notes ?? '',
  }
}

async function handleSubmit() {
  submitting.value  = true
  errors.value      = {}
  globalError.value = null

  try {
    if (isEdit.value) {
      await store.update(route.params.id, form.value)
    } else {
      await store.create(form.value)
    }
    router.push({ name: 'admin.sheep.index' })
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors ?? {}
      globalError.value = 'Veuillez corriger les erreurs.'
    } else {
      globalError.value = 'Une erreur est survenue.'
    }
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await loadBreeds()
  await loadSheep()
})
</script>

<style scoped>
.form-header { display: flex; align-items: center; gap: 16px; margin-bottom: 24px; }
.form-header h2 { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; }
.btn-back { color: #4361ee; text-decoration: none; font-size: 0.85rem; font-weight: 500; }

.form-grid { display: flex; flex-direction: column; gap: 20px; }

.form-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #e9ecef;
}
.card-title { font-size: 0.95rem; font-weight: 600; color: #1a1a2e; margin-bottom: 20px; }

.fields-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px; }
@media (max-width: 768px) { .fields-grid { grid-template-columns: 1fr; } }

.field { display: flex; flex-direction: column; gap: 5px; }
.field.full { grid-column: 1 / -1; }
.field label { font-size: 0.78rem; font-weight: 600; color: #495057; }
.field input, .field select, .field textarea {
  padding: 9px 12px;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.85rem;
  font-family: inherit;
  color: #1a1a2e;
  outline: none;
  transition: border-color 0.2s;
  background: white;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color: #4361ee; }
.error { font-size: 0.72rem; color: #e63946; }

.toggle-field { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
.toggle-field input[type="checkbox"] { width: 16px; height: 16px; accent-color: #4361ee; cursor: pointer; }
.toggle-field label { font-size: 0.85rem; color: #495057; cursor: pointer; font-weight: 400; }

.global-error { background: #fff3f3; border: 1px solid #f8d7da; color: #e63946; padding: 12px 16px; border-radius: 8px; font-size: 0.85rem; }

.form-actions { display: flex; gap: 12px; justify-content: flex-end; }
.btn-cancel { background: #f8f9fa; border: 1px solid #dee2e6; color: #495057; padding: 11px 22px; border-radius: 8px; text-decoration: none; font-size: 0.88rem; font-weight: 500; display: flex; align-items: center; }
.btn-submit { background: #4361ee; color: white; border: none; padding: 11px 24px; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-submit:hover:not(:disabled) { background: #3451d1; }
.btn-submit:disabled { background: #adb5bd; cursor: not-allowed; }
</style>
