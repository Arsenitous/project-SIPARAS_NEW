<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Laporan Eksekutif</h1>
        <p class="page-subtitle">Rekapitulasi tiket dan kinerja operasional SIPARAS per periode.</p>
      </div>
      <div style="display:flex;gap:0.75rem;align-items:center;">
        <select v-model="filterYear" class="filter-select" @change="load">
          <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
        </select>
        <select v-model="filterMonth" class="filter-select" @change="load">
          <option value="">Semua Bulan</option>
          <option v-for="(name, idx) in monthNames" :key="idx+1" :value="idx+1">{{ name }}</option>
        </select>
        <button class="btn btn-outline filter-refresh-btn" @click="load" title="Refresh">
          <span style="font-size:1rem;">↺</span> Refresh
        </button>
        <button class="btn btn-primary btn-sm" disabled title="Segera hadir">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Ekspor Excel
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem;">
      <div v-for="i in 4" :key="i" class="skeleton" style="height:110px;border-radius:12px;"></div>
    </div>

    <!-- ── Summary Cards ── -->
    <div v-else class="report-summary-grid">
      <div class="report-stat-card report-stat-blue">
        <div class="report-stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 9a2 2 0 012-2h16a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9z"/><path d="M15 12h.01"/></svg>
        </div>
        <div>
          <div class="report-stat-num">{{ summary.total }}</div>
          <div class="report-stat-label">Total Tiket</div>
        </div>
      </div>
      <div class="report-stat-card report-stat-green">
        <div class="report-stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <div class="report-stat-num">{{ summary.approved }}</div>
          <div class="report-stat-label">Selesai / Approved</div>
          <div class="report-stat-rate" v-if="summary.total > 0">{{ pctApproved }}%</div>
        </div>
      </div>
      <div class="report-stat-card report-stat-yellow">
        <div class="report-stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
          <div class="report-stat-num">{{ summary.pending }}</div>
          <div class="report-stat-label">Menunggu Proses</div>
        </div>
      </div>
      <div class="report-stat-card report-stat-red">
        <div class="report-stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
          <div class="report-stat-num">{{ summary.rejected }}</div>
          <div class="report-stat-label">Ditolak</div>
          <div class="report-stat-rate" v-if="summary.total > 0">{{ pctRejected }}%</div>
        </div>
      </div>
    </div>

    <!-- ── Charts Row ── -->
    <div v-if="!isLoading" class="report-charts-row">

      <!-- Tiket per Prosedur -->
      <div class="card report-panel">
        <div class="report-panel-title">📋 Tiket per Prosedur</div>
        <div v-if="byType.length === 0" class="report-empty">Belum ada data</div>
        <div v-else class="bar-chart">
          <div v-for="item in byType" :key="item.procedure_type" class="bar-row">
            <div class="bar-label">{{ formatType(item.procedure_type) }}</div>
            <div class="bar-track">
              <div class="bar-fill bar-blue" :style="{ width: barPct(item.total, maxByType) + '%' }"></div>
            </div>
            <div class="bar-value">{{ item.total }}</div>
          </div>
        </div>
      </div>

      <!-- Tiket per Urgensi -->
      <div class="card report-panel">
        <div class="report-panel-title">🔥 Distribusi Urgensi</div>
        <div v-if="byUrgency.length === 0" class="report-empty">Belum ada data</div>
        <div v-else class="urgency-list">
          <div v-for="item in byUrgency" :key="item.urgency_level" class="urgency-row">
            <span :class="['urgency-badge', 'urgency-' + item.urgency_level]">{{ formatUrgency(item.urgency_level) }}</span>
            <div class="bar-track">
              <div :class="['bar-fill', urgencyBarClass(item.urgency_level)]" :style="{ width: barPct(item.total, maxByUrgency) + '%' }"></div>
            </div>
            <span class="bar-value">{{ item.total }}</span>
          </div>
        </div>
      </div>

    </div>

    <!-- ── Per Unit Table ── -->
    <div v-if="!isLoading" class="card" style="margin-top:1.25rem;">
      <div class="report-panel-title" style="padding:1rem 1.25rem 0;">🏫 Rekapitulasi per Unit</div>
      <div v-if="byUnit.length === 0" class="report-empty" style="padding:2rem;">Belum ada tiket</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th style="width:40px;">#</th>
            <th>Unit</th>
            <th style="width:130px;">Kode</th>
            <th style="width:100px;">Total Tiket</th>
            <th>Proporsi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, idx) in byUnit" :key="item.unit_id">
            <td style="color:var(--text-muted);font-size:0.8rem;text-align:center;">{{ idx+1 }}</td>
            <td style="font-weight:600;font-size:0.875rem;">{{ item.unit?.name || '—' }}</td>
            <td><span class="unit-code-sm">{{ item.unit?.unit_code || '—' }}</span></td>
            <td style="font-weight:700;font-size:0.95rem;color:var(--color-primary);">{{ item.total }}</td>
            <td>
              <div class="inline-bar-wrap">
                <div class="inline-bar" :style="{ width: barPct(item.total, byUnit[0]?.total) + '%' }"></div>
                <span class="inline-bar-pct">{{ barPct(item.total, summary.total) }}%</span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── Trend Bulanan ── -->
    <div v-if="!isLoading && byMonth.length > 0" class="card" style="margin-top:1.25rem;">
      <div class="report-panel-title" style="padding:1rem 1.25rem 0.5rem;">📈 Tren Bulanan {{ filterYear }}</div>
      <div class="month-trend">
        <div v-for="m in byMonth" :key="m.month" class="month-col">
          <div class="month-bars">
            <div class="month-bar-approved" :style="{ height: barPct(m.approved, maxMonthTotal) + '%' }" :title="`Approved: ${m.approved}`"></div>
            <div class="month-bar-total" :style="{ height: barPct(m.total, maxMonthTotal) + '%' }" :title="`Total: ${m.total}`"></div>
          </div>
          <div class="month-name">{{ shortMonth(m.month) }}</div>
          <div class="month-num">{{ m.total }}</div>
        </div>
      </div>
      <div class="month-legend">
        <span class="legend-dot" style="background:#16a34a;"></span> Approved/Selesai &nbsp;
        <span class="legend-dot" style="background:var(--color-primary);opacity:0.4;"></span> Total Tiket
      </div>
    </div>

    <!-- Empty state if no data at all -->
    <div v-if="!isLoading && summary.total === 0" class="card report-empty-state">
      <div style="font-size:3rem;">📊</div>
      <p style="font-weight:700;color:var(--text-secondary);margin-top:0.75rem;">Belum ada data tiket</p>
      <p style="color:var(--text-muted);font-size:0.875rem;">Data rekapitulasi akan muncul setelah tiket mulai diajukan dalam sistem.</p>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';

