<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
        <div>
          <h1 class="page-title">Dashboard</h1>
          <p class="page-subtitle">Selamat datang, <strong>{{ formatRole(authStore.user?.role) }}</strong> — {{ formatDate(now) }}</p>
        </div>
        <span class="badge" :class="roleBadgeClass">{{ formatRole(authStore.user?.role) }}</span>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div v-for="stat in stats" :key="stat.label" class="stat-card" :class="`stat-card--${stat.color}`">
        <div class="stat-icon" v-html="stat.icon"></div>
        <div class="stat-body">
          <p class="stat-label">{{ stat.label }}</p>
          <p class="stat-value">
            <span v-if="isLoading" class="skeleton" style="width:40px;height:2rem;display:inline-block;border-radius:6px;"></span>
            <span v-else>{{ stat.value }}</span>
          </p>
          <p class="stat-hint">{{ stat.hint }}</p>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div v-if="quickActions.length" style="margin-top: 1.75rem;">
      <h2 style="font-size:1rem;font-weight:700;color:var(--text-secondary);margin-bottom:0.875rem;text-transform:uppercase;letter-spacing:0.05em;">Aksi Cepat</h2>
      <div class="quick-actions-grid">
        <router-link
          v-for="action in quickActions"
          :key="action.to"
          :to="action.to"
          class="quick-action-card"
          :class="`quick-action--${action.color}`"
        >
          <span class="quick-action-icon" v-html="action.icon"></span>
          <div>
            <p class="quick-action-title">{{ action.title }}</p>
            <p class="quick-action-desc">{{ action.desc }}</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Recent Tickets Table -->
    <div class="card" style="margin-top: 1.75rem;">
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h2 style="font-size:1rem;font-weight:700;">Tiket Terbaru</h2>
          <p style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">5 tiket terakhir dalam sistem</p>
        </div>
        <router-link v-if="canCreateTicket" to="/tickets/create" class="btn btn-primary btn-sm">+ Buat Tiket</router-link>
      </div>

      <div v-if="isLoading" style="padding:2rem;text-align:center;color:var(--text-muted);">
        <div class="skeleton" style="height:200px;border-radius:8px;"></div>
      </div>
      <div v-else-if="recentTickets.length === 0" class="empty-state">
        <div class="empty-icon">📋</div>
        <p class="empty-title">Belum ada tiket</p>
        <p class="empty-sub">Tiket baru yang diajukan akan tampil di sini.</p>
      </div>
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Judul</th>
              <th>Tipe</th>
              <th>Urgensi</th>
              <th>Status</th>
              <th>Tgl Ajukan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ticket in recentTickets" :key="ticket.id" style="cursor:pointer;" @click="$router.push(`/tickets/${ticket.id}`)">
              <td><code style="font-size:0.78rem;color:var(--text-muted);">#{{ String(ticket.id).padStart(4,'0') }}</code></td>
              <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500;">{{ ticket.title }}</td>
              <td><span class="badge badge-neutral" style="font-size:0.7rem;">{{ formatType(ticket.procedure_type) }}</span></td>
              <td><span class="badge" :class="urgencyBadge(ticket.urgency_level)">{{ formatUrgency(ticket.urgency_level) }}</span></td>
              <td><span class="badge" :class="statusBadge(ticket.status)">{{ formatStatus(ticket.status) }}</span></td>
              <td style="color:var(--text-muted);font-size:0.8rem;white-space:nowrap;">{{ formatDate(ticket.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useTicketStore } from '@/stores/ticket';

const authStore = useAuthStore();
const ticketStore = useTicketStore();

const isLoading = ref(true);
const recentTickets = ref([]);
const now = ref(new Date().toISOString());

const ticketCounts = ref({ active: 0, approved: 0, pending_review: 0, rejected: 0 });
const canCreateTicket = computed(() =>
  ['superadmin', 'kepsek', 'dept_ygpkb', 'asp_wilayah', 'kd_sarpras'].includes(authStore.user?.role)
);

onMounted(async () => {
  try {
    const res = await ticketStore.fetchTickets({ per_page: 5 });
    recentTickets.value = res?.data || [];
    // Count status
    recentTickets.value.forEach(t => {
      if (['submitted_to_asp','submitted_to_kd','in_progress'].includes(t.status)) ticketCounts.value.active++;
      if (['approved','completed','signed_archived'].includes(t.status)) ticketCounts.value.approved++;
      if (['submitted_to_asp','submitted_to_kd'].includes(t.status)) ticketCounts.value.pending_review++;
      if (t.status === 'rejected') ticketCounts.value.rejected++;
    });
  } catch {
    // silently fail if API not ready
  } finally {
    isLoading.value = false;
  }
});

const stats = computed(() => [
  {
    label: 'Tiket Aktif',
    value: ticketCounts.value.active,
    hint: 'Sedang diproses',
    color: 'blue',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a2 2 0 012-2h16a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9z"/><path d="M15 12h.01"/></svg>`,
  },
  {
    label: 'Selesai / Approved',
    value: ticketCounts.value.approved,
    hint: 'Bulan ini',
    color: 'green',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
  },
  {
    label: 'Menunggu Review',
    value: ticketCounts.value.pending_review,
    hint: 'Perlu tindakan',
    color: 'yellow',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
  },
  {
    label: 'Ditolak',
    value: ticketCounts.value.rejected,
    hint: 'Total tiket rejected',
    color: 'red',
    icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
  },
]);

const quickActions = computed(() => {
  const role = authStore.user?.role;
  const actions = [];
  if (['superadmin', 'kepsek','dept_ygpkb','asp_wilayah','kd_sarpras'].includes(role)) {
    actions.push({ to: '/tickets/create', title: 'Buat Tiket Baru', desc: 'Ajukan tiket perbaikan, pengadaan, atau mutasi', color: 'blue', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>` });
  }
  if (role === 'asp_wilayah') {
    actions.push({ to: '/tickets/asp-review', title: 'Review Tiket (ASP)', desc: 'Tinjau tiket yang menunggu review Anda', color: 'purple', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>` });
  }
  if (role === 'kd_sarpras') {
    actions.push({ to: '/approval-desk', title: 'Meja Approval KD', desc: 'Setujui atau tolak tiket yang masuk', color: 'purple', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>` });
  }
  if (['admin_sarpras','kd_sarpras'].includes(role)) {
    actions.push({ to: '/purchase-orders', title: 'Kelola PO & BAST', desc: 'Terbitkan nomor PO dan kelola dokumen BAST', color: 'green', icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>` });
  }
  return actions;
});

const roleBadgeClass = computed(() => {
  const map = { kepsek: 'badge-info', dept_ygpkb: 'badge-info', asp_wilayah: 'badge-warning', kd_sarpras: 'badge-success', admin_sarpras: 'badge-neutral', koordinator_ski: 'badge-neutral', koordinator_utama: 'badge-neutral' };
  return map[authStore.user?.role] || 'badge-neutral';
});

const formatRole = (role) => {
  const map = { kepsek: 'Kepala Sekolah', dept_ygpkb: 'Departemen YGPKB', asp_wilayah: 'ASP Wilayah', kd_sarpras: 'KD Sarpras', admin_sarpras: 'Admin Sarpras', koordinator_ski: 'Koordinator SKI', koordinator_utama: 'Koordinator Utama' };
  return map[role] || role;
};
const formatType = (t) => ({ perbaikan:'Perbaikan', pengadaan:'Pengadaan', mutasi:'Mutasi', pemutihan:'Pemutihan', pemeliharaan:'Pemeliharaan' }[t] || t);
const formatUrgency = (u) => ({ rendah:'Rendah', sedang:'Sedang', tinggi_darurat:'Darurat' }[u] || u);
const urgencyBadge = (u) => ({ rendah:'badge-info', sedang:'badge-warning', tinggi_darurat:'badge-error' }[u] || 'badge-neutral');
const formatStatus = (s) => ({ submitted_to_asp:'Menunggu ASP', submitted_to_kd:'Menunggu KD', revision_required:'Perlu Revisi', approved:'Disetujui', in_progress:'Diproses', signed_archived:'Selesai', completed:'Selesai', rejected:'Ditolak' }[s] || s);
const statusBadge = (s) => ({ approved:'badge-success', completed:'badge-success', signed_archived:'badge-success', rejected:'badge-error', revision_required:'badge-warning', submitted_to_asp:'badge-info', submitted_to_kd:'badge-info', in_progress:'badge-info' }[s] || 'badge-neutral');
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) : '-';
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1rem;
}
.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.25rem 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  transition: all 0.2s;
  box-shadow: var(--shadow-sm);
}
.stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.stat-icon :deep(svg) { width: 22px; height: 22px; }
.stat-card--blue   .stat-icon { background: #eff6ff; color: #2563eb; }
.stat-card--green  .stat-icon { background: #f0fdf4; color: #16a34a; }
.stat-card--yellow .stat-icon { background: #fffbeb; color: #d97706; }
.stat-card--red    .stat-icon { background: #fef2f2; color: #dc2626; }
.stat-card--blue   { border-top: 3px solid #3b82f6; }
.stat-card--green  { border-top: 3px solid #22c55e; }
.stat-card--yellow { border-top: 3px solid #f59e0b; }
.stat-card--red    { border-top: 3px solid #ef4444; }
.stat-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value { font-size: 2rem; font-weight: 800; color: var(--text-primary); line-height: 1.2; margin-top: 0.25rem; }
.stat-hint  { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 0.875rem;
}
.quick-action-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  text-decoration: none;
  transition: all 0.2s;
  box-shadow: var(--shadow-sm);
}
.quick-action-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
.quick-action-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.quick-action-icon :deep(svg) { width: 20px; height: 20px; }
.quick-action--blue   .quick-action-icon { background: #eff6ff; color: #2563eb; }
.quick-action--green  .quick-action-icon { background: #f0fdf4; color: #16a34a; }
.quick-action--yellow .quick-action-icon { background: #fffbeb; color: #d97706; }
.quick-action--purple .quick-action-icon { background: #faf5ff; color: #7c3aed; }
.quick-action-title { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); }
.quick-action-desc  { font-size: 0.78rem; color: var(--text-muted); margin-top: 2px; }

.empty-state { text-align: center; padding: 3rem 1rem; }
.empty-icon  { font-size: 3rem; margin-bottom: 0.75rem; }
.empty-title { font-size: 1rem; font-weight: 600; color: var(--text-secondary); }
.empty-sub   { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.375rem; }
</style>
