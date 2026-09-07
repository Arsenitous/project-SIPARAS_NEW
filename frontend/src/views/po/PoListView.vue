<template>
  <div class="po-page">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Manajemen PO & BAST</h1>
        <p class="page-subtitle">{{ canManagePO ? 'Kelola Purchase Order dan Berita Acara Serah Terima.' : 'Pantau status Purchase Order dan Berita Acara Serah Terima (read-only).' }}</p>
      </div>
    </div>

    <!-- ── Tab Bar ── -->
    <div class="po-tabs" style="justify-content: space-between; align-items: flex-end;">
      <div style="display: flex; gap: 0.25rem;">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          :class="['po-tab', { active: activeTab === tab.value }]"
          @click="activeTab = tab.value"
        >
          <span>{{ tab.icon }}</span>
          <span>{{ tab.label }}</span>
          <span v-if="tab.count > 0" class="po-tab-badge">{{ tab.count }}</span>
        </button>
      </div>
      
      <!-- Right Side Actions -->
      <div style="display:flex;align-items:center;gap:0.75rem; margin-bottom: 1rem;">
        <span v-if="!canManagePO" class="badge badge-neutral" style="font-size:0.72rem;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          View Only
        </span>
        <button v-if="canManagePO" class="btn btn-primary btn-sm" @click="showDraftModal = true">
          <span style="font-size:1.1rem; margin-right:0.25rem; font-weight:300;">+</span>
          Buat Draf PO
        </button>
      </div>
    </div>

    <!-- ── Main Card ── -->
    <div class="card" style="padding: 0; overflow: hidden;">

      <!-- Card Header with search -->
      <div class="po-card-header">
        <div class="po-card-title">
          <span>{{ currentTab?.icon }}</span>
          <span>{{ currentTab?.label }}</span>
        </div>
        <div class="po-search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="po-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" placeholder="Cari nomor PO / vendor..." class="po-search-input" />
        </div>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="po-empty">
        <div class="po-spinner"></div>
        <p class="po-empty-text">Memuat data Purchase Order...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredPos.length === 0" class="po-empty">
        <div class="po-empty-icon">{{ emptyStateIcon }}</div>
        <h3 class="po-empty-title">{{ emptyStateTitle }}</h3>
        <p class="po-empty-text">{{ emptyStateDesc }}</p>
        <button v-if="canManagePO" class="btn btn-primary" @click="showDraftModal = true">
          <span style="font-size:1.1rem; margin-right:0.25rem;">+</span>
          Buat Draf PO Baru
        </button>
      </div>

      <!-- PO Table -->
      <div v-else class="po-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Nomor PO</th>
              <th>Vendor</th>
              <th>ID Tiket</th>
              <th>Unit</th>
              <th style="text-align:right;">Grand Total</th>
              <th style="text-align:center;">Status</th>
              <th style="text-align:center;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="po in filteredPos" :key="po.id" class="po-row">
              <td>
                <span class="po-number">{{ po.po_number || `DRAF-${po.id}` }}</span>
              </td>
              <td>
                <span class="po-vendor">{{ po.vendor?.name || '—' }}</span>
                <span class="po-vendor-pic">{{ po.vendor?.pic_name || '' }}</span>
              </td>
              <td>
                <span class="po-ticket-id" @click="$router.push(`/tickets/${po.ticket_id}`)">
                  #{{ String(po.ticket_id).padStart(4, '0') }}
                </span>
              </td>
              <td class="po-unit">{{ po.ticket?.unit?.name || '—' }}</td>
              <td style="text-align:right;">
                <span class="po-total">{{ formatRp(po.grand_total) }}</span>
              </td>
              <td style="text-align:center;">
                <span :class="['badge', statusBadge(po.status)]">{{ statusLabel(po.status) }}</span>
              </td>
              <td style="text-align:center;">
                <div class="po-actions">
                  <button class="btn btn-outline btn-sm" title="Lihat Detail">👁 Detail</button>
                  <button v-if="canManagePO && po.status === 'draft'" class="btn btn-primary btn-sm" title="Ajukan ke Admin">Ajukan</button>
                  <button v-if="po.status === 'approved'" class="btn btn-success btn-sm" title="Download PDF PO">⬇ PDF</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── Draft PO Modal Placeholder ── -->
    <div v-if="showDraftModal" class="po-modal-overlay" @click.self="showDraftModal = false">
      <div class="po-modal">
        <div class="po-modal-header">
          <h2 class="po-modal-title">Buat Draf Purchase Order</h2>
          <button class="po-modal-close" @click="showDraftModal = false">✕</button>
        </div>
        <div class="po-modal-body">
          <div class="po-modal-info">
            <span class="po-modal-info-icon">ℹ️</span>
            <p>Fitur pembuatan draf PO akan terhubung ke tiket yang sudah disetujui. Pilih tiket yang akan dibuatkan PO-nya.</p>
          </div>
          <p style="text-align:center; color:var(--text-muted); font-size:0.9rem; padding: 2rem 0;">
            Form draf PO akan tersedia setelah ada tiket berstatus <strong>Approved</strong>.
          </p>
        </div>
        <div class="po-modal-footer">
          <button class="btn btn-outline" @click="showDraftModal = false">Tutup</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';

