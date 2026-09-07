<template>
  <div class="master-page">

    <!-- ── Page Header ── -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Master Data</h1>
        <p class="page-subtitle">Kelola data referensi unit operasional dan mitra vendor SIPARAS.</p>
      </div>
    </div>

    <!-- ── Stats Bar ── -->
    <div class="master-stats">
      <div class="master-stat-card">
        <div class="master-stat-icon" style="background:rgba(26,58,107,0.1);color:var(--color-primary);">🏫</div>
        <div>
          <div class="master-stat-num">{{ masterStore.units.length }}</div>
          <div class="master-stat-label">Unit Operasional</div>
        </div>
      </div>
      <div class="master-stat-card">
        <div class="master-stat-icon" style="background:rgba(22,163,74,0.1);color:var(--color-success);">🏭</div>
        <div>
          <div class="master-stat-num">{{ masterStore.vendors.length }}</div>
          <div class="master-stat-label">Mitra Vendor</div>
        </div>
      </div>
      <div class="master-stat-card">
        <div class="master-stat-icon" style="background:rgba(22,163,74,0.1);color:var(--color-success);">✅</div>
        <div>
          <div class="master-stat-num">{{ activeVendors }}</div>
          <div class="master-stat-label">Vendor Aktif</div>
        </div>
      </div>
    </div>

    <!-- ─────────── Panel Unit Operasional ─────────── -->
    <div class="panel-card">
      <!-- Header (selalu tampil, bisa klik untuk collapse) -->
      <button class="panel-toggle-header" @click="togglePanel('units')">
        <div class="panel-toggle-left">
          <span class="panel-icon">🏫</span>
          <span class="panel-toggle-title">Unit Operasional</span>
          <span class="badge badge-info">{{ masterStore.units.length }}</span>
        </div>
        <div class="panel-toggle-right">
          <button class="btn btn-primary btn-sm" @click.stop="showUnitModal = true">
            <span>+</span> Tambah Unit
          </button>
          <span class="collapse-chevron" :class="{ rotated: !openPanels.units }">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>
      </button>

      <!-- Collapsible Body -->
      <div class="panel-body" :class="{ collapsed: !openPanels.units }">
        <!-- Search + Filter row -->
        <div class="panel-toolbar">
          <div class="panel-search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toolbar-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="unitSearch" type="text" class="toolbar-search-input" placeholder="Cari kode atau nama unit..." />
          </div>
          <div class="toolbar-info">
            Menampilkan <strong>{{ filteredUnits.length }}</strong> dari {{ masterStore.units.length }} unit
          </div>
        </div>

        <!-- Table -->
        <div class="panel-table-wrap">
          <div v-if="masterStore.isLoading" class="panel-loading">
            <div class="panel-spinner"></div> Memuat data unit...
          </div>
          <table v-else class="data-table">
            <thead>
              <tr>
                <th style="width:130px;">Kode Unit</th>
                <th>Nama Unit</th>
                <th style="width:160px;">Kategori</th>
                <th style="width:160px;">Wilayah ASP</th>
                <th style="width:100px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredUnits.length === 0">
                <td colspan="5" class="panel-empty-cell">Tidak ada unit yang cocok dengan pencarian.</td>
              </tr>
              <tr v-for="unit in filteredUnits" :key="unit.id">
                <td><span class="unit-code">{{ unit.unit_code }}</span></td>
                <td class="unit-name-cell">{{ unit.name }}</td>
                <td><span class="badge badge-info" style="font-size:0.7rem;">{{ unit.category || 'persekolahan' }}</span></td>
                <td class="asp-cell">{{ unit.asp_territory || '—' }}</td>
                <td>
                  <button class="btn btn-ghost btn-sm" @click="selectUnit(unit)">Detail</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ─────────── Panel Mitra Vendor ─────────── -->
    <div class="panel-card">
      <button class="panel-toggle-header" @click="togglePanel('vendors')">
        <div class="panel-toggle-left">
          <span class="panel-icon">🏭</span>
          <span class="panel-toggle-title">Mitra Vendor</span>
          <span class="badge badge-success">{{ masterStore.vendors.length }}</span>
        </div>
        <div class="panel-toggle-right">
          <button class="btn btn-success btn-sm" @click.stop="showVendorModal = true">
            <span>+</span> Tambah Vendor
          </button>
          <span class="collapse-chevron" :class="{ rotated: !openPanels.vendors }">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
        </div>
      </button>

      <div class="panel-body" :class="{ collapsed: !openPanels.vendors }">
        <div class="panel-toolbar">
          <div class="panel-search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toolbar-search-icon"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input v-model="vendorSearch" type="text" class="toolbar-search-input" placeholder="Cari nama vendor atau PIC..." />
          </div>
          <div class="toolbar-info">
            Menampilkan <strong>{{ filteredVendors.length }}</strong> dari {{ masterStore.vendors.length }} vendor
          </div>
        </div>

        <div class="panel-table-wrap">
          <div v-if="masterStore.isLoading" class="panel-loading">
            <div class="panel-spinner"></div> Memuat data vendor...
          </div>
          <table v-else class="data-table">
            <thead>
              <tr>
                <th>Nama Vendor</th>
                <th style="width:180px;">PIC</th>
                <th style="width:180px;">No. Telepon</th>
                <th style="width:120px;">Status</th>
                <th style="width:100px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredVendors.length === 0">
                <td colspan="5" class="panel-empty-cell">Tidak ada vendor yang cocok dengan pencarian.</td>
              </tr>
              <tr v-for="vendor in filteredVendors" :key="vendor.id">
                <td class="vendor-name-cell">{{ vendor.name }}</td>
                <td class="vendor-pic-cell">{{ vendor.pic_name }}</td>
                <td class="vendor-phone-cell">{{ vendor.phone }}</td>
                <td>
                  <span :class="['badge', vendor.is_active ? 'badge-success' : 'badge-error']">
                    {{ vendor.is_active ? '✅ Aktif' : '⛔ Nonaktif' }}
                  </span>
                </td>
                <td>
                  <button class="btn btn-ghost btn-sm" @click="selectVendor(vendor)">Detail</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Modal Detail Unit ── -->
    <transition name="modal">
      <div v-if="selectedUnit" class="modal-overlay" @click.self="selectedUnit = null">
        <div class="detail-modal">
          <div class="detail-modal-header">
            <h2>Detail Unit: {{ selectedUnit.name }}</h2>
            <button class="modal-close-btn" @click="selectedUnit = null">✕</button>
          </div>
          <div class="detail-modal-body">
            <div class="detail-row"><span class="detail-label">Kode</span><span class="unit-code">{{ selectedUnit.unit_code }}</span></div>
            <div class="detail-row"><span class="detail-label">Nama Unit</span><span>{{ selectedUnit.name }}</span></div>
            <div class="detail-row"><span class="detail-label">Kategori</span><span>{{ selectedUnit.category }}</span></div>
            <div class="detail-row"><span class="detail-label">Wilayah ASP</span><span>{{ selectedUnit.asp_territory || '—' }}</span></div>
          </div>
          <div class="detail-modal-footer">
            <button class="btn btn-outline" @click="selectedUnit = null">Tutup</button>
            <button class="btn btn-primary">✏️ Edit Unit</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- ── Modal Detail Vendor ── -->
    <transition name="modal">
      <div v-if="selectedVendor" class="modal-overlay" @click.self="selectedVendor = null">
        <div class="detail-modal">
          <div class="detail-modal-header">
            <h2>Detail Vendor</h2>
            <button class="modal-close-btn" @click="selectedVendor = null">✕</button>
          </div>
          <div class="detail-modal-body">
            <div class="detail-row"><span class="detail-label">Nama</span><span class="vendor-name-cell">{{ selectedVendor.name }}</span></div>
            <div class="detail-row"><span class="detail-label">PIC</span><span>{{ selectedVendor.pic_name }}</span></div>
            <div class="detail-row"><span class="detail-label">Telepon</span><span>{{ selectedVendor.phone }}</span></div>
            <div class="detail-row"><span class="detail-label">Email</span><span>{{ selectedVendor.email || '—' }}</span></div>
            <div class="detail-row"><span class="detail-label">Alamat</span><span>{{ selectedVendor.address || '—' }}</span></div>
            <div class="detail-row">
              <span class="detail-label">Status</span>
              <span :class="['badge', selectedVendor.is_active ? 'badge-success' : 'badge-error']">
                {{ selectedVendor.is_active ? '✅ Aktif' : '⛔ Nonaktif' }}
              </span>
            </div>
          </div>
          <div class="detail-modal-footer">
            <button class="btn btn-outline" @click="selectedVendor = null">Tutup</button>
            <button class="btn btn-primary">✏️ Edit Vendor</button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useMasterStore } from '@/stores/master';

