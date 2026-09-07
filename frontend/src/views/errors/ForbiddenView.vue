<template>
  <div class="forbidden-page">
    <div class="forbidden-card">
      <div class="forbidden-icon">🔒</div>
      <h1 class="forbidden-title">403 — Akses Ditolak</h1>
      <p class="forbidden-message">
        Maaf, akun Anda tidak memiliki izin untuk mengakses halaman ini.
        Silakan kembali ke Dashboard atau hubungi administrator jika Anda merasa ini adalah kesalahan.
      </p>
      <div class="forbidden-meta">
        <span class="badge badge-neutral">Role: {{ formatRole(authStore.user?.role) }}</span>
      </div>
      <div class="forbidden-actions">
        <button class="btn btn-primary" @click="$router.push('/dashboard')">
          ← Kembali ke Dashboard
        </button>
        <button class="btn btn-outline" @click="$router.go(-1)">
          Halaman Sebelumnya
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

const formatRole = (role) => {
  const map = {
    kepsek: 'Kepala Sekolah',
    dept_ygpkb: 'Departemen YGPKB',
    asp_wilayah: 'ASP Wilayah',
    kd_sarpras: 'KD Sarpras',
    admin_sarpras: 'Admin Sarpras',
    koordinator_ski: 'Koordinator SKI',
    koordinator_utama: 'Koordinator Utama',
    superadmin: 'Super Admin',
  };
  return map[role] || role || '—';
};
</script>

<style scoped>
.forbidden-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-page);
  padding: 1.5rem;
}

.forbidden-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: 20px;
  box-shadow: var(--shadow-lg);
  padding: 3rem 2.5rem;
  max-width: 480px;
  width: 100%;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.25rem;
}

.forbidden-icon {
  font-size: 4rem;
  line-height: 1;
  filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
}

.forbidden-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--color-error, #dc2626);
  margin: 0;
  letter-spacing: -0.02em;
}

.forbidden-message {
  font-size: 0.9rem;
  color: var(--text-secondary);
  line-height: 1.6;
  margin: 0;
  max-width: 360px;
}

.forbidden-meta {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.badge-neutral {
  background: rgba(100,116,139,0.1);
  color: #64748b;
  border-color: rgba(100,116,139,0.2);
}

.forbidden-actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 0.5rem;
}
</style>
