<template>
  <div class="kd-desk">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Meja Kerja Approval KD</h1>
        <p class="page-subtitle">Persetujuan final seluruh pengajuan tiket sarpras YGPKB.</p>
      </div>
      <button class="btn btn-outline" @click="loadTickets" :disabled="isLoading">
        <span v-if="!isLoading" style="font-size:1rem; margin-right:0.25rem;">↺</span>
        <span v-if="isLoading" class="spinner-sm"></span>
        {{ isLoading ? 'Memuat...' : 'Segarkan' }}
      </button>
    </div>

    <!-- ── Stats ── -->
    <div class="kd-stats">
      <div class="kd-stat-card">
        <span class="kd-stat-num">{{ tickets.length }}</span>
        <span class="kd-stat-label">Menunggu Keputusan</span>
      </div>
      <div class="kd-stat-card kd-stat-urgent">
        <span class="kd-stat-num">{{ urgentCount }}</span>
        <span class="kd-stat-label">Tinggi / Darurat</span>
      </div>
      <div class="kd-stat-card kd-stat-cost">
        <span class="kd-stat-num kd-stat-num-sm">{{ formatCurrencyShort(totalEstimate) }}</span>
        <span class="kd-stat-label">Total Estimasi Biaya</span>
      </div>
    </div>

    <!-- ── Main Card ── -->
    <div class="card" style="padding: 0; overflow: hidden;">

      <!-- Card Header -->
      <div class="kd-card-header">
        <div class="kd-card-title">
          <span class="kd-card-icon">⚖️</span>
          <span>Menunggu Persetujuan Anda</span>
          <span v-if="tickets.length" class="badge badge-warning">{{ tickets.length }}</span>
        </div>
        <div class="kd-search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="kd-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" placeholder="Cari judul / unit..." class="kd-search-input" />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="kd-empty">
        <div class="kd-spinner"></div>
        <p class="kd-empty-text">Memuat antrean persetujuan...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredTickets.length === 0" class="kd-empty">
        <div class="kd-empty-icon">🎉</div>
        <h3 class="kd-empty-title">Meja Kerja Bersih!</h3>
        <p class="kd-empty-text">
          {{ search ? 'Tidak ada tiket yang cocok dengan pencarian.' : 'Semua pengajuan telah diproses. Tidak ada yang menunggu keputusan Anda.' }}
        </p>
      </div>

      <!-- Table -->
      <div v-else class="kd-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>ID Tiket</th>
              <th>Prosedur</th>
              <th>Unit</th>
              <th>Judul Pengajuan</th>
              <th style="text-align:right;">Estimasi Biaya</th>
              <th style="text-align:center;">Urgensi</th>
              <th style="text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="ticket in filteredTickets"
              :key="ticket.id"
              class="kd-row"
            >
              <td>
                <span class="kd-ticket-id">#{{ String(ticket.id).padStart(4, '0') }}</span>
              </td>
              <td>
                <span :class="['badge', procedureBadge(ticket.procedure_type)]">
                  {{ procedureLabel(ticket.procedure_type) }}
                </span>
              </td>
              <td class="kd-unit">{{ ticket.unit?.name || '—' }}</td>
              <td class="kd-title">{{ ticket.title }}</td>
              <td style="text-align:right;">
                <span class="kd-cost">{{ formatCurrency(ticket.estimated_cost) }}</span>
              </td>
              <td style="text-align:center;">
                <span :class="['badge', urgencyBadge(ticket.urgency_level)]">
                  {{ urgencyLabel(ticket.urgency_level) }}
                </span>
              </td>
              <td style="text-align:center;">
                <button
                  class="btn btn-primary btn-sm"
                  @click="$router.push(`/tickets/${ticket.id}`)"
                >
                  Buka Berkas
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
    t.unit?.name?.toLowerCase().includes(q)
  );
});

const urgentCount = computed(() => tickets.value.filter(t => t.urgency_level === 'tinggi_darurat').length);
const totalEstimate = computed(() => tickets.value.reduce((s, t) => s + Number(t.estimated_cost || 0), 0));

