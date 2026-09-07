<template>
  <div class="asp-queue">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Antrean Review ASP</h1>
        <p class="page-subtitle">Verifikasi teknis pengajuan tiket dari Kepala Unit di wilayah Anda.</p>
      </div>
      <button class="btn btn-outline" @click="loadTickets" :disabled="isLoading">
        <span v-if="!isLoading" style="font-size:1rem; margin-right:0.25rem;">↺</span>
        <span v-if="isLoading" class="spinner-sm"></span>
        {{ isLoading ? 'Memuat...' : 'Segarkan' }}
      </button>
    </div>

    <!-- ── Stats Bar ── -->
    <div class="asp-stats">
      <div class="asp-stat-card">
        <span class="asp-stat-num">{{ tickets.length }}</span>
        <span class="asp-stat-label">Menunggu Review</span>
      </div>
      <div class="asp-stat-card asp-stat-urgent">
        <span class="asp-stat-num">{{ urgentCount }}</span>
        <span class="asp-stat-label">Tinggi / Darurat</span>
      </div>
      <div class="asp-stat-card asp-stat-ok">
        <span class="asp-stat-num">{{ normalCount }}</span>
        <span class="asp-stat-label">Normal / Rendah</span>
      </div>
    </div>

    <!-- ── Main Card ── -->
    <div class="card" style="padding: 0; overflow: hidden;">

      <!-- Card Header -->
      <div class="asp-card-header">
        <div class="asp-card-title">
          <span class="asp-card-icon">📋</span>
          <span>Daftar Menunggu Validasi</span>
          <span v-if="tickets.length" class="badge badge-warning">{{ tickets.length }}</span>
        </div>
        <!-- Filter / Search placeholder -->
        <div class="asp-search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="asp-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" placeholder="Cari judul / unit..." class="asp-search-input" />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="asp-empty">
        <div class="asp-empty-spinner"></div>
        <p class="asp-empty-text">Memuat antrean tiket...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredTickets.length === 0" class="asp-empty">
        <div class="asp-empty-icon">✅</div>
        <h3 class="asp-empty-title">Antrean Bersih!</h3>
        <p class="asp-empty-text">
          {{ search ? 'Tidak ada tiket yang cocok dengan pencarian.' : 'Tidak ada tiket yang menunggu review di wilayah Anda saat ini.' }}
        </p>
        <button class="btn btn-outline" @click="loadTickets">Cek Ulang</button>
      </div>

      <!-- Ticket Table -->
      <div v-else class="asp-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Tiket</th>
              <th>Prosedur</th>
              <th>Unit / Lokasi</th>
              <th>Judul Kendala</th>
              <th>Urgensi</th>
              <th>Tanggal</th>
              <th style="text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ticket in filteredTickets"
              :key="ticket.id"
              class="asp-row"
              @click="$router.push(`/tickets/${ticket.id}`)"
            >
              <td>
                <span class="asp-ticket-id">#{{ String(ticket.id).padStart(4, '0') }}</span>
              </td>
              <td>
                <span :class="['badge', procedureBadge(ticket.procedure_type)]">
                  {{ procedureLabel(ticket.procedure_type) }}
                </span>
              </td>
              <td>
                <span class="asp-unit">{{ ticket.unit?.name || '—' }}</span>
                <span class="asp-location">{{ ticket.location_name || 'Lokasi tidak diisi' }}</span>
              </td>
              <td class="asp-title-cell">{{ ticket.title }}</td>
              <td>
                <span :class="['badge', urgencyBadge(ticket.urgency_level)]">
                  {{ urgencyLabel(ticket.urgency_level) }}
                </span>
              </td>
              <td class="asp-date">{{ formatDate(ticket.created_at) }}</td>
              <td style="text-align:center;">
                <button
                  class="btn btn-warning btn-sm"
                  @click.stop="reviewTicket(ticket)"
                >
                  Review
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTicketStore } from '@/stores/ticket';

const ticketStore = useTicketStore();
const router = useRouter();
const isLoading = ref(false);
const tickets = ref([]);
const search = ref('');

const filteredTickets = computed(() => {
  if (!search.value.trim()) return tickets.value;
  const q = search.value.toLowerCase();
  return tickets.value.filter(t =>
    t.title?.toLowerCase().includes(q) ||
    t.unit?.name?.toLowerCase().includes(q) ||
    t.location_name?.toLowerCase().includes(q)
  );
});