const filterYear  = ref(new Date().getFullYear());
const filterMonth = ref('');
const isLoading   = ref(true);

const summary   = ref({ total: 0, approved: 0, rejected: 0, pending: 0, totalCost: 0 });
const byType    = ref([]);
const byUnit    = ref([]);
const byMonth   = ref([]);
const byUrgency = ref([]);

const yearOptions = computed(() => {
  const cur = new Date().getFullYear();
  return Array.from({ length: 5 }, (_, i) => cur - i);
});

const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

const pctApproved = computed(() => summary.value.total ? Math.round(summary.value.approved / summary.value.total * 100) : 0);
const pctRejected = computed(() => summary.value.total ? Math.round(summary.value.rejected / summary.value.total * 100) : 0);

const maxByType    = computed(() => Math.max(...byType.value.map(i => i.total), 1));
const maxByUrgency = computed(() => Math.max(...byUrgency.value.map(i => i.total), 1));
const maxMonthTotal = computed(() => Math.max(...byMonth.value.map(m => m.total), 1));

function barPct(val, max) { return max > 0 ? Math.round((val / max) * 100) : 0; }
function shortMonth(n) { return ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'][n - 1] || n; }

function formatType(t) {
  return { perbaikan:'Perbaikan', pengadaan:'Pengadaan', mutasi:'Mutasi', pemutihan:'Pemutihan', pemeliharaan:'Pemeliharaan' }[t] || t;
}
function formatUrgency(u) {
  return { low:'Rendah', medium:'Sedang', high:'Tinggi', critical:'Kritis' }[u] || u;
}
function urgencyBarClass(u) {
  return { low:'bar-green', medium:'bar-yellow', high:'bar-orange', critical:'bar-red' }[u] || 'bar-blue';
}

async function load() {
  isLoading.value = true;
  try {
    const params = { year: filterYear.value };
    if (filterMonth.value) params.month = filterMonth.value;
    const res = await api.get('/reports/recap', { params });
    const d = res.data;
    summary.value   = d.summary   || {};
    byType.value    = d.by_type   || [];
    byUnit.value    = d.by_unit   || [];
    byMonth.value   = d.by_month  || [];
    byUrgency.value = d.by_urgency || [];
  } catch {
    // silently fail
  } finally {
    isLoading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
/* ── Summary stats ── */
.report-summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
.report-stat-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm); }
.report-stat-icon { width: 48px; height: 48px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.report-stat-icon svg { width: 22px; height: 22px; }
.report-stat-num   { font-size: 2rem; font-weight: 800; line-height: 1; }
.report-stat-label { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.2rem; }
.report-stat-rate  { font-size: 0.72rem; font-weight: 700; margin-top: 0.15rem; opacity: 0.7; }

.report-stat-blue  .report-stat-icon { background: rgba(26,58,107,0.1); color: var(--color-primary); }
.report-stat-blue  .report-stat-num  { color: var(--color-primary); }
.report-stat-green .report-stat-icon { background: rgba(22,163,74,0.1); color: #16a34a; }
.report-stat-green .report-stat-num  { color: #16a34a; }
.report-stat-yellow .report-stat-icon { background: rgba(217,119,6,0.1); color: #d97706; }
.report-stat-yellow .report-stat-num  { color: #d97706; }
.report-stat-red   .report-stat-icon { background: rgba(220,38,38,0.1); color: #dc2626; }
.report-stat-red   .report-stat-num  { color: #dc2626; }

/* ── Charts row ── */
.report-charts-row { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.report-panel { padding: 1rem 1.25rem; }
.report-panel-title { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; }
.report-empty { text-align: center; padding: 2rem; color: var(--text-muted); font-style: italic; font-size: 0.875rem; }

/* ── Bar chart ── */
.bar-chart   { display: flex; flex-direction: column; gap: 0.625rem; }
.bar-row     { display: flex; align-items: center; gap: 0.625rem; }
.bar-label   { width: 100px; font-size: 0.78rem; font-weight: 600; color: var(--text-secondary); text-align: right; flex-shrink: 0; }
.bar-track   { flex: 1; height: 10px; background: var(--bg-page); border-radius: 999px; overflow: hidden; }
.bar-fill    { height: 100%; border-radius: 999px; transition: width 0.6s cubic-bezier(0.34,1.56,0.64,1); }
.bar-value   { width: 28px; text-align: right; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); flex-shrink: 0; }
.bar-blue   { background: var(--color-primary); }
.bar-green  { background: #16a34a; }
.bar-yellow { background: #d97706; }
.bar-orange { background: #ea580c; }
.bar-red    { background: #dc2626; }

/* ── Urgency ── */
.urgency-list { display: flex; flex-direction: column; gap: 0.75rem; }
.urgency-row  { display: flex; align-items: center; gap: 0.75rem; }
.urgency-badge { font-size: 0.72rem; font-weight: 700; border-radius: 999px; padding: 0.2rem 0.65rem; white-space: nowrap; flex-shrink: 0; }
.urgency-low      { background: rgba(22,163,74,0.1); color: #16a34a; }
.urgency-medium   { background: rgba(217,119,6,0.1); color: #d97706; }
.urgency-high     { background: rgba(234,88,12,0.1); color: #ea580c; }
.urgency-critical { background: rgba(220,38,38,0.1); color: #dc2626; }

/* ── Inline bar (per unit) ── */
.inline-bar-wrap { display: flex; align-items: center; gap: 0.625rem; }
.inline-bar { height: 8px; background: var(--color-primary); border-radius: 999px; min-width: 4px; opacity: 0.7; transition: width 0.5s ease; }
.inline-bar-pct { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; white-space: nowrap; }
.unit-code-sm { font-family: monospace; font-size: 0.78rem; font-weight: 700; color: var(--color-primary); background: rgba(26,58,107,0.07); padding: 0.15rem 0.45rem; border-radius: 5px; }

/* ── Month trend ── */
.month-trend { display: flex; align-items: flex-end; gap: 0.75rem; padding: 0.5rem 1.25rem 0; height: 120px; }
.month-col   { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.25rem; height: 100%; justify-content: flex-end; }
.month-bars  { display: flex; align-items: flex-end; gap: 2px; width: 100%; justify-content: center; height: 80px; }
.month-bar-total    { flex: 1; background: var(--color-primary); opacity: 0.25; border-radius: 4px 4px 0 0; min-height: 3px; transition: height 0.5s ease; max-width: 16px; }
.month-bar-approved { flex: 1; background: #16a34a; border-radius: 4px 4px 0 0; min-height: 3px; transition: height 0.5s ease; max-width: 16px; }
.month-name { font-size: 0.68rem; color: var(--text-muted); text-align: center; }
.month-num  { font-size: 0.72rem; font-weight: 700; color: var(--text-secondary); }
.month-legend { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.25rem 1rem; font-size: 0.75rem; color: var(--text-muted); }
.legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

/* ── Empty state ── */
.report-empty-state { text-align: center; padding: 3rem; margin-top: 1.25rem; }

/* ── Filter bar ── */
.filter-select {
  height: 40px; width: auto; min-width: 120px;
  padding: 0 2rem 0 0.875rem;
  font-size: 0.875rem; font-family: inherit;
  background: var(--bg-input); border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md); color: var(--text-primary);
  outline: none; cursor: pointer; appearance: none; flex-shrink: 0;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 0.5rem center;
}
.filter-refresh-btn { height: 40px; display: flex; align-items: center; gap: 0.375rem; padding: 0 1rem; }

@media (max-width: 900px) {
  .report-summary-grid { grid-template-columns: repeat(2,1fr); }
  .report-charts-row   { grid-template-columns: 1fr; }
}
</style>