const router    = useRouter();
const authStore = useAuthStore();

// Koordinator: hanya bisa VIEW (tidak bisa Create/Edit/Delete)
const canManagePO = computed(() =>
  ['superadmin', 'admin_sarpras', 'kd_sarpras', 'asp_wilayah'].includes(authStore.user?.role)
);
const isLoading = ref(false);
const pos = ref([]);
const search = ref('');
const activeTab = ref('all');
const showDraftModal = ref(false);

const tabs = computed(() => [
  { value: 'all',      icon: '📄', label: 'Semua PO',       count: pos.value.length },
  { value: 'draft',    icon: '✏️', label: 'Draf',           count: pos.value.filter(p => p.status === 'draft').length },
  { value: 'approved', icon: '✅', label: 'Disetujui',      count: pos.value.filter(p => p.status === 'approved').length },
  { value: 'signed',   icon: '📝', label: 'Ditandatangani', count: pos.value.filter(p => p.status === 'signed_archived').length },
]);

const currentTab = computed(() => tabs.value.find(t => t.value === activeTab.value));

const filteredPos = computed(() => {
  let list = activeTab.value === 'all' ? pos.value : pos.value.filter(p => p.status === activeTab.value);
  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(p =>
      p.po_number?.toLowerCase().includes(q) ||
      p.vendor?.name?.toLowerCase().includes(q)
    );
  }
  return list;
});

const emptyStateIcon = computed(() => ({ draft: '✏️', approved: '✅', signed: '📝', all: '📭' }[activeTab.value] || '📭'));
const emptyStateTitle = computed(() => activeTab.value === 'all' ? 'Belum Ada Purchase Order' : `Tidak Ada PO ${currentTab.value?.label}`);
const emptyStateDesc = computed(() => 'Buat draf PO setelah tiket pengadaan disetujui oleh KD Sarpras.');

const loadPos = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/purchase-orders');
    pos.value = res.data?.data || res.data || [];
  } catch { pos.value = []; }
  finally { isLoading.value = false; }
};

const formatRp = (val) => {
  if (!val) return 'Rp 0';
  return 'Rp ' + Number(val).toLocaleString('id-ID');
};

const statusLabel = (s) => ({ draft: 'Draf', pending_approval: 'Menunggu', approved: 'Disetujui', signed_archived: 'Arsip' }[s] || s || '—');
const statusBadge = (s) => ({ draft: 'badge-secondary', pending_approval: 'badge-warning', approved: 'badge-success', signed_archived: 'badge-info' }[s] || 'badge-secondary');

onMounted(() => loadPos());
</script>

<style scoped>
.po-page { display: flex; flex-direction: column; gap: 1.25rem; }

/* Override page-header margin to prevent double-gap */
.page-header { margin-bottom: 0; }

/* ── Tabs ── */
.po-tabs {
  display: flex;
  gap: 0.25rem;
  border-bottom: 2px solid var(--border-color);
  padding-bottom: 0;
}

