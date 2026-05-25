<template>
  <div>
    <!-- HERO -->
    <section class="hero">
      <div class="hero-left">
        <div class="hero-badge">🏆 Élevage certifié · Suivi vétérinaire</div>
        <h1>Des moutons d'<em>élite</em>,<br>livrés avec leur fiche</h1>
        <p class="hero-desc">
          Chaque animal est suivi de près : poids, vaccinations,
          alimentation et photos. Achetez en toute confiance.
        </p>
        <div class="hero-actions">
          <RouterLink to="/catalogue" class="btn-primary">Voir le catalogue</RouterLink>
          <RouterLink to="/suivi" class="btn-secondary">Suivre ma commande</RouterLink>
        </div>
        <div class="hero-stats">
          <div class="stat">
            <span class="num">{{ stats.available_count }}</span>
            <span class="lbl">Disponibles</span>
          </div>
          <div class="stat">
            <span class="num">{{ stats.breeds_count }}</span>
            <span class="lbl">Races</span>
          </div>
          <div class="stat">
            <span class="num">{{ stats.sold_count }}+</span>
            <span class="lbl">Vendus</span>
          </div>
        </div>
      </div>
      <div class="hero-right">
        <div class="hero-emoji">🐑</div>
        <div class="hero-tag top">
          <span>💉</span>
          <div>
            <p>Vacciné &amp; suivi</p>
            <small>Carnet à jour</small>
          </div>
        </div>
        <div class="hero-tag bottom">
          <span>⚖️</span>
          <div>
            <p>Pesé &amp; certifié</p>
            <small>Mise à jour hebdo</small>
          </div>
        </div>
      </div>
    </section>

    <!-- PROCESSUS -->
    <section class="process">
      <div class="section-header">
        <span class="label">Comment ça marche</span>
        <h2>De la fiche à la livraison</h2>
      </div>
      <div class="steps">
        <div class="step" v-for="step in steps" :key="step.num">
          <div class="step-num">{{ step.num }}</div>
          <div class="step-icon">{{ step.icon }}</div>
          <h3>{{ step.title }}</h3>
          <p>{{ step.desc }}</p>
        </div>
      </div>
    </section>

    <!-- CTA CATALOGUE -->
    <section class="cta">
      <h2>Prêt à choisir votre mouton ?</h2>
      <p>Consultez notre catalogue complet avec fiches détaillées.</p>
      <RouterLink to="/catalogue" class="btn-primary large">Voir le catalogue →</RouterLink>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/lib/axios'

const stats = ref({ available_count: '...', breeds_count: '...', sold_count: '...' })

const steps = [
  { num: '1', icon: '🔍', title: 'Consultez la fiche',  desc: 'Âge, poids, race, photos et historique de vaccination complets.' },
  { num: '2', icon: '📋', title: 'Passez commande',     desc: 'Choisissez votre animal et confirmez votre commande en ligne.' },
  { num: '3', icon: '💳', title: 'Paiement sécurisé',   desc: 'Orange Money, Wave ou virement bancaire selon votre choix.' },
  { num: '4', icon: '🚚', title: 'Livraison ou retrait', desc: 'Livraison à domicile ou retrait à l\'élevage selon votre préférence.' },
]

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/v1/stats')
    stats.value = data
  } catch (e) {
    stats.value = { available_count: '120+', breeds_count: '4', sold_count: '200+' }
  }
})
</script>

<style scoped>
/* HERO */
.hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
  padding-top: 72px;
  background: #fdf8f2;
}
.hero-left {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 80px 60px 80px 80px;
}
.hero-badge {
  display: inline-block;
  background: rgba(74,103,65,0.1);
  color: #4a6741;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 28px;
  width: fit-content;
  border: 1px solid rgba(74,103,65,0.2);
}
h1 {
  font-family: 'Playfair Display', serif;
  font-size: 3.5rem;
  font-weight: 900;
  line-height: 1.1;
  color: #1a120b;
  margin-bottom: 24px;
}
h1 em { font-style: normal; color: #8b5e3c; }
.hero-desc {
  font-size: 1rem;
  line-height: 1.7;
  color: #6b4c35;
  margin-bottom: 36px;
  max-width: 420px;
}
.hero-actions { display: flex; gap: 14px; margin-bottom: 48px; flex-wrap: wrap; }
.btn-primary {
  background: #8b5e3c;
  color: white;
  padding: 13px 26px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: background 0.2s;
  font-size: 0.95rem;
}
.btn-primary:hover { background: #1a120b; }
.btn-primary.large { padding: 16px 36px; font-size: 1rem; }
.btn-secondary {
  border: 2px solid #8b5e3c;
  color: #8b5e3c;
  padding: 13px 26px;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  transition: all 0.2s;
  font-size: 0.95rem;
}
.btn-secondary:hover { background: #8b5e3c; color: white; }

.hero-stats {
  display: flex;
  gap: 40px;
  padding-top: 36px;
  border-top: 1px solid rgba(139,94,60,0.15);
}
.stat .num {
  display: block;
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 900;
  color: #8b5e3c;
  line-height: 1;
}
.stat .lbl { font-size: 0.78rem; color: #8b6b55; margin-top: 4px; display: block; }

.hero-right {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #f5ede0, #e8d5b0);
  overflow: hidden;
}
.hero-emoji {
  font-size: 160px;
  animation: float 4s ease-in-out infinite;
  filter: drop-shadow(0 20px 40px rgba(139,94,60,0.2));
}
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-18px); }
}
.hero-tag {
  position: absolute;
  background: white;
  border-radius: 12px;
  padding: 12px 16px;
  box-shadow: 0 8px 30px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.3rem;
}
.hero-tag.top    { top: 100px; right: 40px; }
.hero-tag.bottom { bottom: 100px; left: 40px; }
.hero-tag p     { font-size: 0.8rem; font-weight: 600; color: #1a120b; margin: 0; }
.hero-tag small { font-size: 0.7rem; color: #8b6b55; }

/* PROCESS */
.process {
  padding: 100px 80px;
  background: #1a120b;
  color: white;
}
.section-header { text-align: center; margin-bottom: 60px; }
.section-header .label {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: #d4a574;
  display: block;
  margin-bottom: 12px;
}
.section-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2.2rem;
  font-weight: 700;
  color: white;
}
.steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 40px;
}
.step { text-align: center; }
.step-num {
  width: 52px; height: 52px;
  background: #8b5e3c;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
  margin: 0 auto 16px;
}
.step-icon { font-size: 1.5rem; margin-bottom: 10px; }
.step h3 { font-family: 'Playfair Display', serif; font-size: 1rem; color: #d4a574; margin-bottom: 8px; }
.step p  { font-size: 0.8rem; color: #c8b8a8; line-height: 1.6; }

/* CTA */
.cta {
  padding: 100px 40px;
  text-align: center;
  background: #f5ede0;
}
.cta h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: #1a120b;
  margin-bottom: 12px;
}
.cta p { color: #6b4c35; margin-bottom: 32px; font-size: 1rem; }

/* Responsive */
@media (max-width: 900px) {
  .hero { grid-template-columns: 1fr; }
  .hero-right { height: 280px; }
  .hero-left { padding: 60px 24px 40px; }
  h1 { font-size: 2.2rem; }
  .steps { grid-template-columns: repeat(2, 1fr); }
  .process { padding: 60px 24px; }
}
</style>