const masterStore = useMasterStore();
const unitSearch   = ref('');
const vendorSearch = ref('');
const selectedUnit   = ref(null);
const selectedVendor = ref(null);
const showUnitModal   = ref(false);
const showVendorModal = ref(false);

// Both panels open by default
const openPanels = reactive({ units: true, vendors: true });
const togglePanel = (key) => { openPanels[key] = !openPanels[key]; };
const selectUnit   = (u) => { selectedUnit.value = u; };
const selectVendor = (v) => { selectedVendor.value = v; };

const filteredUnits = computed(() => {
  if (!unitSearch.value.trim()) return masterStore.units;
  const q = unitSearch.value.toLowerCase();
  return masterStore.units.filter(u =>
    u.name?.toLowerCase().includes(q) || u.unit_code?.toLowerCase().includes(q)
  );
});

const filteredVendors = computed(() => {
  if (!vendorSearch.value.trim()) return masterStore.vendors;
  const q = vendorSearch.value.toLowerCase();
  return masterStore.vendors.filter(v =>
    v.name?.toLowerCase().includes(q) || v.pic_name?.toLowerCase().includes(q)
  );
});

const activeVendors = computed(() => masterStore.vendors.filter(v => v.is_active).length);

onMounted(async () => {
  await Promise.all([ masterStore.fetchUnits(), masterStore.fetchVendors() ]);
});
</script>

