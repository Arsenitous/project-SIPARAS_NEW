<template>
  <div class="login-page">
    <!-- Background decoration -->
    <div class="login-bg">
      <div class="login-bg-circle login-bg-circle--1"></div>
      <div class="login-bg-circle login-bg-circle--2"></div>
      <div class="login-bg-circle login-bg-circle--3"></div>
    </div>

    <div class="login-container">
      <!-- Left Panel — Branding -->
      <div class="login-panel login-panel--brand">
        <div class="brand-content">
          <img src="/logo-ygpkb.png" alt="Logo YGPKB" class="brand-logo" />
          <h1 class="brand-title">SIPARAS</h1>
          <p class="brand-subtitle">Sistem Informasi Prasarana & Sarana</p>
          <div class="brand-divider"></div>
          <p class="brand-org">Yayasan Gereja Protestan Kampung Bali</p>
          <div class="brand-features">
            <div class="brand-feature">
              <span class="brand-feature-icon">📋</span>
              <span>Manajemen Tiket Terpadu</span>
            </div>
            <div class="brand-feature">
              <span class="brand-feature-icon">✅</span>
              <span>Alur Approval 4 Jalur</span>
            </div>
            <div class="brand-feature">
              <span class="brand-feature-icon">📄</span>
              <span>Penerbitan PO & BAST Digital</span>
            </div>
            <div class="brand-feature">
              <span class="brand-feature-icon">📊</span>
              <span>Laporan Eksekutif Real-Time</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel — Form -->
      <div class="login-panel login-panel--form">
        <div class="login-form-card">
          <!-- Mobile logo -->
          <div class="login-mobile-brand">
            <img src="/logo-ygpkb.png" alt="YGPKB" class="login-mobile-logo" />
            <div>
              <h2 class="login-mobile-title">SIPARAS</h2>
              <p class="login-mobile-sub">YGPKB</p>
            </div>
          </div>

          <div class="login-form-header">
            <h2 class="login-form-title">Selamat Datang 👋</h2>
            <p class="login-form-sub">Masuk ke akun SIPARAS Anda</p>
          </div>

          <!-- Session Expired Alert -->
          <div v-if="sessionExpired" class="login-alert login-alert--warning" style="margin-bottom: 1.25rem;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Sesi Anda telah berakhir. Silakan masuk kembali.
          </div>

          <form @submit.prevent="handleLogin" class="login-form" novalidate>
            <!-- Username -->
            <div class="form-group">
              <label for="login-username" class="form-label">Username atau Email</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input
                  id="login-username"
                  v-model="form.username"
                  type="text"
                  class="form-input input-with-icon"
                  placeholder="Masukkan username atau email"
                  autocomplete="username"
                  required
                  :disabled="isLoading"
                />
              </div>
              <p v-if="errors.username" class="field-error">{{ errors.username }}</p>
            </div>

            <!-- Password -->
            <div class="form-group">
              <label for="login-password" class="form-label">Password</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </span>
                <input
                  id="login-password"
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-input input-with-icon input-with-icon-right"
                  placeholder="Masukkan password"
                  autocomplete="current-password"
                  required
                  :disabled="isLoading"
                />
                <!-- Eye Toggle -->
                <button
                  type="button"
                  class="input-eye-toggle"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'"
                  :disabled="isLoading"
                >
                  <!-- Eye Open -->
                  <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <!-- Eye Closed -->
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <p v-if="errors.password" class="field-error">{{ errors.password }}</p>
            </div>

            <!-- Error alert -->
            <div v-if="errorMsg" class="login-alert login-alert--error">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
              {{ errorMsg }}
            </div>

            <!-- Submit -->
            <button type="submit" class="login-btn" :disabled="isLoading">
              <span v-if="isLoading" class="login-spinner"></span>
              <span>{{ isLoading ? 'Sedang Masuk...' : 'Masuk ke SIPARAS' }}</span>
            </button>
          </form>

          <p class="login-footer-note">
            Butuh bantuan? Hubungi Admin Sarpras YGPKB
          </p>
        </div>
      </div>
    </div>

    <!-- Global Toast -->
    <ToastNotification />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import ToastNotification from '@/components/ToastNotification.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const toast = useToast();

const form = ref({ username: '', password: '' });
const isLoading = ref(false);
const showPassword = ref(false);
const errorMsg = ref('');
const errors = ref({});

const sessionExpired = computed(() => route.query.session === 'expired');