const urgentCount = computed(() => tickets.value.filter(t => t.urgency_level === 'tinggi_darurat').length);
const normalCount = computed(() => tickets.value.filter(t => t.urgency_level !== 'tinggi_darurat').length);

const loadTickets = async () => {
  isLoading.value = true;
  try {
    await ticketStore.fetchTickets({ status: 'submitted_to_asp' });
    tickets.value = ticketStore.tickets || [];
  } catch { /* ignore */ }
  finally { isLoading.value = false; }
};

const reviewTicket = (ticket) => {
  router.push(`/tickets/${ticket.id}`);
};

// ── Helpers ──
const procedureLabel = (type) => ({
  perbaikan: 'Perbaikan', pengadaan: 'Pengadaan',
  mutasi: 'Mutasi', pemutihan: 'Pemutihan', pemeliharaan: 'Pemeliharaan',
}[type] || type);

const procedureBadge = (type) => ({
  perbaikan: 'badge-danger', pengadaan: 'badge-info',
  mutasi: 'badge-warning', pemutihan: 'badge-secondary', pemeliharaan: 'badge-success',
}[type] || 'badge-secondary');

const urgencyLabel = (level) => ({
  rendah: 'Rendah', sedang: 'Sedang', tinggi_darurat: '🔴 Darurat',
}[level] || level);

const urgencyBadge = (level) => ({
  rendah: 'badge-success', sedang: 'badge-warning', tinggi_darurat: 'badge-danger',
}[level] || 'badge-secondary');

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

onMounted(() => loadTickets());
</script>

<style scoped>
.asp-queue { display: flex; flex-direction: column; gap: 1.5rem; }

/* ── Stats ── */
.asp-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.asp-stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  padding: 1.125rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  border-left: 4px solid var(--color-primary);
  box-shadow: var(--shadow-sm);
}

.asp-stat-card.asp-stat-urgent { border-left-color: var(--color-error, #e53e3e); }
.asp-stat-card.asp-stat-ok    { border-left-color: var(--color-success, #38a169); }

.asp-stat-num {
  font-size: 2rem;
  font-weight: 800;
  color: var(--text-primary);
  line-height: 1;
}

.asp-stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
  font-weight: 500;
}

/* ── Card Header ── */
.asp-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.125rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-page);
  flex-wrap: wrap;
}

.asp-card-title {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-primary);
}

.asp-card-icon { font-size: 1.125rem; }

/* ── Search ── */
.asp-search-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.asp-search-icon {
  position: absolute;
  left: 0.75rem;
  width: 15px;
  height: 15px;
  color: var(--text-muted);
  pointer-events: none;
}

.asp-search-input {
  padding: 0.5rem 0.875rem 0.5rem 2.25rem;
  border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md);
  background: var(--bg-input);
  font-size: 0.875rem;
  font-family: inherit;
  color: var(--text-primary);
  outline: none;
  width: 220px;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.asp-search-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
}

/* ── Empty State ── */
.asp-empty {
  padding: 4rem 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.asp-empty-icon {
  font-size: 3.5rem;
  line-height: 1;
  margin-bottom: 0.5rem;
}

.asp-empty-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border-color);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.asp-empty-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.asp-empty-text {
  font-size: 0.9rem;
  color: var(--text-muted);
  margin: 0;
  max-width: 360px;
}

/* ── Table ── */
.asp-table-wrap { overflow-x: auto; }

.asp-row {
  cursor: pointer;
  transition: background 0.12s;
}

.asp-row:hover td { background: var(--bg-page) !important; }

.asp-ticket-id {
  font-family: 'JetBrains Mono', 'Fira Code', monospace;
  font-weight: 700;
  font-size: 0.85rem;
  color: var(--color-primary);
}

.asp-unit {
  display: block;
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--text-primary);
}

.asp-location {
  display: block;
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 1px;
}

.asp-title-cell {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.asp-date {
  font-size: 0.8rem;
  color: var(--text-muted);
  white-space: nowrap;
}

.btn-warning {
  background: linear-gradient(135deg, #d97706, #b45309);
  color: #fff;
  border: none;
}
.btn-warning:hover { background: linear-gradient(135deg, #b45309, #92400e); }

.spinner-sm {
  width: 14px; height: 14px;
  border: 2px solid rgba(0,0,0,0.15);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  display: inline-block;
  animation: spin 0.7s linear infinite;
  margin-right: 0.375rem;
  vertical-align: middle;
}

/* Badge secondary */
:deep(.badge-secondary) {
  background: var(--bg-page);
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
