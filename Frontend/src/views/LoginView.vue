<template>
  <div class="login-wrapper">

    <!-- Background decorative shapes -->
    <div class="bg-circle bg-circle-1"></div>
    <div class="bg-circle bg-circle-2"></div>

    <div class="login-card">

      <!-- Logo -->
      <div class="logo-area">
        <img src="/logo.png" alt="RM Trans" class="logo-img" />
      </div>

      <div class="card-divider"></div>

      <p class="card-subtitle">Silakan masuk untuk melanjutkan</p>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="login-form">

        <!-- Username -->
        <div class="input-group">
          <label for="username">Username</label>
          <div class="input-wrap" :class="{ focused: focusedField === 'username' }">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </span>
            <input id="username" v-model="form.username" type="text" placeholder="Masukkan username" :disabled="loading"
              @focus="focusedField = 'username'" @blur="focusedField = ''" autocomplete="username" />
          </div>
        </div>

        <!-- Password -->
        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-wrap" :class="{ focused: focusedField === 'password' }">
            <span class="input-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
            </span>
            <input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
              placeholder="Masukkan password" :disabled="loading" @focus="focusedField = 'password'"
              @blur="focusedField = ''" autocomplete="current-password" />
            <span class="toggle-password" @click="showPassword = !showPassword">
              <!-- Eye -->
              <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                <circle cx="12" cy="12" r="3" />
              </svg>
              <!-- Eye Off -->
              <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                  d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24" />
                <line x1="1" y1="1" x2="23" y2="23" />
              </svg>
            </span>
          </div>
        </div>

        <!-- Error -->
        <transition name="fade">
          <div v-if="error" class="error-msg">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10" />
              <line x1="12" y1="8" x2="12" y2="12" />
              <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            {{ error }}
          </div>
        </transition>

        <!-- Submit -->
        <button type="submit" class="btn-login" :disabled="loading">
          <span v-if="!loading">Masuk</span>
          <span v-else class="btn-loading">
            <span class="spinner"></span>
            Tunggu...
          </span>
        </button>

      </form>

      <p class="footer-text">© 2026 RM Trans · All rights reserved</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginView',
  data() {
    return {
      form: { username: '', password: '' },
      showPassword: false,
      focusedField: '',
    }
  },

  computed: {
    loading() {
      return this.$store.state.auth.loading
    },
    error() {
      return this.$store.state.auth.error
    }
  },

  methods: {
    async handleLogin() {
      this.$store.commit('auth/SET_ERROR', null)

      if (!this.form.username || !this.form.password) {
        this.$store.commit('auth/SET_ERROR', 'Username dan password tidak boleh kosong.')
        return
      }

      const success = await this.$store.dispatch('auth/login', this.form)

      if (success) {
        this.$router.push('/dashboard')
      }
    }
  }
}
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');
</style>

<style scoped>
/* ===== WRAPPER ===== */
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #3E3D90;
  padding: 1.5rem;
  position: relative;
  overflow: hidden;
  font-family: 'Poppins', sans-serif;
}

/* ===== BACKGROUND CIRCLES ===== */
.bg-circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.07;
  background: #ffffff;
  pointer-events: none;
}

.bg-circle-1 {
  width: 420px;
  height: 420px;
  top: -120px;
  left: -100px;
}

.bg-circle-2 {
  width: 300px;
  height: 300px;
  bottom: -80px;
  right: -60px;
}

/* ===== CARD ===== */
.login-card {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 440px;
  background: #ffffff;
  border-radius: 20px;
  padding: 2.8rem 2.4rem 2rem;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.22);
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ===== LOGO ===== */
.logo-area {
  width: 100%;
  text-align: center;
  margin-bottom: 1.4rem;
}

.logo-img {
  max-width: 200px;
  height: auto;
  object-fit: contain;
}

/* ===== DIVIDER ===== */
.card-divider {
  width: 48px;
  height: 3px;
  background: #3E3D90;
  border-radius: 2px;
  margin-bottom: 0.8rem;
}

/* ===== SUBTITLE ===== */
.card-subtitle {
  font-size: 13px;
  color: #888;
  margin-bottom: 1.8rem;
  letter-spacing: 0.2px;
}

/* ===== FORM ===== */
.login-form {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

/* ===== INPUT GROUP ===== */
.input-group {
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.input-group label {
  font-size: 12px;
  font-weight: 600;
  color: #3E3D90;
  letter-spacing: 0.6px;
  text-transform: uppercase;
}

/* ===== INPUT WRAP ===== */
.input-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #f5f5fb;
  border: 1.5px solid #e0e0ef;
  border-radius: 10px;
  padding: 0 14px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.input-wrap.focused {
  border-color: #3E3D90;
  box-shadow: 0 0 0 3px rgba(62, 61, 144, 0.12);
  background: #ffffff;
}

.input-icon {
  display: flex;
  align-items: center;
  color: #b0b0c8;
  flex-shrink: 0;
}

.input-wrap input {
  flex: 1;
  border: none;
  background: transparent;
  outline: none;
  font-family: 'Poppins', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: #303030;
  padding: 13px 0;
}

.input-wrap input::placeholder {
  color: #c0c0d0;
}

.input-wrap input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ===== TOGGLE PASSWORD ===== */
.toggle-password {
  display: flex;
  align-items: center;
  color: #b0b0c8;
  cursor: pointer;
  flex-shrink: 0;
  transition: color 0.15s;
}

.toggle-password:hover {
  color: #3E3D90;
}

/* ===== ERROR ===== */
.error-msg {
  display: flex;
  align-items: center;
  gap: 7px;
  background: #fff0f0;
  border: 1px solid #ffd0d0;
  border-radius: 8px;
  padding: 9px 13px;
  font-size: 12.5px;
  color: #e74c3c;
  font-weight: 500;
}

/* ===== BUTTON ===== */
.btn-login {
  width: 100%;
  padding: 13px;
  background: #3E3D90;
  border: none;
  border-radius: 10px;
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  font-weight: 600;
  color: #ffffff;
  cursor: pointer;
  margin-top: 0.3rem;
  letter-spacing: 0.4px;
  transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease;
  box-shadow: 0 4px 16px rgba(62, 61, 144, 0.3);
}

.btn-login:hover:not(:disabled) {
  background: #4c4bb0;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(62, 61, 144, 0.4);
}

.btn-login:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 4px 16px rgba(62, 61, 144, 0.3);
}

.btn-login:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

/* ===== LOADING SPINNER ===== */
.btn-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.spinner {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* ===== FADE TRANSITION ===== */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}

/* ===== FOOTER ===== */
.footer-text {
  font-size: 11px;
  color: #bbb;
  margin-top: 1.5rem;
  text-align: center;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 500px) {
  .login-card {
    padding: 2rem 1.4rem 1.6rem;
    border-radius: 16px;
  }

  .logo-img {
    max-width: 160px;
  }
}

@media (max-width: 360px) {
  .login-wrapper {
    padding: 1rem;
  }

  .login-card {
    padding: 1.5rem 1.1rem 1.4rem;
  }

  .card-subtitle {
    font-size: 12px;
  }

  .btn-login {
    padding: 11px;
    font-size: 14px;
  }
}
</style>