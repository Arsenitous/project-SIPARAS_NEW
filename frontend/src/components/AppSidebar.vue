<template>
  <!-- Mobile Overlay Backdrop -->
  <transition name="overlay-fade">
    <div
      v-if="isMobileOpen"
      class="sidebar-overlay"
      @click="$emit('close')"
    />
  </transition>

  <aside class="sidebar" :class="{ 'sidebar--collapsed': isCollapsed, 'sidebar--mobile-open': isMobileOpen }">
    <!-- Floating Edge Toggle Button (HANYA tampil saat sidebar di-minimize) -->
    <button
      v-if="isCollapsed"
      class="sidebar-collapse-btn"
      @click="toggleCollapse"
      title="Perluas Sidebar"
      aria-label="Perluas Sidebar"
    >
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <polyline points="9 18 15 12 9 6"/>
      </svg>
    </button>

    <!-- Logo Area -->
    <div
      class="sidebar-logo"
      :class="{ 'sidebar-logo--collapsed': isCollapsed, 'cursor-pointer': isCollapsed }"
      @click="isCollapsed && toggleCollapse()"
      :title="isCollapsed ? 'Klik untuk memperluas sidebar' : ''"
    >
      <div class="sidebar-logo-inner">
        <img src="/logo-ygpkb.png" alt="YGPKB Logo" class="sidebar-logo-img" />
        <transition name="fade-text">
          <div v-if="!isCollapsed" class="sidebar-logo-text">
            <span class="sidebar-appname">SIPARAS</span>
            <span class="sidebar-tagline">Sarpras YGPKB</span>
          </div>
        </transition>
      </div>

      <transition name="fade-text">
        <button v-if="!isCollapsed" class="sidebar-collapse-btn-header" @click.stop="toggleCollapse" title="Ciutkan Sidebar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
      </transition>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div class="sidebar-nav-section">
        <p v-if="!isCollapsed" class="sidebar-section-label">Menu Utama</p>

        <router-link
          v-for="item in menuItems"
          :key="item.to"
          :to="item.to"
          class="sidebar-link"
          :class="{ active: isActive(item.to) }"
          :title="isCollapsed ? item.label : ''"
        >
          <span class="sidebar-link-icon" v-html="item.icon"></span>
          <transition name="fade-text">
            <span v-if="!isCollapsed" class="sidebar-link-label">{{ item.label }}</span>
          </transition>
          <!-- Badge -->
          <transition name="fade-text">
            <span v-if="!isCollapsed && item.badge" class="sidebar-badge">{{ item.badge }}</span>
          </transition>
          <span v-if="isCollapsed && item.badge" class="sidebar-badge-dot"></span>
        </router-link>
      </div>

      <!-- Admin Section -->
      <div v-if="hasMasterAccess || hasUserMgmtAccess" class="sidebar-nav-section">
        <p v-if="!isCollapsed" class="sidebar-section-label">Administrasi</p>

        <!-- Master Data Dropdown -->
        <div v-if="hasMasterAccess">
          <button
            class="sidebar-link sidebar-group-btn"
            :class="{ active: isMasterActive }"
            @click="toggleMasterGroup"
            :title="isCollapsed ? 'Master Data' : ''"
          >
            <span class="sidebar-link-icon" v-html="icons.master"></span>
            <transition name="fade-text">
              <span v-if="!isCollapsed" class="sidebar-link-label">Master Data</span>
            </transition>
            <transition name="fade-text">
              <span v-if="!isCollapsed" class="sidebar-group-chevron" :class="{ rotated: masterOpen }">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:13px;height:13px;"><polyline points="6 9 12 15 18 9"/></svg>
              </span>
            </transition>
          </button>

          <!-- Sub items -->
          <transition name="sub-expand">
            <div v-if="!isCollapsed && masterOpen" class="sidebar-sub-group">
              <router-link to="/master-data/units" class="sidebar-sub-link" :class="{ active: isActive('/master-data/units') }">
                <span class="sub-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
                Unit Operasional
              </router-link>
              <router-link to="/master-data/vendors" class="sidebar-sub-link" :class="{ active: isActive('/master-data/vendors') }">
                <span class="sub-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><rect x="9" y="12" width="6" height="10"/><path d="M1 9l2 2 2-2"/><path d="M21 9l-2 2-2-2"/></svg>
                </span>
                Mitra Vendor
              </router-link>
            </div>
          </transition>

          <!-- Collapsed: show direct links with icons only -->
          <template v-if="isCollapsed">
            <router-link to="/master-data/units" class="sidebar-link" :class="{ active: isActive('/master-data/units') }" title="Unit Operasional">
              <span class="sidebar-link-icon">🏫</span>
            </router-link>
            <router-link to="/master-data/vendors" class="sidebar-link" :class="{ active: isActive('/master-data/vendors') }" title="Mitra Vendor">
              <span class="sidebar-link-icon">🏭</span>
            </router-link>
          </template>
        </div>

        <!-- Manajemen Pengguna -->
        <router-link
          v-if="hasUserMgmtAccess"
          to="/admin/users"
          class="sidebar-link"
          :class="{ active: isActive('/admin/users') }"
          :title="isCollapsed ? 'Manajemen Pengguna' : ''"
        >
          <span class="sidebar-link-icon" v-html="icons.users"></span>
          <transition name="fade-text">
            <span v-if="!isCollapsed" class="sidebar-link-label">Manajemen Pengguna</span>
          </transition>
        </router-link>
      </div>
    </nav>

    <!-- Spacer -->
    <div style="flex: 1;"></div>

    <!-- Dark Mode Toggle -->
    <div class="sidebar-darkmode">
      <button class="sidebar-darkmode-btn" @click="$emit('toggle-dark')" :title="isDark ? 'Mode Terang' : 'Mode Gelap'">
        <svg v-if="isDark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        <transition name="fade-text">
          <span v-if="!isCollapsed">{{ isDark ? 'Mode Terang' : 'Mode Gelap' }}</span>
        </transition>
      </button>
    </div>

    <!-- User Profile Card -->
    <div class="sidebar-profile" :class="{ 'sidebar-profile--collapsed': isCollapsed }">
      <template v-if="!isCollapsed">
        <div class="sidebar-profile-avatar">
          {{ userInitials }}
        </div>
        <div class="sidebar-profile-info">
          <p class="sidebar-profile-name">{{ formatRole(authStore.user?.role) }}</p>
          <p class="sidebar-profile-role">{{ authStore.user?.username || authStore.user?.email }}</p>
          <p v-if="authStore.user?.unit" class="sidebar-profile-unit">{{ authStore.user.unit?.name }}</p>
        </div>
        <button class="sidebar-logout-btn" @click="handleLogout" title="Keluar">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </template>

      <!-- Collapsed Mode: Avatar Centered, and Centered Logout Button Below -->
      <template v-else>
        <div
          class="sidebar-profile-avatar"
          :title="`${authStore.user?.name || authStore.user?.username} (${formatRole(authStore.user?.role)})`"
        >
          {{ userInitials }}
        </div>
        <button
          class="sidebar-logout-btn sidebar-logout-btn--collapsed"
          @click="handleLogout"
          title="Keluar (Logout)"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </template>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  pendingCount: { type: Number, default: 0 },
  isDark: { type: Boolean, default: false },
  isMobileOpen: { type: Boolean, default: false },
});
const emit = defineEmits(['toggle-dark', 'toggle-collapse', 'close']);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();
const isCollapsed = ref(false);

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value;
  emit('toggle-collapse', isCollapsed.value);
};
const isActive = (path) => route.path.startsWith(path);

