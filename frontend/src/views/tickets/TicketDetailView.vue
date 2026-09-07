<template>
  <div>
    <!-- Back button + PDF button -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.75rem;">
      <button class="btn btn-ghost btn-sm" @click="$router.back()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
      </button>
      <button v-if="ticket" class="btn btn-outline btn-sm" @click="downloadPdf" :disabled="isPdfLoading">
        <svg v-if="!isPdfLoading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        <span v-if="isPdfLoading" class="spinner-sm"></span>
        {{ isPdfLoading ? 'Menyiapkan PDF...' : 'Unduh PDF' }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="card" style="padding:3rem;text-align:center;color:var(--text-muted);">
      <div class="skeleton" style="height:300px;border-radius:12px;"></div>
    </div>

    <!-- Not Found -->
    <div v-else-if="!ticket" class="card" style="text-align:center;padding:3rem;">
      <p style="font-size:2rem;">🔍</p>
      <p style="font-weight:700;margin-top:0.75rem;">Tiket tidak ditemukan</p>
      <p style="color:var(--text-muted);margin-top:0.375rem;">ID tiket ini tidak ada atau Anda tidak memiliki akses.</p>
    </div>

    <!-- Ticket Detail -->
    <div v-else>
      <!-- Header Card -->
      <div class="card" style="margin-bottom:1.25rem;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
          <div>
            <div style="display:flex;align-items:center;gap:0.625rem;margin-bottom:0.5rem;flex-wrap:wrap;">
              <code style="font-size:0.8rem;color:var(--text-muted);background:var(--bg-page);padding:0.2rem 0.5rem;border-radius:6px;">
                #{{ String(ticket.id).padStart(4,'0') }}
              </code>
              <span class="badge" :class="typeBadge(ticket.procedure_type)">{{ formatType(ticket.procedure_type) }}</span>
              <span class="badge" :class="urgencyBadge(ticket.urgency_level)">{{ formatUrgency(ticket.urgency_level) }}</span>
            </div>
            <h1 style="font-size:1.375rem;font-weight:800;color:var(--text-primary);">{{ ticket.title }}</h1>
            <div style="display:flex;align-items:center;gap:1.25rem;margin-top:0.625rem;flex-wrap:wrap;">
              <span style="font-size:0.8rem;color:var(--text-muted);">
                📅 {{ formatDate(ticket.created_at) }}
              </span>
              <span style="font-size:0.8rem;color:var(--text-muted);">
                👤 {{ ticket.user?.name || '—' }}
              </span>
              <span style="font-size:0.8rem;color:var(--text-muted);" v-if="ticket.unit">
                🏢 {{ ticket.unit.name }}
              </span>
            </div>
          </div>
          <div style="text-align:right;">
            <p style="font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-muted);margin-bottom:0.375rem;">Status</p>
            <span class="badge" :class="statusBadge(ticket.status)" style="font-size:0.875rem;padding:0.375rem 1rem;">{{ formatStatus(ticket.status) }}</span>
          </div>
        </div>

        <!-- Routing Path -->
        <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border-color);display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
          <span style="font-size:0.75rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);letter-spacing:0.05em;">Jalur Routing:</span>
          <span class="badge badge-info">{{ formatRouting(ticket.routing_path) }}</span>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="detail-grid">
        <!-- Left: Details + Timeline -->
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

          <!-- Detail Info -->
          <div class="card">
            <div class="card-header">
              <h2 style="font-size:0.95rem;font-weight:700;">📋 Informasi Tiket</h2>
            </div>
            <dl class="detail-list">
              <div class="detail-row">
                <dt>Ruangan / Lokasi</dt>
                <dd>{{ ticket.location_name || '—' }} <span v-if="ticket.specific_location" style="color:var(--text-muted);">({{ ticket.specific_location }})</span></dd>
              </div>
              <div class="detail-row" v-if="ticket.problem_cause">
                <dt>Deskripsi / Justifikasi</dt>
                <dd style="white-space:pre-wrap;">{{ ticket.problem_cause }}</dd>
              </div>
              <div class="detail-row" v-if="ticket.estimated_cost > 0">
                <dt>Estimasi Biaya</dt>
                <dd style="font-weight:700;color:var(--color-primary);">{{ formatRp(ticket.estimated_cost) }}</dd>
              </div>
              <div class="detail-row" v-if="ticket.actual_cost">
                <dt>Biaya Aktual</dt>
                <dd style="font-weight:700;">{{ formatRp(ticket.actual_cost) }}</dd>
              </div>
              <!-- Mutasi specific -->
              <template v-if="ticket.procedure_type === 'mutasi'">
                <div class="detail-row">
                  <dt>Unit Tujuan</dt>
                  <dd>{{ ticket.targetUnit?.name || '—' }}</dd>
                </div>
                <div class="detail-row">
                  <dt>Ruangan / Lokasi Tujuan</dt>
                  <dd>{{ ticket.target_location_name || '—' }}</dd>
                </div>
              </template>
              <!-- Pemutihan specific -->
              <template v-if="ticket.procedure_type === 'pemutihan'">
                <div class="detail-row">
                  <dt>Tindakan Disposal</dt>
                  <dd class="capitalize">{{ ticket.disposal_action?.replace(/_/g,' ') || '—' }}</dd>
                </div>
                <div class="detail-row" v-if="ticket.estimated_salvage_value > 0">
                  <dt>Estimasi Nilai Sisa</dt>
                  <dd>{{ formatRp(ticket.estimated_salvage_value) }}</dd>
                </div>
              </template>
            </dl>
          </div>

          <!-- Items Table (Pengadaan / Mutasi) -->
          <div class="card" v-if="ticket.items?.length > 0">
            <div class="card-header">
              <h2 style="font-size:0.95rem;font-weight:700;">{{ ticket.procedure_type === 'pengadaan' ? '🛒 Daftar Item Belanja' : '📦 Daftar Inventaris' }}</h2>
            </div>
            <div class="table-wrapper">
              <table class="data-table">
                <thead>
                  <tr>
                    <th v-if="ticket.procedure_type === 'mutasi'">Kode Aset</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Satuan</th>
                    <th v-if="ticket.procedure_type === 'pengadaan'">Harga Satuan</th>
                    <th v-if="ticket.procedure_type === 'pengadaan'">Subtotal</th>
                    <th v-if="ticket.procedure_type === 'mutasi'">Kondisi</th>
                    <th>Catatan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in ticket.items" :key="item.id">
                    <td v-if="ticket.procedure_type === 'mutasi'"><code style="font-size:0.78rem;">{{ item.inventory_code || '-' }}</code></td>
                    <td style="font-weight:500;">{{ item.item_description }}</td>
                    <td>{{ item.quantity }}</td>
                    <td>{{ item.unit_metric }}</td>
                    <td v-if="ticket.procedure_type === 'pengadaan'">{{ formatRp(item.estimated_unit_price) }}</td>
                    <td v-if="ticket.procedure_type === 'pengadaan'" style="font-weight:700;">{{ formatRp(item.estimated_subtotal) }}</td>
                    <td v-if="ticket.procedure_type === 'mutasi'"><span class="badge" :class="item.condition_status === 'baik' ? 'badge-success' : 'badge-warning'">{{ item.condition_status === 'baik' ? 'Baik' : 'Rusak Ringan' }}</span></td>
                    <td style="color:var(--text-muted);">{{ item.item_notes || '—' }}</td>
                  </tr>
                </tbody>
                <tfoot v-if="ticket.procedure_type === 'pengadaan'">
                  <tr>
                    <td colspan="4" style="padding:0.75rem 1rem;font-weight:700;color:var(--text-secondary);">Total Estimasi</td>
                    <td style="padding:0.75rem 1rem;font-weight:800;color:var(--color-primary);">{{ formatRp(ticket.estimated_cost) }}</td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- Photos -->
          <div class="card" v-if="ticket.photos?.length > 0">
            <div class="card-header">
              <h2 style="font-size:0.95rem;font-weight:700;">📸 Lampiran Foto</h2>
            </div>
            <div class="photos-grid">
              <a v-for="photo in ticket.photos" :key="photo.id" :href="`/storage/${photo.file_path}`" target="_blank" class="photo-thumb">
                <img :src="`/storage/${photo.file_path}`" :alt="photo.caption || 'Foto tiket'" />
                <span v-if="photo.caption" class="photo-caption">{{ photo.caption }}</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Right: Timeline + Approval -->
        <div style="display:flex;flex-direction:column;gap:1.25rem;">

          <!-- Approval Status -->
          <div class="card">
            <div class="card-header">
              <h2 style="font-size:0.95rem;font-weight:700;">✅ Status Persetujuan</h2>
            </div>
            <div class="approval-steps">
              <!-- ASP Review -->
              <div class="approval-step" :class="aspStepClass">
                <div class="approval-step-icon">
                  <svg v-if="ticket.asp_approval_status === 'approved'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <svg v-else-if="ticket.asp_approval_status === 'rejected'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  <svg v-else-if="ticket.asp_approval_status === 'bypass'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span v-else style="font-size:0.7rem;font-weight:700;">ASP</span>
                </div>
                <div class="approval-step-info">
                  <p class="approval-step-title">Review ASP Wilayah</p>
                  <p class="approval-step-status">{{ formatApprovalStatus(ticket.asp_approval_status) }}</p>
                  <p v-if="ticket.asp_approval_at" class="approval-step-date">{{ formatDate(ticket.asp_approval_at) }}</p>
                  <p v-if="ticket.asp_notes" class="approval-step-notes">{{ ticket.asp_notes }}</p>
                </div>
              </div>

              <!-- KD Approval -->
              <div class="approval-step" :class="kdStepClass">
                <div class="approval-step-icon">
                  <svg v-if="ticket.kd_approval_status === 'approved'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <svg v-else-if="ticket.kd_approval_status === 'rejected'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  <span v-else style="font-size:0.7rem;font-weight:700;">KD</span>
                </div>
                <div class="approval-step-info">
                  <p class="approval-step-title">Approval KD Sarpras</p>
                  <p class="approval-step-status">{{ formatApprovalStatus(ticket.kd_approval_status) }}</p>
                  <p v-if="ticket.kd_approval_at" class="approval-step-date">{{ formatDate(ticket.kd_approval_at) }}</p>
                  <p v-if="ticket.kd_notes" class="approval-step-notes">{{ ticket.kd_notes }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Audit Log Timeline -->
          <div class="card">
            <div class="card-header">
              <h2 style="font-size:0.95rem;font-weight:700;">🕐 Riwayat Aktivitas</h2>
            </div>
            <div v-if="timeline.length === 0" style="text-align:center;padding:2rem;color:var(--text-muted);">
              <p>Belum ada riwayat aktivitas.</p>
            </div>
            <ol class="timeline" v-else>
              <li v-for="(log, idx) in timeline" :key="log.id" class="timeline-item">
                <div class="timeline-dot" :class="actionColor(log.action)"></div>
                <div class="timeline-content">
                  <p class="timeline-action">{{ log.description }}</p>
                  <p class="timeline-meta">
                    <span>{{ log.role?.replace(/_/g,' ') }}</span>
                    <span>·</span>
                    <span>{{ formatDate(log.created_at) }}</span>
                  </p>
                </div>
              </li>
            </ol>
          </div>

          <!-- PO & BAST Info -->
          <div class="card" v-if="ticket.purchaseOrders?.length > 0 || ticket.bastDocument">
            <div class="card-header">
              <h2 style="font-size:0.95rem;font-weight:700;">📄 Dokumen PO & BAST</h2>
            </div>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
              <div v-for="po in ticket.purchaseOrders" :key="po.id" class="doc-item">
                <div>
                  <p style="font-weight:700;font-size:0.875rem;">{{ po.po_number || 'Draft PO' }}</p>
                  <p style="font-size:0.78rem;color:var(--text-muted);">{{ formatRp(po.grand_total) }} · {{ po.vendor_name_snapshot }}</p>
                </div>
                <span class="badge" :class="po.po_status === 'completed' ? 'badge-success' : 'badge-warning'">{{ po.po_status }}</span>
              </div>
              <div v-if="ticket.bastDocument" class="doc-item">
                <div>
                  <p style="font-weight:700;font-size:0.875rem;">BAST {{ formatType(ticket.bastDocument.bast_type) }}</p>
                  <p style="font-size:0.78rem;color:var(--text-muted);">{{ formatDate(ticket.bastDocument.handover_date) }}</p>
                </div>
                <span class="badge" :class="ticket.bastDocument.status === 'signed_archived' ? 'badge-success' : 'badge-warning'">{{ ticket.bastDocument.status }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useTicketStore } from '@/stores/ticket';
import api from '@/services/api';

const route = useRoute();
const ticketStore = useTicketStore();

const ticket = ref(null);
const timeline = ref([]);
const isLoading = ref(true);
const isPdfLoading = ref(false);

onMounted(async () => {
  const id = route.params.id;
  try {
    ticket.value = await ticketStore.fetchTicket(id);
    timeline.value = await ticketStore.fetchAuditTimeline(id);
  } catch {
    ticket.value = null;
  } finally {
    isLoading.value = false;
  }
});

async function downloadPdf() {
  if (!ticket.value) return;
  isPdfLoading.value = true;
  try {
    // Direct browser download via token-authenticated fetch
    const token = localStorage.getItem('sanctum_token');
    const res = await fetch(`/api/v1/tickets/${ticket.value.id}/pdf`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/pdf' },
    });
    if (!res.ok) throw new Error('Gagal mengunduh PDF');
    const blob = await res.blob();
    const url  = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Tiket-SIPARAS-${String(ticket.value.id).padStart(4,'0')}.pdf`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  } catch {
    alert('Gagal mengunduh PDF. Coba lagi.');
  } finally {
    isPdfLoading.value = false;
  }
}

// Formatters
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }) : '—';
const formatRp = (v) => v != null ? 'Rp ' + Number(v).toLocaleString('id-ID') : 'Rp 0';
const formatType = (t) => ({ perbaikan:'Perbaikan', pengadaan:'Pengadaan', mutasi:'Mutasi', pemutihan:'Pemutihan', pemeliharaan:'Pemeliharaan' }[t] || t);
const formatUrgency = (u) => ({ rendah:'Rendah', sedang:'Sedang', tinggi_darurat:'🔴 Darurat' }[u] || u);
const urgencyBadge = (u) => ({ rendah:'badge-info', sedang:'badge-warning', tinggi_darurat:'badge-error' }[u] || 'badge-neutral');
const typeBadge = () => 'badge-neutral';
const formatStatus = (s) => ({ submitted_to_asp:'Menunggu ASP', submitted_to_kd:'Menunggu KD', revision_required:'Perlu Revisi', approved:'Disetujui', in_progress:'Diproses', signed_archived:'Selesai', completed:'Selesai', rejected:'Ditolak' }[s] || s);
const statusBadge = (s) => ({ approved:'badge-success', completed:'badge-success', signed_archived:'badge-success', rejected:'badge-error', revision_required:'badge-warning', submitted_to_asp:'badge-info', submitted_to_kd:'badge-info', in_progress:'badge-info' }[s] || 'badge-neutral');
const formatRouting = (r) => ({ jalur_1_sekolah:'Jalur 1 — Via ASP', jalur_2_kantor_pusat:'Jalur 2 — Kantor Pusat', jalur_3_temuan_asp:'Jalur 3 — Temuan ASP', jalur_4_direct_kd:'Jalur 4 — Direct KD' }[r] || r || '—');
const formatApprovalStatus = (s) => ({ pending:'⏳ Menunggu', approved:'✅ Disetujui', revision_required:'✏️ Perlu Revisi', rejected:'❌ Ditolak', bypass:'⏭️ Bypass' }[s] || s);
const actionColor = (action) => {
  if (['ASP_APPROVE','KD_APPROVE','COMPLETED'].includes(action)) return 'dot-green';
  if (['ASP_REJECT','KD_REJECT'].includes(action)) return 'dot-red';
  if (['REVISE'].includes(action)) return 'dot-yellow';
  return 'dot-blue';
};

const aspStepClass = computed(() => {
  if (!ticket.value) return '';
  const s = ticket.value.asp_approval_status;
  if (s === 'approved' || s === 'bypass') return 'step-done';
  if (s === 'rejected') return 'step-rejected';
  if (s === 'revision_required') return 'step-revision';
  return 'step-pending';
});
const kdStepClass = computed(() => {
  if (!ticket.value) return '';
  const s = ticket.value.kd_approval_status;
  if (s === 'approved' || s === 'bypass') return 'step-done';
  if (s === 'rejected') return 'step-rejected';
  if (s === 'revision_required') return 'step-revision';
  return 'step-pending';
});
</script>

<style scoped>
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 1.25rem;
  align-items: flex-start;
}
@media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } }

/* Detail list */
.detail-list { display: flex; flex-direction: column; gap: 0.875rem; }
.detail-row { display: grid; grid-template-columns: 160px 1fr; gap: 0.5rem; font-size: 0.875rem; }
.detail-row dt { font-weight: 600; color: var(--text-muted); }
.detail-row dd { color: var(--text-primary); }
@media (max-width: 500px) { .detail-row { grid-template-columns: 1fr; } }

/* Photos */
.photos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.625rem; }
.photo-thumb { position: relative; border-radius: 10px; overflow: hidden; aspect-ratio: 1; display: block; background: var(--bg-page); border: 1px solid var(--border-color); }
.photo-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.2s; }
.photo-thumb:hover img { transform: scale(1.05); }
.photo-caption { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: #fff; font-size: 0.65rem; padding: 0.25rem 0.375rem; }

/* Approval steps */
.approval-steps { display: flex; flex-direction: column; gap: 1rem; }
.approval-step {
  display: flex;
  align-items: flex-start;
  gap: 0.875rem;
  padding: 0.875rem;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--border-color);
  background: var(--bg-page);
  transition: all 0.2s;
}
.step-done     { border-color: var(--color-success-border); background: var(--color-success-bg); }
.step-rejected { border-color: var(--color-error-border);   background: var(--color-error-bg); }
.step-revision { border-color: var(--color-warning-border); background: var(--color-warning-bg); }
.approval-step-icon {
  width: 36px; height: 36px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-card);
  border: 2px solid var(--border-color);
  flex-shrink: 0;
  font-size: 0.7rem;
  font-weight: 700;
  color: var(--text-muted);
}
.step-done .approval-step-icon     { background: var(--color-success-bg); border-color: var(--color-success-border); color: var(--color-success); }
.step-rejected .approval-step-icon { background: var(--color-error-bg);   border-color: var(--color-error-border);   color: var(--color-error); }
.step-revision .approval-step-icon { background: var(--color-warning-bg); border-color: var(--color-warning-border); color: var(--color-warning); }
.approval-step-icon svg { width: 16px; height: 16px; }
.approval-step-title  { font-size: 0.875rem; font-weight: 700; color: var(--text-primary); }
.approval-step-status { font-size: 0.8rem; color: var(--text-secondary); margin-top: 2px; }
.approval-step-date   { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }
.approval-step-notes  { font-size: 0.78rem; color: var(--text-secondary); margin-top: 4px; font-style: italic; background: rgba(0,0,0,0.04); padding: 0.25rem 0.5rem; border-radius: 4px; }

/* Timeline */
.timeline { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0; }
.timeline-item { display: flex; gap: 0.75rem; padding-bottom: 1rem; position: relative; }
.timeline-item::before { content: ''; position: absolute; left: 11px; top: 24px; bottom: 0; width: 2px; background: var(--border-color); }
.timeline-item:last-child::before { display: none; }
.timeline-dot { width: 24px; height: 24px; border-radius: 50%; flex-shrink: 0; border: 3px solid var(--bg-card); box-shadow: 0 0 0 2px var(--border-color); margin-top: 2px; }
.dot-green  { background: var(--color-success); box-shadow: 0 0 0 2px var(--color-success-border); }
.dot-red    { background: var(--color-error);   box-shadow: 0 0 0 2px var(--color-error-border); }
.dot-yellow { background: var(--color-warning); box-shadow: 0 0 0 2px var(--color-warning-border); }
.dot-blue   { background: var(--color-info);    box-shadow: 0 0 0 2px var(--color-info-border); }
.timeline-action { font-size: 0.875rem; font-weight: 600; color: var(--text-primary); }
.timeline-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; display: flex; gap: 0.375rem; text-transform: capitalize; }

/* Doc items */
.doc-item { display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0.875rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-page); }
</style>
