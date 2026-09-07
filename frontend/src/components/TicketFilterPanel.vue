<template>
  <div class="filter-panel" :class="{ 'filter-panel--expanded': isExpanded }">
    <!-- Header Bar -->
    <div class="filter-header">
      <div class="filter-search-wrap">
        <svg class="filter-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
          v-model="filters.search"
          type="text"
          class="form-input filter-search-input"
          placeholder="Cari judul tiket..."
          @input="$emit('change')"
        />
      </div>

      <button
        class="btn btn-outline btn-sm filter-toggle-btn"
        @click="isExpanded = !isExpanded"
        :class="{ 'has-filters': isFiltered }"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="4" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="12" y2="18"/></svg>
        Filter
        <span v-if="isFiltered" class="filter-active-dot"></span>
      </button>

      <button v-if="isFiltered" class="btn btn-ghost btn-sm" @click="onReset">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
        Reset
      </button>

      <!-- Sort control -->
      <div style="display:flex;align-items:center;gap:0.5rem;margin-left:auto;">
        <span style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;">Urut:</span>
        <select v-model="filters.sort_by" class="form-input" style="width:auto;font-size:0.8rem;padding:0.4rem 0.6rem;" @change="$emit('change')">
          <option value="created_at">Tanggal</option>
          <option value="urgency_level">Urgensi</option>
          <option value="status">Status</option>
          <option value="title">Judul</option>
        </select>
        <button class="btn btn-outline btn-sm btn-icon" @click="toggleSortDir" :title="filters.sort_dir === 'asc' ? 'Ascending' : 'Descending'">
          <svg v-if="filters.sort_dir === 'desc'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
          <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
        </button>
      </div>
    </div>

    <!-- Expanded Filter Rows -->
    <transition name="slide-up">
      <div v-if="isExpanded" class="filter-body">
        <!-- Status chips -->
        <div class="filter-group">
          <span class="filter-group-label">Status:</span>
          <div class="filter-chips">
            <button
              v-for="s in statusOptions"
              :key="s.value"
              class="filter-chip"
              :class="{ active: filters.status === s.value }"
              @click="toggle('status', s.value)"
            >{{ s.label }}</button>
          </div>
        </div>

        <!-- Procedure type chips -->
        <div class="filter-group">
          <span class="filter-group-label">Tipe:</span>
          <div class="filter-chips">
            <button
              v-for="t in typeOptions"
              :key="t.value"
              class="filter-chip"
              :class="{ active: filters.procedure_type === t.value }"
              @click="toggle('procedure_type', t.value)"
            >{{ t.label }}</button>
          </div>
        </div>

        <!-- Urgency chips -->
        <div class="filter-group">
          <span class="filter-group-label">Urgensi:</span>
          <div class="filter-chips">
            <button
              v-for="u in urgencyOptions"
              :key="u.value"
              class="filter-chip"
              :class="[{ active: filters.urgency_level === u.value }, u.color]"
              @click="toggle('urgency_level', u.value)"
            >{{ u.label }}</button>
          </div>
        </div>

        <!-- Date range -->
        <div class="filter-group filter-group--dates">
          <span class="filter-group-label">Tanggal:</span>
          <div style="display:flex;align-items:center;gap:0.5rem;">
            <input v-model="filters.date_from" type="date" class="form-input" style="font-size:0.8rem;padding:0.4rem 0.6rem;" @change="$emit('change')" />
            <span style="color:var(--text-muted);font-size:0.875rem;">s/d</span>
            <input v-model="filters.date_to" type="date" class="form-input" style="font-size:0.8rem;padding:0.4rem 0.6rem;" @change="$emit('change')" />
          </div>
        </div>
      </div>
    </transition>

    <!-- Active filter summary pills -->
    <div v-if="isFiltered && !isExpanded" class="active-filters-summary">
      <span v-if="filters.status" class="active-filter-pill" @click="filters.status = ''; $emit('change')">
        Status: {{ statusOptions.find(s => s.value === filters.status)?.label }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:10px;height:10px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </span>
      <span v-if="filters.procedure_type" class="active-filter-pill" @click="filters.procedure_type = ''; $emit('change')">
        Tipe: {{ typeOptions.find(t => t.value === filters.procedure_type)?.label }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:10px;height:10px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </span>
      <span v-if="filters.urgency_level" class="active-filter-pill" @click="filters.urgency_level = ''; $emit('change')">
        Urgensi: {{ urgencyOptions.find(u => u.value === filters.urgency_level)?.label }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:10px;height:10px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </span>
      <span v-if="filters.date_from || filters.date_to" class="active-filter-pill" @click="filters.date_from = ''; filters.date_to = ''; $emit('change')">
        {{ filters.date_from || '...' }} — {{ filters.date_to || '...' }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:10px;height:10px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  filters:    { type: Object, required: true },
  isFiltered: { type: Boolean, default: false },
});
const emit = defineEmits(['change', 'reset']);

const isExpanded = ref(false);

const filters = props.filters; // two-way via v-model on parent

const statusOptions = [
  { value: 'submitted_to_asp', label: '⏳ Menunggu ASP' },
  { value: 'submitted_to_kd',  label: '⏳ Menunggu KD' },
  { value: 'revision_required',label: '✏️ Perlu Revisi' },
  { value: 'approved',         label: '✅ Disetujui' },
  { value: 'in_progress',      label: '🔧 Diproses' },
  { value: 'completed',        label: '🏁 Selesai' },
  { value: 'rejected',         label: '❌ Ditolak' },
];

const typeOptions = [
  { value: 'perbaikan',   label: '🔧 Perbaikan' },
  { value: 'pengadaan',   label: '🛒 Pengadaan' },
  { value: 'mutasi',      label: '🚚 Mutasi' },
  { value: 'pemutihan',   label: '🗑️ Pemutihan' },
  { value: 'pemeliharaan',label: '🛠️ Pemeliharaan' },
];

const urgencyOptions = [
  { value: 'rendah',        label: '🟢 Rendah',  color: 'chip-green' },
  { value: 'sedang',        label: '🟡 Sedang',  color: 'chip-yellow' },
  { value: 'tinggi_darurat',label: '🔴 Darurat', color: 'chip-red' },
];

function toggle(key, value) {
  filters[key] = filters[key] === value ? '' : value;
  emit('change');
}
function toggleSortDir() {
  filters.sort_dir = filters.sort_dir === 'asc' ? 'desc' : 'asc';
  emit('change');
}
function onReset() {
  emit('reset');
}
</script>

<style scoped>
.filter-panel {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  margin-bottom: 1.25rem;
  overflow: hidden;
  transition: all 0.2s;
}
.filter-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1.25rem;
  flex-wrap: wrap;
}
.filter-search-wrap { position: relative; flex: 1; min-width: 220px; }
.filter-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--text-muted); }
.filter-search-input { padding-left: 2.25rem !important; }