const userInitials = computed(() => {
  const role = authStore.user?.role || '';
  const abbr = {
    superadmin: 'SA', kepsek: 'KS', dept_ygpkb: 'DY',
    asp_wilayah: 'AS', kd_sarpras: 'KD', admin_sarpras: 'AD',
    koordinator_ski: 'SK', koordinator_utama: 'KU',
  };
  return abbr[role] || role.slice(0,2).toUpperCase() || '?';
});

const formatRole = (role) => {
  if (!role) return '';
  const map = {
    kepsek: 'Kepala Sekolah',
    dept_ygpkb: 'Departemen YGPKB',
    asp_wilayah: 'ASP Wilayah',
    kd_sarpras: 'Kepala Divisi Sarpras',
    admin_sarpras: 'Admin Sarpras',
    koordinator_ski: 'Koordinator SKI',
    koordinator_utama: 'Koordinator Utama',
  };
  return map[role] || role;
};

// SVG icons inline
const icons = {
  dashboard: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
  ticket: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a2 2 0 012-2h16a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9z"/><path d="M15 12h.01"/></svg>`,
  ticketPlus: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a2 2 0 012-2h16a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9z"/><line x1="12" y1="10" x2="12" y2="14"/><line x1="10" y1="12" x2="14" y2="12"/></svg>`,
  review: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>`,
  approval: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
  po: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
  report: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>`,
  master: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 010 14.14M4.93 4.93a10 10 0 000 14.14"/></svg>`,
  users: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>`,
};

const menuItems = computed(() => {
  const role = authStore.user?.role;
  const items = [
    { to: '/dashboard', label: 'Dashboard', icon: icons.dashboard },
  ];

  if (['superadmin', 'kepsek', 'dept_ygpkb', 'asp_wilayah', 'kd_sarpras'].includes(role)) {
    items.push({ to: '/tickets/create', label: 'Buat Tiket', icon: icons.ticketPlus });
  }

  if (role === 'asp_wilayah') {
    items.push({
      to: '/tickets/asp-review',
      label: 'Antrean Review ASP',
      icon: icons.review,
      badge: props.pendingCount > 0 ? props.pendingCount : null,
    });
  }

  if (role === 'kd_sarpras') {
    items.push({
      to: '/approval-desk',
      label: 'Approval KD',
      icon: icons.approval,
      badge: props.pendingCount > 0 ? props.pendingCount : null,
    });
  }

  if (['admin_sarpras', 'kd_sarpras', 'asp_wilayah', 'koordinator_utama', 'koordinator_ski'].includes(role)) {
    items.push({ to: '/purchase-orders', label: 'Manajemen PO & BAST', icon: icons.po });
  }

  if (['kd_sarpras', 'koordinator_ski', 'koordinator_utama'].includes(role)) {
    items.push({ to: '/reports/executive', label: 'Laporan Eksekutif', icon: icons.report });
  }

  return items;
});

const hasMasterAccess  = computed(() => ['superadmin', 'admin_sarpras', 'kd_sarpras'].includes(authStore.user?.role));
const hasUserMgmtAccess = computed(() => ['superadmin', 'kd_sarpras', 'admin_sarpras'].includes(authStore.user?.role));
const isMasterActive = computed(() => route.path.startsWith('/master-data'));

const masterOpen = ref(false);
const toggleMasterGroup = () => {
  if (!isCollapsed.value) masterOpen.value = !masterOpen.value;
  else router.push('/master-data/units');
};
// Auto-open when on master data pages
watch(() => route.path, (p) => {
  if (p.startsWith('/master-data')) masterOpen.value = true;
  // Auto-close mobile drawer on navigation
  emit('close');
}, { immediate: true });

const handleLogout = async () => {
  try {
    await authStore.logout();
    toast.success('Anda telah keluar dari sistem.', 'Logout Berhasil');
    router.push('/login');
  } catch {
    router.push('/login');
  }
};
</script>

<style scoped>
/* ── MOBILE OVERLAY ── */
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.55);
  z-index: 99;
  backdrop-filter: blur(2px);
}
.overlay-fade-enter-active { transition: opacity 0.25s ease; }
.overlay-fade-leave-active { transition: opacity 0.2s ease; }
.overlay-fade-enter-from, .overlay-fade-leave-to { opacity: 0; }

/* ── SIDEBAR BASE ── */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: var(--sidebar-width, 180px);
  background: var(--bg-sidebar, #0f2347);
  display: flex;
  flex-direction: column;
  z-index: 100;
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: var(--shadow-sidebar, 4px 0 20px rgba(0,0,0,0.15));
  overflow: visible; /* allow collapse btn to stick out */
}
/* clip inner content only */
.sidebar > *:not(.sidebar-collapse-btn) { overflow: hidden; }
.sidebar--collapsed {
  width: var(--sidebar-collapsed, 68px);
}

/* ── MOBILE DRAWER MODE ── */
@media (max-width: 768px) {
  .sidebar {
    /* On mobile, sidebar is hidden off-screen by default */
    width: 280px !important;
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 200;
  }
  .sidebar--mobile-open {
    transform: translateX(0);
    box-shadow: 4px 0 40px rgba(0, 0, 0, 0.4);
  }
}

/* ── COLLAPSE / EXPAND FLOATING EDGE BUTTON ── */
.sidebar-collapse-btn {
  position: absolute;
  top: 19px;
  right: -13px;
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--bg-sidebar, #0f2347);
  border: 2px solid rgba(255, 255, 255, 0.4);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 120;
}
.sidebar-collapse-btn:hover {
  background: var(--color-primary, #1a3a6b);
  border-color: #ffffff;
  transform: scale(1.18);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.5);
}
.sidebar-collapse-btn svg {
  width: 14px;
  height: 14px;
}
@media (max-width: 768px) {
  .sidebar-collapse-btn {
    display: none;
  }
}

/* ── LOGO ── */
.sidebar-logo {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.25rem;
  padding: 0 0.625rem;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  height: var(--topbar-height, 64px);
  min-height: 64px;
  transition: background 0.2s;
}
.sidebar-logo.cursor-pointer { cursor: pointer; }
.sidebar-logo.cursor-pointer:hover { background: rgba(255,255,255,0.03); }

.sidebar-logo-inner {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.sidebar--collapsed .sidebar-logo {
  justify-content: center !important;
  padding: 0 !important;
}

.sidebar--collapsed .sidebar-logo-inner {
  justify-content: center !important;
  width: 100% !important;
  gap: 0 !important;
}

.sidebar-logo-img {
  width: 38px;
  height: 38px;
  object-fit: contain;
  flex-shrink: 0;
  filter: brightness(0) invert(1);
  border-radius: 8px;
}

.sidebar--collapsed .sidebar-logo-img {
  margin: 0 auto;
}
.sidebar-logo-text { overflow: hidden; white-space: nowrap; }
.sidebar-appname {
  display: block;
  font-size: 0.9rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: 0.05em;
  line-height: 1.2;
}
.sidebar-tagline {
  display: block;
  font-size: 0.65rem;
  color: var(--text-sidebar-muted, rgba(255,255,255,0.5));
  letter-spacing: 0.04em;
  margin-top: 1px;
}

/* ── COLLAPSE TOGGLE BUTTON IN HEADER ── */
.sidebar-collapse-btn-header {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(255,255,255,0.06);
  border: 1px solid rgba(255,255,255,0.1);
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}
.sidebar-collapse-btn-header:hover {
  background: rgba(255,255,255,0.15);
  color: #fff;
}
.sidebar-collapse-btn-header svg { width: 16px; height: 16px; }

/* ── NAV ── */
.sidebar-nav { display: flex; flex-direction: column; gap: 0.25rem; padding: 0 0.625rem; overflow-y: auto; overflow-x: hidden; }
.sidebar-nav-section { margin-bottom: 0.5rem; }
.sidebar-section-label {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--text-sidebar-muted, rgba(255,255,255,0.4));
  padding: 0.75rem 0.5rem 0.375rem;
}

.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  border-radius: 10px;
  text-decoration: none;
  color: var(--text-sidebar, rgba(255,255,255,0.8));
  transition: all 0.2s;
  position: relative;
  white-space: nowrap;
  overflow: hidden;
}
.sidebar--collapsed .sidebar-link {
  justify-content: center;
  padding-left: 0;
  padding-right: 0;
}
.sidebar-link:hover { background: var(--bg-sidebar-item, rgba(255,255,255,0.06)); color: #fff; }
.sidebar-link.active { background: var(--bg-sidebar-active, rgba(255,255,255,0.15)); color: #fff; font-weight: 600; }
.sidebar-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 20%;
  height: 60%;
  width: 3px;
  background: var(--color-accent, #c8a84b);
  border-radius: 0 3px 3px 0;
}

.sidebar-link-icon { width: 20px; height: 20px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.sidebar-link-icon :deep(svg) { width: 100%; height: 100%; }
.sidebar-link-label { flex: 1; font-size: 0.875rem; }

.sidebar-badge {
  background: var(--color-error, #dc2626);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 0.1rem 0.45rem;
  border-radius: 999px;
  min-width: 20px;
  text-align: center;
}
.sidebar-badge-dot {
  width: 8px;
  height: 8px;
  background: var(--color-error, #dc2626);
  border-radius: 50%;
  position: absolute;
  top: 8px;
  right: 8px;
}

/* ── DARK MODE TOGGLE ── */
.sidebar-darkmode { padding: 0.5rem 0.625rem; }
.sidebar-darkmode-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.65);
  font-size: 0.8125rem;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
  overflow: hidden;
}
.sidebar--collapsed .sidebar-darkmode-btn {
  justify-content: center;
  padding-left: 0;
  padding-right: 0;
}
.sidebar-darkmode-btn:hover { background: rgba(255,255,255,0.10); color: #fff; }
.sidebar-darkmode-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

/* ── PROFILE ── */
.sidebar-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 0.875rem;
  border-top: 1px solid rgba(255,255,255,0.08);
  min-height: 72px;
  overflow: hidden;
}

.sidebar--collapsed .sidebar-profile {
  flex-direction: column !important;
  justify-content: center !important;
  align-items: center !important;
  gap: 0.5rem !important;
  padding: 0.75rem 0 !important;
  min-height: 84px !important;
}

.sidebar-logout-btn--collapsed {
  margin: 0 auto !important;
}
.sidebar-profile-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--color-accent, #c8a84b), var(--color-primary-light, #2a5298));
  color: #fff;
  font-size: 0.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.sidebar-profile-info { flex: 1; min-width: 0; overflow: hidden; }
.sidebar-profile-name { font-size: 0.8125rem; font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-profile-role { font-size: 0.7rem; color: var(--color-accent, #c8a84b); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sidebar-profile-unit { font-size: 0.65rem; color: rgba(255,255,255,0.4); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px; }

.sidebar-logout-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(220,38,38,0.15);
  border: 1px solid rgba(220,38,38,0.25);
  color: #f87171;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.sidebar-logout-btn:hover { background: rgba(220,38,38,0.3); color: #fff; }
.sidebar-logout-btn svg { width: 16px; height: 16px; }

/* ── TEXT FADE TRANSITION ── */
.fade-text-enter-active,
.fade-text-leave-active { transition: opacity 0.2s ease; }
.fade-text-enter-from,
.fade-text-leave-to { opacity: 0; }

/* ── Sidebar Group / Dropdown ── */
.sidebar-group-btn {
  width: 100%;
  cursor: pointer;
  border: none;
  background: none;
  text-align: left;
}
.sidebar-group-chevron {
  margin-left: auto;
  display: flex;
  align-items: center;
  color: var(--text-muted);
  transition: transform 0.25s ease;
  flex-shrink: 0;
}
.sidebar-group-chevron.rotated { transform: rotate(-180deg); }

/* Sub-items */
.sidebar-sub-group {
  overflow: hidden;
  margin-left: 0.75rem;
  border-left: 2px solid rgba(255,255,255,0.12);
  margin-bottom: 0.25rem;
  padding-left: 0;
}
.sidebar-sub-link {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.5rem 0.75rem 0.5rem 0.875rem;
  font-size: 0.83rem;
  font-weight: 500;
  color: var(--text-sidebar, rgba(255,255,255,0.8));
  text-decoration: none;
  border-radius: 0 8px 8px 0;
  transition: background 0.15s, color 0.15s;
  white-space: nowrap;
}
.sidebar-sub-link:hover {
  background: rgba(255,255,255,0.06);
  color: #fff;
}
.sidebar-sub-link.active {
  background: rgba(255,255,255,0.12);
  color: #fff;
  font-weight: 600;
}
.sub-icon {
  width: 16px; height: 16px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  opacity: 0.85;
}
.sub-icon svg { width: 16px; height: 16px; }

/* Sub-expand transition */
.sub-expand-enter-active { animation: sub-in 0.22s ease; }
.sub-expand-leave-active { animation: sub-in 0.18s ease reverse; }
@keyframes sub-in { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
</style>
