<template>
  <article
    class="sheep-card"
    :class="[`status-${sheep.status}`, { featured: sheep.is_featured }]"
    @click="$emit('open', sheep)"
  >
    <!-- Photo principale -->
    <div class="card-image">
      <img
        v-if="sheep.primary_photo_url"
        :src="sheep.primary_photo_url"
        :alt="sheep.name"
        loading="lazy"
      />
      <div v-else class="card-image-placeholder">🐑</div>

      <!-- Badge statut -->
      <span class="badge" :class="`badge-${sheep.status}`">
        {{ sheep.status_label }}
      </span>

      <!-- Bouton favori -->
      <button
        class="btn-fav"
        :class="{ active: isFavorite }"
        @click.stop="toggleFavorite"
        :aria-label="isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris'"
      >
        {{ isFavorite ? '❤️' : '🤍' }}
      </button>

      <!-- Badge featured -->
      <span v-if="sheep.is_featured" class="badge-featured">⭐ Sélection</span>
    </div>

    <!-- Corps de la carte -->
    <div class="card-body">
      <p class="card-breed">{{ sheep.breed?.name }}</p>
      <h3 class="card-name">{{ sheep.name }}</h3>
      <p class="card-id">{{ sheep.reference }} · {{ sheep.gender_label }}</p>

      <!-- Specs principales -->
      <div class="card-specs">
        <div class="spec">
          <span class="spec-val">{{ sheep.age_formatted }}</span>
          <span class="spec-key">Âge</span>
        </div>
        <div class="spec">
          <span class="spec-val">{{ sheep.current_weight }} kg</span>
          <span class="spec-key">Poids</span>
        </div>
        <div class="spec">
          <span class="spec-val">{{ sheep.health_label }}</span>
          <span class="spec-key">État</span>
        </div>
      </div>

      <!-- Vaccins -->
      <div class="card-vaccins" v-if="vaccinNames.length">
        <span
          v-for="name in vaccinNames"
          :key="name"
          class="vaccin-tag"
        >✅ {{ name }}</span>
      </div>

      <!-- Footer prix + action -->
      <div class="card-footer">
        <div class="card-price">
          {{ sheep.price_formatted }}
        </div>
        <button
          v-if="sheep.status === 'available'"
          class="btn-add"
          :class="{ 'in-cart': inCart }"
          @click.stop="handleCart"
        >
          {{ inCart ? '✓ Dans le panier' : 'Commander →' }}
        </button>
        <span v-else class="btn-disabled">{{ sheep.status_label }}</span>
      </div>
    </div>
  </article>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useCartStore } from '@/stores/cart'

const props = defineProps({
  sheep: { type: Object, required: true },
})
const emit = defineEmits(['open'])

const cart      = useCartStore()
const favorites = ref(JSON.parse(localStorage.getItem('favorites') || '[]'))

const inCart    = computed(() => cart.hasItem(props.sheep.id))
const isFavorite = computed(() => favorites.value.includes(props.sheep.id))

const vaccinNames = computed(() => {
  if (!props.sheep.vaccinations_summary?.names) return []
  return props.sheep.vaccinations_summary.names.slice(0, 3)
})

function handleCart() {
  if (inCart.value) {
    cart.removeSheep(props.sheep.id)
  } else {
    cart.addSheep(props.sheep)
  }
}

function toggleFavorite() {
  const id  = props.sheep.id
  const idx = favorites.value.indexOf(id)
  if (idx === -1) {
    favorites.value.push(id)
  } else {
    favorites.value.splice(idx, 1)
  }
  localStorage.setItem('favorites', JSON.stringify(favorites.value))
}
</script>

<style scoped>
.sheep-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid rgba(139, 94, 60, 0.1);
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.sheep-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 36px rgba(139, 94, 60, 0.15);
}
.sheep-card.featured {
  border-color: rgba(200, 137, 58, 0.3);
}

/* Image */
.card-image {
  position: relative;
  height: 210px;
  overflow: hidden;
  background: #f5ede0;
}
.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}
.sheep-card:hover .card-image img {
  transform: scale(1.04);
}
.card-image-placeholder {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 5rem;
}

/* Badges */
.badge {
  position: absolute;
  top: 12px;
  left: 12px;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.4px;
}
.badge-available { background: #d4edda; color: #1e5631; }
.badge-reserved  { background: #fff3cd; color: #7d5a00; }
.badge-sold      { background: #f8d7da; color: #721c24; }

.badge-featured {
  position: absolute;
  bottom: 10px;
  left: 12px;
  background: rgba(200, 137, 58, 0.9);
  color: white;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.68rem;
  font-weight: 600;
}

.btn-fav {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  border: none;
  background: white;
  border-radius: 50%;
  font-size: 0.9rem;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  transition: transform 0.2s;
}
.btn-fav:hover { transform: scale(1.15); }

/* Corps */
.card-body { padding: 18px; }
.card-breed { font-size: 0.7rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #8b5e3c; margin-bottom: 4px; }
.card-name  { font-family: 'Playfair Display', serif; font-size: 1.15rem; font-weight: 700; color: #1a120b; margin-bottom: 2px; }
.card-id    { font-size: 0.72rem; color: #a08070; margin-bottom: 14px; }

.card-specs {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
  margin-bottom: 12px;
}
.spec {
  background: #f5ede0;
  border-radius: 8px;
  padding: 8px 6px;
  text-align: center;
}
.spec-val { display: block; font-weight: 700; font-size: 0.85rem; color: #1a120b; }
.spec-key { font-size: 0.62rem; color: #8b6b55; }

.card-vaccins { display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 14px; }
.vaccin-tag {
  background: rgba(74, 103, 65, 0.1);
  color: #2d6a3f;
  padding: 3px 7px;
  border-radius: 4px;
  font-size: 0.65rem;
  font-weight: 500;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 14px;
  border-top: 1px solid rgba(139, 94, 60, 0.1);
}
.card-price { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: #8b5e3c; }

.btn-add {
  background: #8b5e3c;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-add:hover    { background: #1a120b; }
.btn-add.in-cart  { background: #4a6741; }
.btn-disabled     { font-size: 0.78rem; color: #a08070; font-style: italic; }
</style>