<style scoped>
.master-page { display: flex; flex-direction: column; gap: 1.25rem; }

/* ── Stats ── */
.master-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.master-stat-card {
  background: var(--bg-card); border: 1px solid var(--border-color);
  border-radius: var(--radius-lg); padding: 1.125rem 1.25rem;
  display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm);
}
.master-stat-icon { width: 46px; height: 46px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; }
.master-stat-num  { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
.master-stat-label{ font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; }

/* ── Collapsible Panel ── */
.panel-card {
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.panel-toggle-header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.5rem;
  background: var(--bg-page);
  border: none;
  border-bottom: 1px solid var(--border-color);
  cursor: pointer;
  text-align: left;
  transition: background 0.15s;
}
.panel-toggle-header:hover { background: rgba(26,58,107,0.04); }

.panel-toggle-left  { display: flex; align-items: center; gap: 0.625rem; }
.panel-toggle-right { display: flex; align-items: center; gap: 0.875rem; flex-shrink: 0; }
.panel-icon { font-size: 1.1rem; }
.panel-toggle-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); }

/* Chevron rotate */
.collapse-chevron {
  display: flex; align-items: center; justify-content: center;
  width: 28px; height: 28px;
  border-radius: 50%;
  color: var(--text-muted);
  transition: transform 0.25s ease, background 0.15s;
}
.collapse-chevron svg { width: 16px; height: 16px; }
.collapse-chevron.rotated { transform: rotate(-90deg); }
.panel-toggle-header:hover .collapse-chevron { background: var(--border-color); }