const handleLogin = async () => {
  errorMsg.value = '';
  errors.value = {};

  if (!form.value.username.trim()) {
    errors.value.username = 'Username atau email wajib diisi.';
    return;
  }
  if (!form.value.password) {
    errors.value.password = 'Password wajib diisi.';
    return;
  }

  isLoading.value = true;
  try {
    const payload = form.value.username.includes('@')
      ? { email: form.value.username.trim(), password: form.value.password }
      : { username: form.value.username.trim(), password: form.value.password };

    await authStore.login(payload);
    toast.success(`Selamat datang, ${authStore.user?.name}!`, 'Login Berhasil');
    const redirect = route.query.redirect || '/dashboard';
    router.push(redirect);
  } catch (error) {
    const msg =
      error?.message ||
      error?.errors?.login?.[0] ||
      error?.response?.data?.message ||
      'Terjadi kesalahan. Periksa koneksi atau hubungi admin.';
    errorMsg.value = msg;
    toast.error(msg, 'Login Gagal');
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* ── PAGE ── */
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: stretch;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #0a1628 0%, #1a3a6b 50%, #0f2347 100%);
}

/* ── BG DECORATION ── */
.login-bg { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
.login-bg-circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(200, 168, 75, 0.06);
  animation: float 8s ease-in-out infinite;
}
.login-bg-circle--1 { width: 500px; height: 500px; top: -200px; left: -100px; animation-delay: 0s; }
.login-bg-circle--2 { width: 350px; height: 350px; bottom: -100px; right: 30%; animation-delay: -3s; }
.login-bg-circle--3 { width: 250px; height: 250px; top: 30%; right: -80px; animation-delay: -5s; }
@keyframes float {
  0%, 100% { transform: translateY(0) scale(1); }
  50%       { transform: translateY(-20px) scale(1.03); }
}

/* ── CONTAINER ── */
.login-container {
  position: relative;
  z-index: 1;
  display: flex;
  width: 100%;
  max-width: 1000px;
  margin: auto;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 32px 80px rgba(0,0,0,0.5);
  min-height: 600px;
}

/* ── PANELS ── */
.login-panel { flex: 1; }
.login-panel--brand {
  background: linear-gradient(160deg, rgba(26,58,107,0.9) 0%, rgba(15,35,71,0.95) 100%);
  backdrop-filter: blur(10px);
  padding: 3rem 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 1px solid rgba(255,255,255,0.08);
}
.login-panel--form {
  background: #ffffff;
  padding: 3rem 2.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ── BRAND PANEL ── */
.brand-content { text-align: center; color: #fff; }
.brand-logo { width: 150px; height: 100px; object-fit: contain; margin: 0 auto 1.25rem; filter: brightness(0) invert(1); }
.brand-title { font-size: 2.25rem; font-weight: 800; letter-spacing: 0.12em; color: #fff; }
.brand-subtitle { font-size: 0.9rem; color: rgba(255,255,255,0.65); margin-top: 0.25rem; }
.brand-divider { width: 50px; height: 3px; background: var(--color-accent, #c8a84b); margin: 1.25rem auto; border-radius: 2px; }
.brand-org { font-size: 0.875rem; color: var(--color-accent, #c8a84b); font-weight: 600; }
.brand-features { margin-top: 2rem; display: flex; flex-direction: column; gap: 0.875rem; text-align: left; }
.brand-feature {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: rgba(255,255,255,0.8);
}
.brand-feature-icon { font-size: 1.25rem; width: 2rem; text-align: center; }

/* ── FORM CARD ── */
.login-form-card { width: 100%; max-width: 380px; }
.login-mobile-brand {
  display: none;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}
.login-mobile-logo { width: 40px; height: 40px; object-fit: contain; }
.login-mobile-title { font-size: 1.25rem; font-weight: 800; color: var(--color-primary, #1a3a6b); letter-spacing: 0.08em; }
.login-mobile-sub { font-size: 0.75rem; color: #64748b; }

.login-form-header { margin-bottom: 1.75rem; }
.login-form-title { font-size: 1.625rem; font-weight: 800; color: #0f172a; }
.login-form-sub { font-size: 0.9rem; color: #64748b; margin-top: 0.25rem; }

/* ── FORM ── */
.login-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.375rem; }
.form-label { font-size: 0.8rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; }

.input-wrapper { position: relative; }
.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  display: flex;
  align-items: center;
}
.input-icon svg { width: 16px; height: 16px; }

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  font-family: inherit;
  font-size: 0.9375rem;
  color: #0f172a;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  outline: none;
  transition: all 0.2s;
}
.form-input:focus { border-color: #1a3a6b; box-shadow: 0 0 0 3px rgba(26,58,107,0.1); background: #fff; }
.form-input::placeholder { color: #94a3b8; }
.input-with-icon { padding-left: 2.75rem; }
.input-with-icon-right { padding-right: 2.75rem; }

.input-eye-toggle {
  position: absolute;
  right: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  padding: 0.25rem;
  border-radius: 4px;
  transition: color 0.2s;
}
.input-eye-toggle:hover { color: #1a3a6b; }
.input-eye-toggle:disabled { opacity: 0.4; cursor: not-allowed; }
.input-eye-toggle svg { width: 16px; height: 16px; }

.field-error { font-size: 0.78rem; color: #dc2626; margin-top: 0.25rem; }

/* ── ALERTS ── */
.login-alert {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
}
.login-alert svg { width: 16px; height: 16px; flex-shrink: 0; }
.login-alert--error  { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; }
.login-alert--warning{ background: #fffbeb; border: 1px solid #fcd34d; color: #b45309; }

/* ── SUBMIT BUTTON ── */
.login-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(135deg, #1a3a6b, #2a5298);
  color: #fff;
  font-family: inherit;
  font-size: 1rem;
  font-weight: 700;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all 0.25s;
  margin-top: 0.5rem;
  letter-spacing: 0.02em;
}
.login-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #0f2347, #1a3a6b);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(26,58,107,0.35);
}
.login-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.login-spinner {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.login-footer-note { margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #94a3b8; }

/* ── RESPONSIVE ── */
@media (max-width: 720px) {
  .login-container { flex-direction: column; border-radius: 0; min-height: 100vh; }
  .login-panel--brand { display: none; }
  .login-panel--form { padding: 2rem 1.5rem; align-items: flex-start; padding-top: 3rem; }
  .login-mobile-brand { display: flex; }
}
</style>
