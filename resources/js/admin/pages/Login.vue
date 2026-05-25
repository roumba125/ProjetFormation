<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <span class="logo-icon">🐑</span>
        <h1>BergerPro <span>Admin</span></h1>
        <p>Espace d'administration</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label>Email</label>
          <input
            v-model="form.email"
            type="email"
            placeholder="admin@bergerpro.com"
            required
            :disabled="loading"
          />
        </div>

        <div class="form-group">
          <label>Mot de passe</label>
          <div class="password-input">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              :disabled="loading"
            />
            <button type="button" @click="showPassword = !showPassword" class="btn-eye">
              {{ showPassword ? '🙈' : '👁️' }}
            </button>
          </div>
        </div>

        <div class="error-banner" v-if="error">
          ⚠️ {{ error }}
        </div>

        <button type="submit" class="btn-login" :disabled="loading">
          <span v-if="loading">⏳ Connexion...</span>
          <span v-else>→ Se connecter</span>
        </button>
      </form>

      <p class="login-back">
        <a href="http://bergerpro.test">← Retour au site</a>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/admin/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const form = ref({ email: '', password: '' })
const error       = ref(null)
const loading     = ref(false)
const showPassword = ref(false)

async function handleLogin() {
  loading.value = true
  error.value   = null

  const result = await auth.login(form.value.email, form.value.password)

  if (result.success) {
    router.push({ name: 'admin.dashboard' })
  } else {
    error.value = result.message
  }

  loading.value = false
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.login-card {
  background: white;
  border-radius: 16px;
  padding: 48px 40px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 4px 40px rgba(0, 0, 0, 0.08);
  border: 1px solid #e9ecef;
}

.login-logo {
  text-align: center;
  margin-bottom: 36px;
}
.logo-icon { font-size: 3rem; display: block; margin-bottom: 12px; }
.login-logo h1 {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1a1a2e;
  margin-bottom: 4px;
}
.login-logo h1 span { color: #4361ee; }
.login-logo p { font-size: 0.85rem; color: #6c757d; }

.login-form { display: flex; flex-direction: column; gap: 20px; }

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group label { font-size: 0.8rem; font-weight: 600; color: #495057; }
.form-group input {
  padding: 11px 14px;
  border: 1.5px solid #dee2e6;
  border-radius: 8px;
  font-size: 0.9rem;
  font-family: inherit;
  color: #1a1a2e;
  transition: border-color 0.2s;
  outline: none;
  width: 100%;
  box-sizing: border-box;
}
.form-group input:focus { border-color: #4361ee; }
.form-group input:disabled { background: #f8f9fa; }

.password-input { position: relative; }
.password-input input { padding-right: 44px; }
.btn-eye {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  padding: 0;
}

.error-banner {
  background: #fff3cd;
  border: 1px solid #ffc107;
  color: #856404;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 0.82rem;
}

.btn-login {
  background: #4361ee;
  color: white;
  border: none;
  padding: 13px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
  margin-top: 4px;
}
.btn-login:hover:not(:disabled) { background: #3451d1; }
.btn-login:disabled { background: #adb5bd; cursor: not-allowed; }

.login-back {
  text-align: center;
  margin-top: 24px;
  font-size: 0.82rem;
}
.login-back a { color: #6c757d; text-decoration: none; }
.login-back a:hover { color: #4361ee; }
</style>