.po-tab {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-muted);
  background: none;
  border: none;
  border-bottom: 2.5px solid transparent;
  margin-bottom: -2px;
  cursor: pointer;
  border-radius: var(--radius-sm) var(--radius-sm) 0 0;
  transition: color 0.15s, border-color 0.15s, background 0.15s;
}

.po-tab:hover { color: var(--text-primary); background: var(--bg-page); }

.po-tab.active {
  color: var(--color-primary);
  border-bottom-color: var(--color-primary);
  font-weight: 700;
}

.po-tab-badge {
  background: var(--color-primary);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 999px;
  min-width: 18px;
  text-align: center;
}

/* ── Card Header ── */
.po-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  background: var(--bg-page);
  flex-wrap: wrap;
}

.po-card-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--text-primary);
}

/* ── Search ── */
.po-search-wrap { position: relative; display: flex; align-items: center; }
.po-search-icon { position: absolute; left: 0.75rem; width: 15px; height: 15px; color: var(--text-muted); pointer-events: none; }
.po-search-input {
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
.po-search-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
}

/* ── Empty ── */
.po-empty {
  padding: 4rem 2rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}
.po-empty-icon { font-size: 3.5rem; line-height: 1; margin-bottom: 0.5rem; }
.po-spinner {
  width: 40px; height: 40px;
  border: 3px solid var(--border-color);
  border-top-color: var(--color-primary);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
.po-empty-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.po-empty-text { font-size: 0.9rem; color: var(--text-muted); margin: 0; max-width: 360px; }

/* ── Table ── */
.po-table-wrap { overflow-x: auto; }
.po-row { transition: background 0.12s; }
.po-row:hover td { background: var(--bg-page) !important; }

.po-number {
  font-family: 'JetBrains Mono', monospace;
  font-weight: 700;
  font-size: 0.875rem;
  color: var(--color-primary);
}

.po-vendor { display: block; font-weight: 600; font-size: 0.875rem; color: var(--text-primary); }
.po-vendor-pic { display: block; font-size: 0.75rem; color: var(--text-muted); }

.po-ticket-id {
  font-family: monospace;
  font-weight: 700;
  color: var(--color-primary);
  cursor: pointer;
  text-decoration: underline;
  text-decoration-style: dotted;
}

.po-unit { font-size: 0.875rem; color: var(--text-secondary); }
.po-total { font-weight: 700; color: var(--text-primary); font-size: 0.9rem; }

.po-actions { display: flex; gap: 0.375rem; justify-content: center; flex-wrap: wrap; }

.btn-success {
  background: linear-gradient(135deg, #38a169, #276749);
  color: #fff;
  border: none;
}
.btn-success:hover { background: linear-gradient(135deg, #276749, #1c4532); }

/* ── Modal ── */
.po-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  backdrop-filter: blur(4px);
  z-index: 900;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.po-modal {
  background: var(--bg-card);
  border-radius: var(--radius-xl, 16px);
  width: 100%;
  max-width: 520px;
  box-shadow: var(--shadow-xl, 0 25px 50px rgba(0,0,0,0.25));
  overflow: hidden;
  animation: modal-in 0.2s ease;
}

.po-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.po-modal-title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); margin: 0; }

.po-modal-close {
  width: 30px; height: 30px;
  border: none;
  background: var(--bg-page);
  border-radius: 50%;
  cursor: pointer;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-muted);
  transition: background 0.15s;
}
.po-modal-close:hover { background: var(--border-color); }

.po-modal-body { padding: 1.5rem; }

.po-modal-info {
  display: flex;
  gap: 0.75rem;
  background: rgba(26,58,107,0.06);
  border-radius: var(--radius-md);
  padding: 0.875rem 1rem;
  border-left: 3px solid var(--color-primary);
}
.po-modal-info-icon { font-size: 1.125rem; flex-shrink: 0; }
.po-modal-info p { font-size: 0.875rem; color: var(--text-secondary); margin: 0; line-height: 1.5; }

.po-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-color);
  background: var(--bg-page);
}

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes modal-in { from { opacity: 0; transform: scale(0.95) translateY(-10px); } to { opacity: 1; transform: none; } }
</style>