.filter-toggle-btn { position: relative; }
.filter-active-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--color-error); position: absolute; top: -2px; right: -2px;
  border: 2px solid var(--bg-card);
}
.has-filters { border-color: var(--color-primary); color: var(--color-primary); }

.filter-body {
  padding: 0.75rem 1.25rem 1rem;
  border-top: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.filter-group { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.filter-group-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; min-width: 60px; }
.filter-chips { display: flex; gap: 0.375rem; flex-wrap: wrap; }
.filter-chip {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  border: 1.5px solid var(--border-color);
  background: var(--bg-page);
  color: var(--text-secondary);
  font-size: 0.78rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  white-space: nowrap;
}
.filter-chip:hover { border-color: var(--color-primary); color: var(--color-primary); }
.filter-chip.active { background: var(--color-primary); border-color: var(--color-primary); color: #fff; font-weight: 700; }
.filter-chip.chip-green.active { background: var(--color-success); border-color: var(--color-success); }
.filter-chip.chip-yellow.active { background: var(--color-warning); border-color: var(--color-warning); }
.filter-chip.chip-red.active { background: var(--color-error); border-color: var(--color-error); }

.active-filters-summary { display: flex; gap: 0.5rem; flex-wrap: wrap; padding: 0.5rem 1.25rem 0.75rem; border-top: 1px solid var(--border-color); }
.active-filter-pill {
  display: inline-flex; align-items: center; gap: 0.35rem;
  padding: 0.2rem 0.6rem; border-radius: 999px;
  background: var(--color-info-bg); color: var(--color-info);
  border: 1px solid var(--color-info-border);
  font-size: 0.75rem; font-weight: 600; cursor: pointer;
  transition: all 0.15s;
}
.active-filter-pill:hover { background: var(--color-error-bg); color: var(--color-error); border-color: var(--color-error-border); }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.25s ease; overflow: hidden; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; max-height: 0; padding: 0 1.25rem; }
.slide-up-enter-to, .slide-up-leave-from { max-height: 300px; }
</style>
