<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Mitra Vendor</h1>
        <p class="page-subtitle">Kelola data rekanan / vendor untuk pengadaan barang dan jasa.</p>
      </div>
      <button v-if="canEdit" class="btn btn-primary" @click="openCreate">
        <span style="font-size:1.1rem;margin-right:0.25rem;">+</span> Tambah Vendor
      </button>
    </div>

    <!-- Stats -->
    <div class="vendor-stats">
      <div class="vstat-card">
        <span class="vstat-icon" style="background:rgba(26,58,107,0.1);">🏭</span>
        <div><div class="vstat-num">{{ masterStore.vendors.length }}</div><div class="vstat-label">Total Vendor</div></div>
      </div>
      <div class="vstat-card">
        <span class="vstat-icon" style="background:rgba(22,163,74,0.1);">✅</span>
        <div><div class="vstat-num">{{ activeCount }}</div><div class="vstat-label">Aktif</div></div>
      </div>
      <div class="vstat-card">
        <span class="vstat-icon" style="background:rgba(220,38,38,0.1);">⛔</span>
        <div><div class="vstat-num">{{ inactiveCount }}</div><div class="vstat-label">Nonaktif</div></div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem;">
      <div class="vendor-filter-bar">
        <div class="filter-search-wrap">
          <svg class="filter-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="search" type="text" class="form-input" style="padding-left:2.5rem;" placeholder="Cari nama vendor atau PIC..." />
        </div>
        <select v-model="filterStatus" class="filter-select">
          <option value="">Semua Status</option>
          <option value="1">✅ Aktif</option>
          <option value="0">⛔ Nonaktif</option>
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
        <div style="font-size:3rem;margin-bottom:0.75rem;">🏭</div>
        <p style="font-weight:700;color:var(--text-secondary);">Tidak ada vendor ditemukan</p>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-top:0.375rem;">Coba ubah kata kunci atau tambah vendor baru.</p>
      </div>
      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:40px;">#</th>
              <th class="sortable" style="width:230px;" @click="toggleSort('name')" title="Urutkan Nama Vendor A-Z">
                Nama Vendor
                <span class="sort-icon" :class="{ active: sortBy === 'name' }">
                  {{ sortBy === 'name' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" style="width:160px;" @click="toggleSort('pic_name')" title="Urutkan PIC">
                PIC
                <span class="sort-icon" :class="{ active: sortBy === 'pic_name' }">
                  {{ sortBy === 'pic_name' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" style="width:150px;" @click="toggleSort('phone')" title="Urutkan Telepon">
                Telepon
                <span class="sort-icon" :class="{ active: sortBy === 'phone' }">
                  {{ sortBy === 'phone' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" style="width:200px;" @click="toggleSort('specialty')" title="Urutkan Spesialisasi">
                Spesialisasi
                <span class="sort-icon" :class="{ active: sortBy === 'specialty' }">
                  {{ sortBy === 'specialty' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" style="width:120px;" @click="toggleSort('is_active')" title="Urutkan Status">
                Status
                <span class="sort-icon" :class="{ active: sortBy === 'is_active' }">
                  {{ sortBy === 'is_active' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th style="width:110px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(vendor, idx) in filtered" :key="vendor.id">
              <td class="row-num">{{ idx + 1 }}</td>
              <td>
                <p class="vendor-name">{{ vendor.name }}</p>
                <p v-if="vendor.address" class="vendor-address">{{ vendor.address }}</p>
              </td>
              <td class="vendor-pic">{{ vendor.pic_name || '—' }}</td>
              <td class="vendor-phone">{{ vendor.phone || '—' }}</td>
              <td><span v-if="vendor.specialty" class="specialty-tag">{{ vendor.specialty }}</span><span v-else class="text-muted">—</span></td>
              <td>
                <span :class="['badge', vendor.is_active ? 'badge-success' : 'badge-error']">
                  {{ vendor.is_active ? '✅ Aktif' : '⛔ Nonaktif' }}
                </span>
              </td>
              <td>
                <div style="display:flex;gap:0.375rem;">
                  <button v-if="canEdit" class="action-btn action-btn-edit" @click="openEdit(vendor)" title="Edit vendor">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button v-if="canEdit" :class="['action-btn', vendor.is_active ? 'action-btn-warning' : 'action-btn-success']" @click="handleToggle(vendor)" :title="vendor.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                    <svg v-if="vendor.is_active" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                  <button v-if="canDelete" class="action-btn action-btn-danger" @click="handleDelete(vendor)" title="Hapus vendor">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="!masterStore.isLoading && filtered.length > 0" class="table-footer">
        Menampilkan {{ filtered.length }} dari {{ masterStore.vendors.length }} vendor
      </div>
    </div>

    <!-- ── Modal Form ── -->
    <transition name="modal">
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-card-wide">
          <div class="modal-header">
            <h2>{{ isEditing ? 'Edit Vendor' : 'Tambah Vendor Baru' }}</h2>
            <button class="modal-close-btn" @click="closeModal">✕</button>
          </div>

          <form @submit.prevent="submitForm" style="display:flex;flex-direction:column;gap:1rem;">
            <div class="form-grid-2">
              <div class="col-span-2">
                <label class="form-label">Nama Vendor / Perusahaan <span class="req">*</span></label>
                <input v-model="form.name" type="text" class="form-input" placeholder="PT. / CV. ..." maxlength="150" required />
              </div>
              <div>
                <label class="form-label">Nama PIC</label>
                <input v-model="form.pic_name" type="text" class="form-input" placeholder="Nama penanggung jawab" maxlength="100" />
              </div>
              <div>
                <label class="form-label">No. Telepon</label>
                <input v-model="form.phone" type="text" class="form-input" placeholder="08xx-xxxx-xxxx" maxlength="40" />
              </div>
              <div>
                <label class="form-label">Email</label>
                <input v-model="form.email" type="email" class="form-input" placeholder="vendor@email.com" maxlength="100" />
              </div>
              <div>
                <label class="form-label">Spesialisasi</label>
                <input v-model="form.specialty" type="text" class="form-input" placeholder="Cth: Elektronik, Konstruksi" maxlength="100" />
              </div>
              <div class="col-span-2">
                <label class="form-label">Alamat</label>
                <textarea v-model="form.address" class="form-input form-textarea" placeholder="Alamat lengkap vendor..." rows="2" maxlength="500"></textarea>
              </div>
              <div v-if="isEditing" class="col-span-2">
                <label class="form-label">Status</label>
                <div style="display:flex;gap:0.75rem;margin-top:0.25rem;">
                  <label class="radio-option" :class="{ active: form.is_active }">
                    <input type="radio" :value="true" v-model="form.is_active" /> ✅ Aktif
                  </label>
                  <label class="radio-option" :class="{ active: !form.is_active }">
                    <input type="radio" :value="false" v-model="form.is_active" /> ⛔ Nonaktif
                  </label>
                </div>
              </div>
            </div>

            <div class="modal-footer-actions">
              <button type="button" class="btn btn-outline" @click="closeModal" :disabled="masterStore.isSubmitting">Batal</button>
              <button type="submit" class="btn btn-primary" :disabled="masterStore.isSubmitting">
                <span v-if="masterStore.isSubmitting" class="spinner"></span>
                {{ masterStore.isSubmitting ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Tambah Vendor') }}
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
import { useAuthStore }   from '@/stores/auth';
import { useToast }       from '@/composables/useToast';
import { useConfirm }     from '@/composables/useConfirm';

const masterStore = useMasterStore();
const authStore   = useAuthStore();
const toast       = useToast();
const { confirm } = useConfirm();

const search       = ref('');
const filterStatus = ref('');
const showModal    = ref(false);
const isEditing    = ref(false);
const editingId    = ref(null);

const FORM_DEFAULT = { name: '', pic_name: '', phone: '', email: '', address: '', specialty: '', is_active: true };
const form = ref({ ...FORM_DEFAULT });

const canEdit   = computed(() => ['superadmin', 'admin_sarpras', 'kd_sarpras'].includes(authStore.user?.role));
const canDelete = computed(() => ['superadmin', 'admin_sarpras'].includes(authStore.user?.role));
const activeCount   = computed(() => masterStore.vendors.filter(v => v.is_active).length);
const inactiveCount = computed(() => masterStore.vendors.filter(v => !v.is_active).length);

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
  let list = masterStore.vendors.filter(v => {
    const q = search.value.toLowerCase();
    const matchSearch = !q || v.name?.toLowerCase().includes(q) || v.pic_name?.toLowerCase().includes(q) || v.specialty?.toLowerCase().includes(q);
    const matchStatus = filterStatus.value === '' || String(Number(v.is_active)) === filterStatus.value;
    return matchSearch && matchStatus;
  });

  list.sort((a, b) => {
    let cmp = 0;
    if (sortBy.value === 'is_active') {
      cmp = Number(b.is_active) - Number(a.is_active);
    } else {
      const valA = (a[sortBy.value] || '').toString();
      const valB = (b[sortBy.value] || '').toString();
      cmp = valA.localeCompare(valB, 'id-ID', { numeric: true, sensitivity: 'base' });
    }
    return sortDir.value === 'asc' ? cmp : -cmp;
  });

  return list;
});

function reset() {
  search.value = '';
  filterStatus.value = '';
  masterStore.fetchVendors();
}

function openCreate() {
  isEditing.value = false;
  editingId.value = null;
  form.value = { ...FORM_DEFAULT };
  showModal.value = true;
}

function openEdit(vendor) {
  isEditing.value = true;
  editingId.value = vendor.id;
  form.value = {
    name:      vendor.name      || '',
    pic_name:  vendor.pic_name  || '',
    phone:     vendor.phone     || '',
    email:     vendor.email     || '',
    address:   vendor.address   || '',
    specialty: vendor.specialty || '',
    is_active: vendor.is_active ?? true,
  };
  showModal.value = true;
}

function closeModal() { showModal.value = false; }

async function submitForm() {
  try {
    if (isEditing.value) {
      await masterStore.updateVendor(editingId.value, form.value);
      toast.success('Vendor berhasil diperbarui.');
    } else {
      await masterStore.createVendor(form.value);
      toast.success('Vendor baru berhasil ditambahkan!');
    }
    closeModal();
    masterStore.fetchVendors();
  } catch (e) {
    const msg = e?.errors ? Object.values(e.errors).flat().join(', ') : (e?.message || 'Gagal menyimpan vendor.');
    toast.error(msg, 'Kesalahan');
  }
}

async function handleToggle(vendor) {
  try {
    await masterStore.toggleVendorActive(vendor.id);
    const label = !vendor.is_active ? 'diaktifkan' : 'dinonaktifkan';
    toast.success(`Vendor "${vendor.name}" berhasil ${label}.`);
  } catch (e) {
    toast.error(e?.message || 'Gagal mengubah status vendor.');
  }
}

async function handleDelete(vendor) {
  const ok = await confirm({
    title: `Hapus Vendor: ${vendor.name}`,
    message: `Yakin ingin menghapus vendor "${vendor.name}"? Tindakan ini tidak dapat dibatalkan.`,
    confirmLabel: 'Ya, Hapus',
    type: 'danger',
  });
  if (!ok) return;
  try {
    await masterStore.deleteVendor(vendor.id);
    toast.success(`Vendor "${vendor.name}" berhasil dihapus.`);
  } catch (e) {
    toast.error(e?.message || 'Gagal menghapus vendor.');
  }
}

onMounted(() => masterStore.fetchVendors());
</script>

<style scoped>
.vendor-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.25rem; }
.vstat-card   { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 1rem 1.25rem; display: flex; align-items: center; gap: 1rem; box-shadow: var(--shadow-sm); }
.vstat-icon   { width: 44px; height: 44px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
.vstat-num    { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
.vstat-label  { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; }

.vendor-filter-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: nowrap; }
.filter-search-wrap { position: relative; flex: 1; min-width: 200px; }
.filter-search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--text-muted); pointer-events: none; }

.filter-select {
  height: 42px; width: auto; min-width: 140px;
  padding: 0 2.25rem 0 0.875rem;
  font-size: 0.875rem; font-family: inherit;
  background: var(--bg-input); border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md); color: var(--text-primary);
  outline: none; cursor: pointer; appearance: none; flex-shrink: 0;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 0.625rem center;
}
.filter-select:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(26,58,107,0.1); }
.filter-refresh-btn { height: 42px; padding: 0 1.125rem; display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; white-space: nowrap; flex-shrink: 0; }

.vendor-name    { font-weight: 600; font-size: 0.875rem; color: var(--text-primary); }
.vendor-address { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.1rem; }
.vendor-pic     { font-size: 0.875rem; color: var(--text-secondary); }
.vendor-phone   { font-size: 0.875rem; font-family: monospace; color: var(--text-secondary); }
.specialty-tag  { display: inline-block; font-size: 0.72rem; font-weight: 600; background: rgba(124,58,237,0.08); color: #7c3aed; border: 1px solid rgba(124,58,237,0.2); border-radius: 999px; padding: 0.25rem 0.75rem; white-space: normal; word-break: break-word; line-height: 1.4; }
.text-muted     { color: var(--text-muted); font-size: 0.8rem; }
.row-num        { font-size: 0.8rem; color: var(--text-muted); text-align: center; }
.empty-state    { text-align: center; padding: 3rem 1rem; }
.table-footer   { padding: 0.75rem 1rem; font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid var(--border-color); }

.btn-danger-ghost  { background: var(--color-error-bg,#fef2f2); color: var(--color-error,#dc2626); border: 1px solid var(--color-error-border,#fecaca); }
.btn-danger-ghost:hover:not(:disabled)  { background: var(--color-error,#dc2626); color: #fff; }
.btn-warning-ghost { background: rgba(217,119,6,0.08); color: #d97706; border: 1px solid rgba(217,119,6,0.25); }
.btn-warning-ghost:hover:not(:disabled) { background: #d97706; color: #fff; }
.btn-success-ghost { background: rgba(22,163,74,0.08); color: #16a34a; border: 1px solid rgba(22,163,74,0.25); }
.btn-success-ghost:hover:not(:disabled) { background: #16a34a; color: #fff; }

/* Modal */
.modal-overlay   { position: fixed; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); z-index: 900; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.modal-card-wide { background: var(--bg-card); border-radius: 20px; padding: 1.75rem; width: 100%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
.modal-header    { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.modal-header h2 { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
.modal-close-btn { width: 28px; height: 28px; border: none; background: var(--bg-page); border-radius: 50%; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; color: var(--text-muted); }
.modal-close-btn:hover { background: var(--border-color); }
.modal-footer-actions { display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); margin-top: 0.25rem; }

.form-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.col-span-2  { grid-column: 1 / -1; }
.req         { color: var(--color-error); }
.form-textarea { resize: vertical; min-height: 60px; }

.radio-option { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border: 1.5px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; font-size: 0.875rem; color: var(--text-secondary); transition: all 0.2s; }
.radio-option.active { border-color: var(--color-primary); background: rgba(26,58,107,0.05); color: var(--color-primary); font-weight: 600; }
.radio-option input { display: none; }

.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; margin-right: 0.375rem; }
@keyframes spin { to { transform: rotate(360deg); } }

.modal-enter-active { animation: modal-in 0.25s cubic-bezier(0.34,1.56,0.64,1); }
.modal-leave-active { animation: modal-out 0.18s ease forwards; }
@keyframes modal-in  { from { opacity: 0; transform: scale(0.9) translateY(16px); } to { opacity: 1; transform: none; } }
@keyframes modal-out { from { opacity: 1; } to { opacity: 0; transform: scale(0.94); } }
</style>
