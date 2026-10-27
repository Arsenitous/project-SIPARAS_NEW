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

    <!-- All Tickets Table Card -->
    <div class="card" style="margin-top: 1.75rem;">
      <!-- Card Header -->
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
        <div>
          <h2 style="font-size:1.05rem;font-weight:700;">📋 Daftar Semua Tiket</h2>
          <p style="font-size:0.8rem;color:var(--text-muted);margin-top:2px;">
            Menampilkan data tiket sesuai hak akses akun Anda (Total: <strong>{{ allTickets.length }}</strong> tiket)
          </p>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;">
          <button class="btn btn-outline btn-sm" @click="loadAllTickets" :disabled="isLoading" title="Segarkan data tiket">
            <span v-if="!isLoading" style="font-size:0.9rem;">↺</span>
            <span v-else class="spinner-sm"></span>
            Segarkan
          </button>
          <router-link v-if="canCreateTicket" to="/tickets/create" class="btn btn-primary btn-sm">
            + Buat Tiket
          </router-link>
        </div>
      </div>

      <!-- Filter & Search Toolbar -->
      <div class="table-toolbar">
        <!-- Search Box -->
        <div class="toolbar-search">
          <svg class="toolbar-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="search"
            type="text"
            class="form-input toolbar-input"
            placeholder="Cari ID (#0001), judul, ruangan, atau unit..."
            @input="currentPage = 1"
          />
          <button v-if="search" class="clear-search-btn" @click="search = ''; currentPage = 1" title="Hapus pencarian">✕</button>
        </div>

        <!-- Filter Prosedur -->
        <div class="toolbar-filter-item">
          <select v-model="filterProcedure" class="form-input toolbar-select" @change="currentPage = 1">
            <option value="">Semua Tipe Prosedur</option>
            <option value="perbaikan">Perbaikan</option>
            <option value="pengadaan">Pengadaan</option>
            <option value="mutasi">Mutasi</option>
            <option value="pemeliharaan">Pemeliharaan</option>
            <option value="pemutihan">Pemutihan</option>
          </select>
        </div>

        <!-- Filter Urgensi -->
        <div class="toolbar-filter-item">
          <select v-model="filterUrgency" class="form-input toolbar-select" @change="currentPage = 1">
            <option value="">Semua Urgensi</option>
            <option value="rendah">🟢 Rendah</option>
            <option value="sedang">🟡 Sedang</option>
            <option value="tinggi_darurat">🔴 Darurat</option>
          </select>
        </div>

        <!-- Filter Status -->
        <div class="toolbar-filter-item">
          <select v-model="filterStatus" class="form-input toolbar-select" @change="currentPage = 1">
            <option value="">Semua Status</option>
            <option value="submitted_to_asp">Menunggu Review ASP</option>
            <option value="submitted_to_kd">Menunggu Approval KD</option>
            <option value="revision_required">Perlu Revisi</option>
            <option value="approved">Disetujui</option>
            <option value="in_progress">Diproses</option>
            <option value="completed">Selesai</option>
            <option value="signed_archived">Arsip BAST</option>
            <option value="rejected">Ditolak</option>
          </select>
        </div>

        <!-- Shortcut Sort Selection -->
        <div class="toolbar-filter-item">
          <select v-model="sortPreset" class="form-input toolbar-select" @change="onSortPresetChange">
            <option value="created_at_desc">📅 Tanggal Terbaru</option>
            <option value="created_at_asc">📅 Tanggal Terlama</option>
            <option value="title_asc">🔤 Judul A — Z</option>
            <option value="title_desc">🔤 Judul Z — A</option>
            <option value="id_asc">🔢 ID Terkecil</option>
            <option value="id_desc">🔢 ID Terbesar</option>
            <option value="urgency_desc">⚡ Urgensi Tertinggi</option>
          </select>
        </div>

        <!-- Reset Button -->
        <button
          v-if="hasActiveFilter"
          class="btn btn-outline btn-sm reset-filter-btn"
          @click="resetFilter"
          title="Reset semua filter"
        >
          Reset Filter
        </button>
      </div>

      <!-- Active Filter Chips Info -->
      <div v-if="hasActiveFilter" class="filter-status-info">
        <span>Menampilkan <strong>{{ filteredTickets.length }}</strong> dari total {{ allTickets.length }} tiket yang cocok dengan kriteria filter.</span>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" style="padding:2.5rem;text-align:center;color:var(--text-muted);">
        <div class="skeleton" style="height:250px;border-radius:8px;"></div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredTickets.length === 0" class="empty-state">
        <div class="empty-icon">🔍</div>
        <p class="empty-title">Tidak ada tiket yang cocok</p>
        <p class="empty-sub">Coba ubah kata kunci pencarian atau sesuaikan opsi filter di atas.</p>
        <button v-if="hasActiveFilter" class="btn btn-outline btn-sm" style="margin-top:0.75rem;" @click="resetFilter">
          Reset Filter
        </button>
      </div>

      <!-- Data Table with Sortable Headers -->
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th class="sortable" @click="toggleSort('id')" title="Urutkan ID">
                ID
                <span class="sort-icon" :class="{ active: sortBy === 'id' }">
                  {{ sortBy === 'id' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('title')" title="Urutkan Judul A-Z / Z-A">
                Judul &amp; Ruangan
                <span class="sort-icon" :class="{ active: sortBy === 'title' }">
                  {{ sortBy === 'title' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('unit')" title="Urutkan Unit A-Z">
                Unit
                <span class="sort-icon" :class="{ active: sortBy === 'unit' }">
                  {{ sortBy === 'unit' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('procedure_type')" title="Urutkan Tipe Prosedur">
                Tipe
                <span class="sort-icon" :class="{ active: sortBy === 'procedure_type' }">
                  {{ sortBy === 'procedure_type' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('urgency_level')" title="Urutkan Urgensi">
                Urgensi
                <span class="sort-icon" :class="{ active: sortBy === 'urgency_level' }">
                  {{ sortBy === 'urgency_level' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('status')" title="Urutkan Status">
                Status
                <span class="sort-icon" :class="{ active: sortBy === 'status' }">
                  {{ sortBy === 'status' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('created_at')" title="Urutkan Tanggal Ajukan">
                Tgl Ajukan
                <span class="sort-icon" :class="{ active: sortBy === 'created_at' }">
                  {{ sortBy === 'created_at' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th style="text-align:center;width:110px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ticket in paginatedTickets"
              :key="ticket.id"
              style="cursor:pointer;"
              @click="$router.push(`/tickets/${ticket.id}`)"
            >
              <td>
                <code class="ticket-id-badge">#{{ String(ticket.id).padStart(4,'0') }}</code>
              </td>
              <td>
                <div style="font-weight:600;color:var(--text-primary);max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                  {{ ticket.title }}
                </div>
                <div v-if="ticket.location_name || ticket.specific_location" style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">
                  📍 {{ ticket.location_name || ticket.specific_location }}
                </div>
              </td>
              <td>
                <span class="unit-tag">
                  🏢 {{ ticket.unit?.name || '—' }}
                </span>
              </td>
              <td>
                <span class="badge" :class="procedureBadge(ticket.procedure_type)" style="font-size:0.72rem;">
                  {{ formatType(ticket.procedure_type) }}
                </span>
              </td>
              <td>
                <span class="badge" :class="urgencyBadge(ticket.urgency_level)">
                  {{ formatUrgency(ticket.urgency_level) }}
                </span>
              </td>
              <td>
                <span class="badge" :class="statusBadge(ticket.status)">
                  {{ formatStatus(ticket.status) }}
                </span>
              </td>
              <td style="color:var(--text-muted);font-size:0.8rem;white-space:nowrap;">
                {{ formatDate(ticket.created_at) }}
              </td>
              <td style="text-align:center;" @click.stop>
                <div style="display:inline-flex;align-items:center;gap:0.375rem;justify-content:center;">
                  <button
                    class="btn btn-outline btn-xs"
                    @click="$router.push(`/tickets/${ticket.id}`)"
                    title="Lihat Detail Berkas"
                  >
                    Buka
                  </button>
                  <button
                    v-if="canDeleteTicket(ticket, authStore.user)"
                    class="btn btn-outline btn-xs btn-table-delete"
                    @click="handleDeleteTicket(ticket)"
                    title="Hapus Tiket"
                    style="color:var(--color-error);border-color:var(--color-error-border);"
                  >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination / Footer Bar -->
      <div v-if="filteredTickets.length > 0" class="table-pagination">
        <div class="pagination-info">
          Menampilkan <strong>{{ pageStart }}</strong> - <strong>{{ pageEnd }}</strong> dari <strong>{{ filteredTickets.length }}</strong> tiket
        </div>

        <div class="pagination-controls">
          <!-- Per Page Selector -->
          <div class="per-page-wrap">
            <span style="font-size:0.78rem;color:var(--text-muted);">Tampilkan:</span>
            <select v-model="perPageOption" class="form-input per-page-select" @change="currentPage = 1">
              <option :value="10">10</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
              <option :value="9999">Semua</option>
            </select>
          </div>

          <!-- Page Navigation -->
          <div v-if="totalPages > 1" class="page-nav">
            <button
              class="btn btn-outline btn-xs page-nav-btn"
              :disabled="currentPage === 1"
              @click="currentPage--"
            >
              ‹ Sebelumnya
            </button>
            <span class="page-indicator">
              {{ currentPage }} / {{ totalPages }}
            </span>
            <button
              class="btn btn-outline btn-xs page-nav-btn"
              :disabled="currentPage === totalPages"
              @click="currentPage++"
            >
              Berikutnya ›
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useTicketStore } from '@/stores/ticket';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import { canDeleteTicket } from '@/utils/ticketWorkflow';

const authStore = useAuthStore();
const ticketStore = useTicketStore();
const toast = useToast();
const { confirm } = useConfirm();

const isLoading = ref(true);
const allTickets = ref([]);
const now = ref(new Date().toISOString());

const handleDeleteTicket = async (ticket) => {
  const confirmed = await confirm({
    title: 'Hapus Tiket #' + String(ticket.id).padStart(4, '0'),
    message: `Apakah Anda yakin ingin menghapus tiket "${ticket.title}"? Seluruh berkas lampiran dan riwayat tiket ini akan dihapus secara permanen.`,
    confirmLabel: 'Ya, Hapus Tiket',
    cancelLabel: 'Batal',
    type: 'danger',
  });

  if (!confirmed) return;

  try {
    const res = await ticketStore.deleteTicket(ticket.id);
    toast.success(res?.message || 'Tiket berhasil dihapus.');
    allTickets.value = allTickets.value.filter(t => t.id !== ticket.id);
  } catch (err) {
    const msg = err?.message || err?.response?.data?.message || 'Gagal menghapus tiket.';
    toast.error(msg);
  }
};

// Filter & Search states
const search = ref('');
const filterProcedure = ref('');
const filterUrgency = ref('');
const filterStatus = ref('');
const sortPreset = ref('created_at_desc');

// Column sorting state
const sortBy = ref('created_at');
const sortDir = ref('desc');

// Pagination state
const currentPage = ref(1);
const perPageOption = ref(15);

const canCreateTicket = computed(() =>
  ['superadmin', 'kepsek', 'dept_ygpkb', 'asp_wilayah', 'kd_sarpras'].includes(authStore.user?.role)
);

const hasActiveFilter = computed(() =>
  !!(search.value.trim() || filterProcedure.value || filterUrgency.value || filterStatus.value)
);

const resetFilter = () => {
  search.value = '';
  filterProcedure.value = '';
  filterUrgency.value = '';
  filterStatus.value = '';
  sortPreset.value = 'created_at_desc';
  sortBy.value = 'created_at';
  sortDir.value = 'desc';
  currentPage.value = 1;
};

const onSortPresetChange = () => {
  const [field, dir] = sortPreset.value.split('_');
  if (field === 'created' && dir === 'at') {
    const parts = sortPreset.value.split('_');
    sortBy.value = 'created_at';
    sortDir.value = parts[2];
  } else if (field === 'urgency') {
    sortBy.value = 'urgency_level';
    sortDir.value = 'desc';
  } else {
    sortBy.value = field;
    sortDir.value = dir;
  }
};

const toggleSort = (column) => {
  if (sortBy.value === column) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = column;
    sortDir.value = column === 'created_at' ? 'desc' : 'asc';
  }
  if (sortBy.value === 'title') {
    sortPreset.value = sortDir.value === 'asc' ? 'title_asc' : 'title_desc';
  } else if (sortBy.value === 'id') {
    sortPreset.value = sortDir.value === 'asc' ? 'id_asc' : 'id_desc';
  } else if (sortBy.value === 'created_at') {
    sortPreset.value = sortDir.value === 'asc' ? 'created_at_asc' : 'created_at_desc';
  }
};

const loadAllTickets = async () => {
  isLoading.value = true;
  try {
    const res = await ticketStore.fetchTickets({ per_page: 500 });
    allTickets.value = res?.data || [];
  } catch {
    allTickets.value = [];
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  loadAllTickets();
});

const urgencyRank = (level) => {
  const map = { tinggi_darurat: 3, sedang: 2, rendah: 1 };
  return map[level] || 0;
};

// Filtered and Sorted Tickets
const filteredTickets = computed(() => {
  let list = allTickets.value.slice();

  // Search filter
  if (search.value.trim()) {
    const q = search.value.trim().toLowerCase();
    list = list.filter(t => {
      const idStr = String(t.id || '');
      const paddedId = '#' + idStr.padStart(4, '0');
      const title = (t.title || '').toLowerCase();
      const unit = (t.unit?.name || '').toLowerCase();
      const loc = (t.location_name || '').toLowerCase();
      const specLoc = (t.specific_location || '').toLowerCase();
      return (
        idStr.includes(q) ||
        paddedId.toLowerCase().includes(q) ||
        title.includes(q) ||
        unit.includes(q) ||
        loc.includes(q) ||
        specLoc.includes(q)
      );
    });
  }

  // Procedure Type filter
  if (filterProcedure.value) {
    list = list.filter(t => t.procedure_type === filterProcedure.value);
  }

  // Urgency filter
  if (filterUrgency.value) {
    list = list.filter(t => t.urgency_level === filterUrgency.value);
  }

  // Status filter
  if (filterStatus.value) {
    list = list.filter(t => t.status === filterStatus.value);
  }

  // Sorting
  list.sort((a, b) => {
    let comparison = 0;
    if (sortBy.value === 'id') {
      comparison = Number(a.id || 0) - Number(b.id || 0);
    } else if (sortBy.value === 'title') {
      comparison = (a.title || '').localeCompare(b.title || '', 'id-ID', { sensitivity: 'base' });
    } else if (sortBy.value === 'unit') {
      comparison = (a.unit?.name || '').localeCompare(b.unit?.name || '', 'id-ID', { sensitivity: 'base' });
    } else if (sortBy.value === 'procedure_type') {
      comparison = (a.procedure_type || '').localeCompare(b.procedure_type || '', 'id-ID');
    } else if (sortBy.value === 'urgency_level') {
      comparison = urgencyRank(a.urgency_level) - urgencyRank(b.urgency_level);
    } else if (sortBy.value === 'status') {
      comparison = (a.status || '').localeCompare(b.status || '', 'id-ID');
    } else if (sortBy.value === 'created_at') {
      const da = new Date(a.created_at || 0).getTime();
      const db = new Date(b.created_at || 0).getTime();
      comparison = da - db;
    }

    return sortDir.value === 'asc' ? comparison : -comparison;
  });

  return list;
});

// Pagination calculations
const totalPages = computed(() => {
  const per = perPageOption.value;
  if (!per || per >= 9999) return 1;
  return Math.ceil(filteredTickets.value.length / per) || 1;
});

const paginatedTickets = computed(() => {
  const per = perPageOption.value;
  if (!per || per >= 9999) return filteredTickets.value;
  const start = (currentPage.value - 1) * per;
  return filteredTickets.value.slice(start, start + per);
});

const pageStart = computed(() => {
  if (filteredTickets.value.length === 0) return 0;
  return (currentPage.value - 1) * perPageOption.value + 1;
});

const pageEnd = computed(() => {
  const end = currentPage.value * perPageOption.value;
  return Math.min(end, filteredTickets.value.length);
});

// Accurate stats calculated from all tickets
const stats = computed(() => {
  let active = 0, approved = 0, pending = 0, rejected = 0;
  allTickets.value.forEach(t => {
    if (['submitted_to_asp', 'submitted_to_kd', 'in_progress'].includes(t.status)) active++;
    if (['approved', 'completed', 'signed_archived'].includes(t.status)) approved++;
    if (['submitted_to_asp', 'submitted_to_kd'].includes(t.status)) pending++;
    if (t.status === 'rejected') rejected++;
  });

  return [
    {
      label: 'Tiket Aktif',
      value: active,
      hint: 'Sedang berjalan',
      color: 'blue',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a2 2 0 012-2h16a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9z"/><path d="M15 12h.01"/></svg>`,
    },
    {
      label: 'Selesai / Approved',
      value: approved,
      hint: 'Total disetujui/selesai',
      color: 'green',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
    },
    {
      label: 'Menunggu Review',
      value: pending,
      hint: 'Perlu verifikasi',
      color: 'yellow',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`,
    },
    {
      label: 'Ditolak',
      value: rejected,
      hint: 'Tiket tidak disetujui',
      color: 'red',
      icon: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
    },
  ];
});

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
  const map = {
    superadmin: 'badge-error',
    kepsek: 'badge-info',
    dept_ygpkb: 'badge-info',
    asp_wilayah: 'badge-warning',
    kd_sarpras: 'badge-success',
    admin_sarpras: 'badge-neutral',
    koordinator_ski: 'badge-neutral',
    koordinator_utama: 'badge-neutral',
  };
  return map[authStore.user?.role] || 'badge-neutral';
});

const formatRole = (role) => {
  const map = {
    superadmin: 'Super Administrator',
    kepsek: 'Kepala Sekolah',
    dept_ygpkb: 'Departemen YGPKB',
    asp_wilayah: 'ASP Wilayah',
    kd_sarpras: 'KD Sarpras',
    admin_sarpras: 'Admin Sarpras',
    koordinator_ski: 'Koordinator SKI',
    koordinator_utama: 'Koordinator Utama',
  };
  return map[role] || role;
};

const formatType = (t) => ({ perbaikan:'Perbaikan', pengadaan:'Pengadaan', mutasi:'Mutasi', pemutihan:'Pemutihan', pemeliharaan:'Pemeliharaan' }[t] || t);
const procedureBadge = (t) => ({
  perbaikan: 'badge-danger',
  pengadaan: 'badge-info',
  mutasi: 'badge-warning',
  pemutihan: 'badge-secondary',
  pemeliharaan: 'badge-success'
}[t] || 'badge-neutral');

const formatUrgency = (u) => ({ rendah:'Rendah', sedang:'Sedang', tinggi_darurat:'Darurat' }[u] || u);
const urgencyBadge = (u) => ({ rendah:'badge-info', sedang:'badge-warning', tinggi_darurat:'badge-error' }[u] || 'badge-neutral');
const formatStatus = (s) => ({ submitted_to_asp:'Menunggu ASP', submitted_to_kd:'Menunggu KD', revision_required:'Perlu Revisi', approved:'Disetujui', in_progress:'Diproses', signed_archived:'Selesai (BAST)', completed:'Selesai', rejected:'Ditolak' }[s] || s);
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

/* ── TOOLBAR STYLES ── */
.table-toolbar {
  padding: 1rem 1.25rem;
  background: var(--bg-page);
  border-top: 1px solid var(--border-color);
  border-bottom: 1px solid var(--border-color);
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.toolbar-search {
  position: relative;
  flex: 1 1 240px;
  min-width: 220px;
}

.toolbar-search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: var(--text-muted);
  pointer-events: none;
}

.toolbar-input {
  padding-left: 2.25rem;
  padding-right: 2rem;
  font-size: 0.85rem;
  height: 38px;
  background: var(--bg-card);
}

.clear-search-btn {
  position: absolute;
  right: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 0.8rem;
  padding: 0.2rem;
}
.clear-search-btn:hover { color: var(--text-primary); }

.toolbar-filter-item {
  flex: 0 1 auto;
}

.toolbar-select {
  height: 38px;
  font-size: 0.85rem;
  background: var(--bg-card);
  padding: 0 2rem 0 0.75rem;
  min-width: 140px;
}

.reset-filter-btn {
  height: 38px;
  white-space: nowrap;
}

.filter-status-info {
  padding: 0.5rem 1.25rem;
  background: rgba(26, 58, 107, 0.04);
  border-bottom: 1px solid var(--border-color);
  font-size: 0.8rem;
  color: var(--text-secondary);
}

/* ── TABLE ROW BADGES ── */
.ticket-id-badge {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--color-primary);
  background: rgba(26, 58, 107, 0.08);
  padding: 0.2rem 0.45rem;
  border-radius: 4px;
}

.unit-tag {
  font-size: 0.8rem;
  color: var(--text-secondary);
  font-weight: 500;
  display: inline-block;
  white-space: nowrap;
}

/* ── PAGINATION STYLES ── */
.table-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 0.875rem 1.25rem;
  background: var(--bg-card);
  border-top: 1px solid var(--border-color);
}

.pagination-info {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.per-page-wrap {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.per-page-select {
  height: 30px;
  font-size: 0.78rem;
  padding: 0 1.5rem 0 0.5rem;
  width: auto;
}

.page-nav {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.page-nav-btn {
  font-size: 0.75rem;
  padding: 0.25rem 0.6rem;
}

.page-indicator {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text-secondary);
  padding: 0 0.35rem;
}

.empty-state { text-align: center; padding: 3rem 1rem; }
.empty-icon  { font-size: 3rem; margin-bottom: 0.75rem; }
.empty-title { font-size: 1rem; font-weight: 600; color: var(--text-secondary); }
.empty-sub   { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.375rem; }
</style>
