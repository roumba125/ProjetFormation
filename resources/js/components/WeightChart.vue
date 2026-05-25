<template>
  <div class="chart-wrapper">
    <svg
      :viewBox="`0 0 ${W} ${H}`"
      class="weight-svg"
      role="img"
      :aria-label="`Graphique évolution du poids sur ${records.length} mesures`"
    >
      <!-- Grille horizontale -->
      <g class="grid">
        <line
          v-for="(tick, i) in yTicks"
          :key="i"
          :x1="padding.left"
          :y1="yScale(tick)"
          :x2="W - padding.right"
          :y2="yScale(tick)"
          stroke="#e8d5b0"
          stroke-width="1"
          stroke-dasharray="4,4"
        />
        <!-- Labels Y -->
        <text
          v-for="(tick, i) in yTicks"
          :key="'yl' + i"
          :x="padding.left - 8"
          :y="yScale(tick) + 4"
          text-anchor="end"
          font-size="10"
          fill="#a08070"
        >{{ tick }} kg</text>
      </g>

      <!-- Zone sous la courbe (gradient) -->
      <defs>
        <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%"   stop-color="#8b5e3c" stop-opacity="0.2" />
          <stop offset="100%" stop-color="#8b5e3c" stop-opacity="0" />
        </linearGradient>
      </defs>
      <path :d="areaPath" fill="url(#areaGrad)" />

      <!-- Courbe principale -->
      <path
        :d="linePath"
        fill="none"
        stroke="#8b5e3c"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      />

      <!-- Points -->
      <g v-for="(r, i) in records" :key="i">
        <circle
          :cx="xScale(i)"
          :cy="yScale(r.weight)"
          r="5"
          fill="#8b5e3c"
          stroke="white"
          stroke-width="2"
        />
        <!-- Label poids au-dessus du point -->
        <text
          :x="xScale(i)"
          :y="yScale(r.weight) - 10"
          text-anchor="middle"
          font-size="10"
          font-weight="700"
          fill="#8b5e3c"
        >{{ r.weight }} kg</text>
        <!-- Date en bas -->
        <text
          :x="xScale(i)"
          :y="H - padding.bottom + 14"
          text-anchor="middle"
          font-size="9"
          fill="#a08070"
        >{{ shortDate(r.recorded_at) }}</text>
      </g>

      <!-- Axe X -->
      <line
        :x1="padding.left"
        :y1="H - padding.bottom"
        :x2="W - padding.right"
        :y2="H - padding.bottom"
        stroke="#e8d5b0"
        stroke-width="1.5"
      />
    </svg>

    <!-- Légende stats -->
    <div class="chart-stats">
      <div class="stat-chip">
        <span class="chip-icon">📈</span>
        <div>
          <span class="chip-val">+{{ totalGain }} kg</span>
          <span class="chip-lbl">Gain total</span>
        </div>
      </div>
      <div class="stat-chip">
        <span class="chip-icon">⚡</span>
        <div>
          <span class="chip-val">{{ avgGMQ }} g/j</span>
          <span class="chip-lbl">GMQ moyen</span>
        </div>
      </div>
      <div class="stat-chip">
        <span class="chip-icon">🔢</span>
        <div>
          <span class="chip-val">{{ records.length }}</span>
          <span class="chip-lbl">Pesées</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  records: { type: Array, default: () => [] },
})

const W = 560
const H = 200
const padding = { top: 30, right: 20, bottom: 30, left: 52 }

const weights  = computed(() => props.records.map(r => r.weight))
const minW     = computed(() => Math.floor(Math.min(...weights.value) - 5))
const maxW     = computed(() => Math.ceil(Math.max(...weights.value) + 5))

function xScale(i) {
  const n = props.records.length
  if (n <= 1) return (W - padding.left - padding.right) / 2 + padding.left
  return padding.left + (i / (n - 1)) * (W - padding.left - padding.right)
}

function yScale(val) {
  const range = maxW.value - minW.value || 10
  return H - padding.bottom - ((val - minW.value) / range) * (H - padding.top - padding.bottom)
}

const yTicks = computed(() => {
  const step = Math.ceil((maxW.value - minW.value) / 4)
  const ticks = []
  for (let v = minW.value; v <= maxW.value; v += step) ticks.push(Math.round(v))
  return ticks
})

const linePath = computed(() => {
  if (!props.records.length) return ''
  return props.records
    .map((r, i) => `${i === 0 ? 'M' : 'L'}${xScale(i)},${yScale(r.weight)}`)
    .join(' ')
})

const areaPath = computed(() => {
  if (!props.records.length) return ''
  const n    = props.records.length
  const base = H - padding.bottom
  const line = linePath.value
  return `${line} L${xScale(n - 1)},${base} L${xScale(0)},${base} Z`
})

const totalGain = computed(() => {
  if (weights.value.length < 2) return 0
  return (weights.value[weights.value.length - 1] - weights.value[0]).toFixed(1)
})

const avgGMQ = computed(() => {
  const gains = props.records.map(r => r.daily_gain).filter(Boolean)
  if (!gains.length) return '—'
  return Math.round(gains.reduce((a, b) => a + b, 0) / gains.length)
})

function shortDate(dateStr) {
  if (!dateStr) return ''
  const parts = dateStr.split('/')
  if (parts.length === 3) return `${parts[0]}/${parts[1]}`
  const d = new Date(dateStr)
  return `${d.getDate()}/${d.getMonth() + 1}`
}
</script>

<style scoped>
.chart-wrapper { background: #fdf8f2; border-radius: 12px; padding: 16px; }
.weight-svg { width: 100%; height: auto; display: block; }
.chart-stats {
  display: flex;
  gap: 16px;
  margin-top: 14px;
  flex-wrap: wrap;
}
.stat-chip { display: flex; align-items: center; gap: 8px; background: white; padding: 8px 14px; border-radius: 8px; border: 1px solid rgba(139,94,60,0.12); }
.chip-icon { font-size: 1.1rem; }
.chip-val  { display: block; font-weight: 700; font-size: 0.88rem; color: #1a120b; }
.chip-lbl  { display: block; font-size: 0.65rem; color: #8b6b55; }
</style>
