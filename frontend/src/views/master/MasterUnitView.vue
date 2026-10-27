<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Unit Operasional</h1>
        <p class="page-subtitle">Kelola data 14 unit operasional YGPKB beserta wilayah ASP.</p>
      </div>
      <button v-if="canEdit" class="btn btn-primary" @click="openCreate">
        <span style="font-size:1.1rem;margin-right:0.25rem;">+</span> Tambah Unit
      </button>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem;">
      <div class="unit-filter-bar">
        <div class="filter-search-wrap">
          <svg class="filter-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" class="form-input" style="padding-left:2.5rem;" placeholder="Cari kode atau nama unit..." @input="onSearch" />
        </div>
        <select v-model="filterCategory" class="filter-select" @change="onSearch">
          <option value="">Semua Kategori</option>
          <option value="persekolahan">Persekolahan</option>
          <option value="yayasan">Yayasan</option>
          <option value="perguruan_tinggi">Perguruan Tinggi</option>
        </select>
        <select v-model="filterTerritory" class="filter-select" @change="onSearch">
          <option value="">Semua Wilayah</option>
          <option value="kota">Kota</option>
          <option value="regu">Regu</option>
          <option value="kubu_raya">Kubu Raya</option>
          <option value="ibc">IBC</option>
          <option value="itbss">ITBSS</option>
        </select>
        <button class="btn btn-outline filter-refresh-btn" @click="reset">
          <span style="font-size:1rem;">↺</span> Refresh
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div v-if="masterStore.isLoading" style="padding:3rem 0;">
        <div class="skeleton" style="height:300px;border-radius:8px;"></div>
      </div>
      <div v-else-if="filtered.length === 0" class="empty-state">
        <div style="font-size:3rem;margin-bottom:0.75rem;">🏫</div>
        <p style="font-weight:700;color:var(--text-secondary);">Tidak ada unit ditemukan</p>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-top:0.375rem;">Coba ubah kata kunci pencarian atau filter.</p>
      </div>
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:50px;">#</th>
              <th class="sortable" style="width:140px;" @click="toggleSort('unit_code')" title="Urutkan Kode Unit">
                Kode Unit
                <span class="sort-icon" :class="{ active: sortBy === 'unit_code' }">
                  {{ sortBy === 'unit_code' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('name')" title="Urutkan Nama Unit A-Z">
                Nama Unit
                <span class="sort-icon" :class="{ active: sortBy === 'name' }">
                  {{ sortBy === 'name' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" style="width:160px;" @click="toggleSort('category')" title="Urutkan Kategori">
                Kategori
                <span class="sort-icon" :class="{ active: sortBy === 'category' }">
                  {{ sortBy === 'category' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" style="width:160px;" @click="toggleSort('asp_territory')" title="Urutkan Wilayah ASP">
                Wilayah ASP
                <span class="sort-icon" :class="{ active: sortBy === 'asp_territory' }">
                  {{ sortBy === 'asp_territory' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th style="width:100px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(unit, idx) in filtered" :key="unit.id">
              <td class="row-num">{{ idx + 1 }}</td>
              <td><span class="unit-code-badge">{{ unit.unit_code }}</span></td>
              <td class="unit-name-cell">{{ unit.name }}</td>
              <td><span class="badge badge-info" style="font-size:0.72rem;">{{ unit.category }}</span></td>
              <td>
                <span v-if="unit.asp_territory" class="territory-tag">{{ unit.asp_territory }}</span>
                <span v-else class="text-muted">—</span>
              </td>
              <td>
                <div style="display:flex;gap:0.375rem;">
                  <button v-if="canEdit" class="action-btn action-btn-edit" @click="openEdit(unit)" title="Edit unit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button v-if="canDelete" class="action-btn action-btn-danger" @click="handleDelete(unit)" title="Hapus unit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Footer count -->
      <div v-if="!masterStore.isLoading && filtered.length > 0" class="table-footer">
        Menampilkan {{ filtered.length }} dari {{ masterStore.units.length }} unit
      </div>
    </div>

    <!-- ── Modal Form ── -->
    <transition name="modal">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card-wide">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Edit Unit' : 'Tambah Unit Baru' }}</h2>
            <button class="modal-close-btn" @click="closeModal">✕</button>
          </div>

          <form @submit.prevent="submitForm" style="display:flex;flex-direction:column;gap:1rem;">
            <div class="form-grid-2">
              <div>
                <label class="form-label">Kode Unit <span class="req">*</span></label>
                <input v-model="form.unit_code" type="text" class="form-input" placeholder="Cth: TK-1, SD-1" maxlength="30" required />
                <p class="form-hint">Format singkat tanpa spasi. Cth: TK-1</p>
              </div>
              <div>
                <label class="form-label">Nama Unit <span class="req">*</span></label>
                <input v-model="form.name" type="text" class="form-input" placeholder="Cth: TK 1, SD 1" maxlength="100" required />
              </div>
              <div>
                <label class="form-label">Kategori <span class="req">*</span></label>
                <select v-model="form.category" class="form-input" required>
                  <option value="">— Pilih Kategori —</option>
                  <option value="persekolahan">Persekolahan</option>
                  <option value="yayasan">Yayasan</option>
                  <option value="perguruan_tinggi">Perguruan Tinggi</option>
                </select>
              </div>
              <div>
                <label class="form-label">Wilayah ASP</label>
                <select v-model="form.asp_territory" class="form-input">
                  <option value="">— Tidak ada —</option>
                  <option value="kota">Kota</option>
                  <option value="regu">Regu</option>
                  <option value="kubu_raya">Kubu Raya</option>
                  <option value="ibc">IBC</option>
                  <option value="itbss">ITBSS</option>
                </select>
                <p class="form-hint">Wilayah operasional ASP yang menangani unit ini</p>
              </div>
            </div>

            <div class="modal-footer-actions">
              <button type="button" class="btn btn-outline" @click="closeModal" :disabled="masterStore.isSubmitting">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="masterStore.isSubmitting">
                <span v-if="masterStore.isSubmitting" class="spinner"></span>
                {{ masterStore.isSubmitting ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Unit') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useMasterStore } from '@/stores/master';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';

const masterStore = useMasterStore();
const authStore   = useAuthStore();
const toast       = useToast();
const { confirm } = useConfirm();

const search          = ref('');
const filterCategory  = ref('');
const filterTerritory = ref('');
const showModal       = ref(false);
const isEditing       = ref(false);
const editingId       = ref(null);

const FORM_DEFAULT = { unit_code: '', name: '', category: '', asp_territory: '' };
const form = ref({ ...FORM_DEFAULT });

const canEdit   = computed(() => ['superadmin', 'admin_sarpras', 'kd_sarpras'].includes(authStore.user?.role));
const canDelete = computed(() => ['superadmin', 'admin_sarpras'].includes(authStore.user?.role));

const sortBy = ref('name');
const sortDir = ref('asc');

function toggleSort(col) {
  if (sortBy.value === col) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortBy.value = col;
    sortDir.value = 'asc';
  }
}

const filtered = computed(() => {
  let list = masterStore.units.filter(u => {
    const q = search.value.toLowerCase();
    const matchSearch = !q || u.name?.toLowerCase().includes(q) || u.unit_code?.toLowerCase().includes(q);
    const matchCat = !filterCategory.value || u.category === filterCategory.value;
    const matchTerr = !filterTerritory.value || u.asp_territory === filterTerritory.value;
    return matchSearch && matchCat && matchTerr;
  });

  list.sort((a, b) => {
    const valA = (a[sortBy.value] || '').toString();
    const valB = (b[sortBy.value] || '').toString();
    const cmp = valA.localeCompare(valB, 'id-ID', { numeric: true, sensitivity: 'base' });
    return sortDir.value === 'asc' ? cmp : -cmp;
  });

  return list;
});

function onSearch() { /* reactive */ }

function reset() {
  search.value = '';
  filterCategory.value = '';
  filterTerritory.value = '';
  masterStore.fetchUnits();
}

function openCreate() {
  isEditing.value = false;
  editingId.value = null;
  form.value = { ...FORM_DEFAULT };
  showModal.value = true;
}

function openEdit(unit) {
  isEditing.value = true;
  editingId.value = unit.id;
  form.value = {
    unit_code:     unit.unit_code     || '',
    name:          unit.name          || '',
    category:      unit.category      || '',
    asp_territory: unit.asp_territory || '',
  };
  showModal.value = true;
}

function closeModal() { showModal.value = false; }

async function submitForm() {
  try {
    if (isEditing.value) {
      await masterStore.updateUnit(editingId.value, form.value);
      toast.success('Unit berhasil diperbarui.');
    } else {
      await masterStore.createUnit(form.value);
      toast.success('Unit baru berhasil ditambahkan!');
    }
    closeModal();
    masterStore.fetchUnits();
  } catch (e) {
    const msg = e?.errors ? Object.values(e.errors).flat().join(', ') : (e?.message || 'Gagal menyimpan unit.');
    toast.error(msg, 'Kesalahan');
  }
}

async function handleDelete(unit) {
  const ok = await confirm({
    title: `Hapus Unit: ${unit.name}`,
    message: `Yakin ingin menghapus unit "${unit.name}" (${unit.unit_code})? Tindakan ini tidak dapat dibatalkan.`,
    confirmLabel: 'Ya, Hapus',
    type: 'danger',
  });
  if (!ok) return;
  try {
    await masterStore.deleteUnit(unit.id);
    toast.success(`Unit "${unit.name}" berhasil dihapus.`);
  } catch (e) {
    toast.error(e?.message || 'Gagal menghapus unit.');
  }
}

onMounted(() => masterStore.fetchUnits());
</script>

<style scoped>
.unit-filter-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: nowrap; }
.filter-search-wrap { position: relative; flex: 1; min-width: 200px; }
.filter-search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--text-muted); pointer-events: none; }

.filter-select {
  height: 42px; width: auto; min-width: 140px;
  padding: 0 2.25rem 0 0.875rem;
  font-size: 0.875rem; font-family: inherit;
  background: var(--bg-input);
  border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md);
  color: var(--text-primary);
  outline: none; cursor: pointer; appearance: none; flex-shrink: 0;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 0.625rem center;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.filter-select:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(26,58,107,0.1); }

.filter-refresh-btn { height: 42px; padding: 0 1.125rem; display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; white-space: nowrap; flex-shrink: 0; }

.unit-code-badge {
  font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; font-weight: 700;
  color: var(--color-primary); background: rgba(26,58,107,0.07);
  padding: 0.2rem 0.5rem; border-radius: 6px; white-space: nowrap;
}
.unit-name-cell  { font-weight: 600; font-size: 0.875rem; }
.row-num         { font-size: 0.8rem; color: var(--text-muted); text-align: center; }
.territory-tag   { display: inline-block; font-size: 0.7rem; background: var(--color-info-bg); color: var(--color-info); border: 1px solid var(--color-info-border); border-radius: 999px; padding: 0.1rem 0.55rem; text-transform: capitalize; }
.text-muted      { color: var(--text-muted); font-size: 0.8rem; }
.empty-state     { text-align: center; padding: 3rem 1rem; }
.table-footer    { padding: 0.75rem 1rem; font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid var(--border-color); }

.btn-danger-ghost { background: var(--color-error-bg, #fef2f2); color: var(--color-error, #dc2626); border: 1px solid var(--color-error-border, #fecaca); }
.btn-danger-ghost:hover:not(:disabled) { background: var(--color-error, #dc2626); color: #fff; }

/* Modal */
.modal-overlay   { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); z-index: 900; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.modal-card-wide { background: var(--bg-card); border-radius: 20px; padding: 1.75rem; width: 100%; max-width: 520px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
.modal-header    { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.modal-header h2 { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
.modal-close-btn { width: 28px; height: 28px; border: none; background: var(--bg-page); border-radius: 50%; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
.modal-close-btn:hover { background: var(--border-color); }
.modal-footer-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem; }

.form-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.req  { color: var(--color-error); }
.form-hint { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }

.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; margin-right: 0.375rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.modal-enter-active { animation: modal-in 0.25s cubic-bezier(0.34,1.56,0.64,1); }
.modal-leave-active { animation: modal-out 0.18s ease forwards; }
@keyframes modal-in  { from { opacity: 0; transform: scale(0.9) translateY(16px); } to { opacity: 1; transform: none; } }
@keyframes modal-out { from { opacity: 1; } to { opacity: 0; transform: scale(0.94); } }
</style>