const loadTickets = async () => {
  isLoading.value = true;
  try {
    await ticketStore.fetchTickets({ status: 'submitted_to_kd' });
    tickets.value = ticketStore.tickets || [];
  } catch { /* ignore */ }
  finally { isLoading.value = false; }
};

// ── Formatters & helpers ──
const formatCurrency = (val) => {
  if (!val) return '—';
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
};

const formatCurrencyShort = (val) => {
  if (!val) return 'Rp 0';
  if (val >= 1_000_000_000) return `Rp ${(val / 1_000_000_000).toFixed(1)}M`;
  if (val >= 1_000_000) return `Rp ${(val / 1_000_000).toFixed(1)} Jt`;
  if (val >= 1_000) return `Rp ${(val / 1_000).toFixed(0)}K`;
  return `Rp ${val}`;
};

const procedureLabel = (type) => ({ perbaikan: 'Perbaikan', pengadaan: 'Pengadaan', mutasi: 'Mutasi', pemutihan: 'Pemutihan', pemeliharaan: 'Pemeliharaan' }[type] || type);
const procedureBadge = (type) => ({ perbaikan: 'badge-danger', pengadaan: 'badge-info', mutasi: 'badge-warning', pemutihan: 'badge-secondary', pemeliharaan: 'badge-success' }[type] || 'badge-secondary');
const urgencyLabel = (level) => ({ rendah: 'Rendah', sedang: 'Sedang', tinggi_darurat: '🔴 Darurat' }[level] || level);
const urgencyBadge = (level) => ({ rendah: 'badge-success', sedang: 'badge-warning', tinggi_darurat: 'badge-danger' }[level] || 'badge-secondary');

onMounted(() => loadTickets());
</script>

<style scoped>
.kd-desk { display: flex; flex-direction: column; gap: 1.5rem; }

/* ── Stats ── */
.kd-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.kd-stat-card {
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
.kd-stat-card.kd-stat-urgent { border-left-color: var(--color-error, #e53e3e); }
.kd-stat-card.kd-stat-cost   { border-left-color: #d97706; }

.kd-stat-num { font-size: 2rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
.kd-stat-num-sm { font-size: 1.35rem; }
.kd-stat-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; }

/* ── Card Header ── */
.kd-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.125rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-page);
  flex-wrap: wrap;
}
.kd-card-title { display: flex; align-items: center; gap: 0.625rem; font-size: 1rem; font-weight: 700; color: var(--text-primary); }
.kd-card-icon { font-size: 1.125rem; }

/* ── Search ── */
.kd-search-wrap { position: relative; display: flex; align-items: center; }
.kd-search-icon { position: absolute; left: 0.75rem; width: 15px; height: 15px; color: var(--text-muted); pointer-events: none; }
.kd-search-input {
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
.kd-search-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
}

/* ── Empty ── */
.kd-empty { padding: 4rem 2rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.kd-empty-icon { font-size: 3.5rem; line-height: 1; margin-bottom: 0.5rem; }
.kd-spinner { width: 40px; height: 40px; border: 3px solid var(--border-color); border-top-color: var(--color-primary); border-radius: 50%; animation: spin 0.8s linear infinite; }
.kd-empty-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.kd-empty-text { font-size: 0.9rem; color: var(--text-muted); margin: 0; max-width: 360px; }

/* ── Table ── */
.kd-table-wrap { overflow-x: auto; }
.kd-row { transition: background 0.12s; }
.kd-row:hover td { background: var(--bg-page) !important; }
.kd-ticket-id { font-family: monospace; font-weight: 700; font-size: 0.875rem; color: var(--color-primary); }
.kd-unit { font-size: 0.875rem; color: var(--text-secondary); }
.kd-title { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kd-cost { font-weight: 700; font-size: 0.875rem; color: var(--text-primary); }

.spinner-sm { width: 14px; height: 14px; border: 2px solid rgba(0,0,0,0.15); border-top-color: var(--color-primary); border-radius: 50%; display: inline-block; animation: spin 0.7s linear infinite; margin-right: 0.375rem; vertical-align: middle; }

@keyframes spin { to { transform: rotate(360deg); } }
</style>