/* Collapsible body */
.panel-body {
  overflow: hidden;
  max-height: 2000px;
  transition: max-height 0.3s ease, opacity 0.25s ease;
  opacity: 1;
}
.panel-body.collapsed {
  max-height: 0;
  opacity: 0;
}

/* ── Toolbar ── */
.panel-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.875rem 1.5rem;
  border-bottom: 1px solid var(--border-color);
  flex-wrap: wrap;
}
.panel-search-wrap { position: relative; flex: 1; min-width: 240px; max-width: 440px; }
.toolbar-search-icon {
  position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
  width: 15px; height: 15px; color: var(--text-muted); pointer-events: none;
}
.toolbar-search-input {
  width: 100%;
  padding: 0.5rem 0.875rem 0.5rem 2.25rem;
  border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md);
  background: var(--bg-input);
  font-size: 0.875rem;
  font-family: inherit;
  color: var(--text-primary);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.toolbar-search-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
}
.toolbar-info { font-size: 0.8rem; color: var(--text-muted); white-space: nowrap; }

/* ── Table ── */
.panel-table-wrap { overflow-x: auto; }
.panel-loading {
  display: flex; align-items: center; justify-content: center;
  gap: 0.75rem; padding: 3rem;
  font-size: 0.875rem; color: var(--text-muted);
}
.panel-spinner {
  width: 20px; height: 20px;
  border: 2px solid var(--border-color); border-top-color: var(--color-primary);
  border-radius: 50%; animation: spin 0.7s linear infinite;
}
.panel-empty-cell {
  text-align: center; padding: 2.5rem;
  color: var(--text-muted); font-style: italic; font-size: 0.875rem;
}

/* ── Cell helpers ── */
.unit-code {
  font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; font-weight: 700;
  color: var(--color-primary); background: rgba(26,58,107,0.06);
  padding: 0.2rem 0.5rem; border-radius: 6px; white-space: nowrap;
}
.unit-name-cell  { font-weight: 600; font-size: 0.875rem; }
.asp-cell        { font-size: 0.875rem; color: var(--text-secondary); text-transform: capitalize; }
.vendor-name-cell{ font-weight: 600; font-size: 0.875rem; }
.vendor-pic-cell { font-size: 0.875rem; color: var(--text-secondary); }
.vendor-phone-cell{ font-size: 0.875rem; font-family: monospace; color: var(--text-secondary); }

/* ── Btn success ── */
.btn-success { background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; border: none; }
.btn-success:hover { background: linear-gradient(135deg, #15803d, #166534); }

/* ── Modals ── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(15,23,42,0.55); backdrop-filter: blur(4px);
  z-index: 900; display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.detail-modal {
  background: var(--bg-card); border-radius: var(--radius-xl);
  width: 100%; max-width: 480px; box-shadow: var(--shadow-lg); overflow: hidden;
}
.detail-modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 1.125rem 1.5rem; border-bottom: 1px solid var(--border-color);
}
.detail-modal-header h2 { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0; }
.modal-close-btn {
  width: 28px; height: 28px; border: none; background: var(--bg-page);
  border-radius: 50%; cursor: pointer; font-size: 0.8rem;
  display: flex; align-items: center; justify-content: center;
  color: var(--text-muted); transition: background 0.15s;
}
.modal-close-btn:hover { background: var(--border-color); }
.detail-modal-body   { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 0.875rem; }
.detail-row          { display: flex; gap: 1rem; align-items: center; }
.detail-label        { width: 110px; flex-shrink: 0; font-size: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; font-weight: 600; }
.detail-modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); background: var(--bg-page); }

/* ── Transitions ── */
.modal-enter-active { animation: modal-in 0.2s ease; }
.modal-leave-active { animation: modal-out 0.15s ease forwards; }
@keyframes modal-in  { from { opacity: 0; transform: scale(0.96) translateY(-8px); } to { opacity:1; transform:none; } }
@keyframes modal-out { from { opacity:1; transform:none; } to { opacity:0; transform: scale(0.96); } }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
