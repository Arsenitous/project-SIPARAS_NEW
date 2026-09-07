<template>
  <div class="app-shell" :class="{ dark: isDark }">
    <!-- Sidebar -->
    <AppSidebar
      :pending-count="pendingCount"
      :is-dark="isDark"
      @toggle-dark="toggleDark"
      @toggle-collapse="sidebarCollapsed = $event"
    />

    <!-- Main Content Wrapper -->
    <div class="main-wrapper" :class="{ 'main-wrapper--collapsed': sidebarCollapsed }">
      <!-- Top Bar -->
      <header class="topbar">
        <div class="topbar-left">
          <h1 class="topbar-title">{{ pageTitle }}</h1>
        </div>
        <div class="topbar-right">
          <!-- Bell Notification -->
          <div style="position:relative;">
            <button
              class="topbar-icon-btn"
              @click.stop="showNotifPanel = !showNotifPanel"
              title="Notifikasi"
              id="notif-bell-btn"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
              <span v-if="pendingCount > 0" class="topbar-badge">{{ pendingCount }}</span>
            </button>

            <!-- Notification Panel Dropdown -->
            <transition name="notif-drop">
              <div v-if="showNotifPanel" class="notif-panel" @click.stop>
                <div class="notif-header">
                  <h3>Notifikasi</h3>
                  <button class="btn btn-ghost btn-sm" @click="showNotifPanel = false" style="font-size:1rem;line-height:1;">✕</button>
                </div>
                <div v-if="pendingCount === 0" class="notif-empty">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;opacity:0.3"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
                  <p>Tidak ada notifikasi baru</p>
                </div>
                <div v-else class="notif-item">
                  <div class="notif-dot"></div>
                  <div>
                    <p class="notif-text">Ada <strong>{{ pendingCount }}</strong> tiket menunggu tindakan Anda.</p>
                    <p class="notif-time">Baru saja</p>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <!-- User greeting -->
          <div class="topbar-user" v-if="authStore.user">
            <span class="topbar-user-name">{{ formatRole(authStore.user?.role) }}</span>
          </div>
        </div>
      </header>


      <!-- Page Content -->
      <main class="page-content">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- Footer -->
      <footer class="app-footer">
        <span>© 2026 Yayasan Gereja Protestan Kampung Bali (YGPKB)</span>
        <span class="footer-sep">·</span>
        <span>Sistem Informasi Prasarana & Sarana (SIPARAS)</span>
      </footer>
    </div>

    <!-- Global UI Layers -->
    <ToastNotification />
    <ConfirmModal />
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import AppSidebar from '@/components/AppSidebar.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';

const route = useRoute();
const authStore = useAuthStore();

// Dark mode
const isDark = ref(localStorage.getItem('siparas-dark') === 'true');
const toggleDark = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('siparas-dark', isDark.value);
};

// Sidebar state (sync with AppSidebar via storage)
const sidebarCollapsed = ref(false);

// Pending count (badge)
const pendingCount = ref(0);

// Notification panel
const showNotifPanel = ref(false);

// Page title from route meta or name
const pageTitle = computed(() => {
  const titles = {
    'dashboard': 'Dashboard',
    'ticket-create': 'Buat Tiket Baru',
    'ticket-detail': 'Detail Tiket',
    'asp-review-queue': 'Antrean Review ASP',
    'kd-approval-desk': 'Meja Approval KD',
    'po-management': 'Manajemen PO & BAST',
    'executive-reports': 'Laporan Eksekutif',
    'master-data': 'Master Data',
    'user-management': 'Manajemen Pengguna',
  };
  return titles[route.name] || 'SIPARAS';
});

const formatRole = (role) => {
  const map = {
    superadmin: 'Super Admin',
    kepsek: 'Kepala Sekolah',
    dept_ygpkb: 'Departemen YGPKB',
    asp_wilayah: 'ASP Wilayah',
    kd_sarpras: 'KD Sarpras',
    admin_sarpras: 'Admin Sarpras',
    koordinator_ski: 'Koordinator SKI',
    koordinator_utama: 'Koordinator Utama',
  };
  return map[role] || role || '—';
};

// Close notif panel when clicking anywhere outside
onMounted(() => {
  document.addEventListener('click', () => { showNotifPanel.value = false; });
});

</script>

<style>
/* ── APP SHELL ── */
.app-shell {
  min-height: 100vh;
  display: flex;
  background: var(--bg-page);
  transition: background var(--transition-base);
}

/* ── MAIN WRAPPER ── */
.main-wrapper {
  flex: 1;
  margin-left: var(--sidebar-width, 180px);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: 0;
}
.main-wrapper--collapsed {
  margin-left: var(--sidebar-collapsed, 68px);
}

/* ── TOPBAR ── */
.topbar {
  position: sticky;
  top: 0;
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: var(--topbar-height, 64px);
  padding: 0 1.75rem;
  background: var(--bg-card);
  border-bottom: 1px solid var(--border-color);
  backdrop-filter: blur(8px);
  box-shadow: var(--shadow-sm);
}
.topbar-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--text-primary);
}
.topbar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}
.topbar-icon-btn {
  position: relative;
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: var(--bg-page);
  border: 1px solid var(--border-color);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-secondary);
  transition: all 0.2s;
}
.topbar-icon-btn:hover { background: var(--color-info-bg); color: var(--color-info); border-color: var(--color-info-border); }
.topbar-icon-btn svg { width: 18px; height: 18px; }
.topbar-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  min-width: 18px;
  height: 18px;
  background: var(--color-error);
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid var(--bg-card);
}
.topbar-user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-secondary);
}

/* ── NOTIFICATION PANEL ── */
.notif-panel {
  position: absolute;
  top: calc(100% + 10px);
  right: 0;
  width: 300px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  box-shadow: 0 16px 48px rgba(0,0,0,0.18);
  z-index: 300;
  overflow: hidden;
}
.notif-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem 0.75rem;
  border-bottom: 1px solid var(--border-color);
}
.notif-header h3 { font-size: 0.9rem; font-weight: 700; }
.notif-empty {
  padding: 2rem;
  text-align: center;
  color: var(--text-muted);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}
.notif-empty p { font-size: 0.875rem; }
.notif-item {
  display: flex;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  align-items: flex-start;
}
.notif-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary); margin-top: 5px; flex-shrink: 0; }
.notif-text { font-size: 0.875rem; color: var(--text-primary); line-height: 1.5; }
.notif-time { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }

/* ── PAGE CONTENT ── */
.page-content {
  flex: 1;
  padding: 1.75rem;
  max-width: 1280px;
  width: 100%;
  margin: 0 auto;
}

/* ── FOOTER ── */
.app-footer {
  padding: 1rem 1.75rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  gap: 0.5rem;
  align-items: center;
  font-size: 0.75rem;
  color: var(--text-muted);
  flex-wrap: wrap;
}
.footer-sep { opacity: 0.4; }

/* ── PAGE TRANSITIONS ── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── NOTIF DROP ANIMATION ── */
.notif-drop-enter-active { animation: notif-in 0.22s cubic-bezier(0.34,1.56,0.64,1); }
.notif-drop-leave-active { animation: notif-out 0.16s ease forwards; }
@keyframes notif-in  { from { opacity:0; transform:translateY(-10px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }
@keyframes notif-out { from { opacity:1; transform:scale(1); } to { opacity:0; transform:translateY(-6px) scale(0.97); } }
</style>
