<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
          <h1 class="page-title">Manajemen Pengguna</h1>
          <p class="page-subtitle">Kelola akun, role, dan status pengguna sistem SIPARAS.</p>
        </div>
        <button v-if="canCreate" class="btn btn-primary" @click="openCreateModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah Pengguna
        </button>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem;">
      <div class="filter-bar">
        <div class="filter-search-wrap">
          <svg class="filter-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input
            v-model="filters.search"
            type="text"
            class="form-input"
            style="padding-left:2.5rem;"
            placeholder="Cari nama, username, atau email..."
            @input="debouncedFetch"
          />
        </div>
        <select v-model="filters.role" class="form-select filter-select" @change="fetchUsers">
          <option value="">Semua Role</option>
          <option v-for="(label, value) in allRoles" :key="value" :value="value">{{ label }}</option>
        </select>
        <select v-model="filters.is_active" class="form-select filter-select" @change="fetchUsers">
          <option value="">Semua Status</option>
          <option value="1">✅ Aktif</option>
          <option value="0">⛔ Nonaktif</option>
        </select>
        <button class="btn btn-outline filter-refresh-btn" @click="resetFilters">
          <span style="font-size:1rem;">↺</span> Refresh
        </button>
      </div>
    </div>

    <!-- User Table -->
    <div class="card">
      <div v-if="isLoading" style="padding:3rem 0;">
        <div class="skeleton" style="height:300px;border-radius:8px;"></div>
      </div>

      <div v-else-if="users.length === 0" class="empty-state">
        <div style="font-size:3rem;margin-bottom:0.75rem;">👥</div>
        <p style="font-weight:700;color:var(--text-secondary);">Tidak ada pengguna ditemukan</p>
        <p style="color:var(--text-muted);font-size:0.875rem;margin-top:0.375rem;">Coba ubah filter pencarian atau buat pengguna baru.</p>
      </div>

      <div v-else class="table-wrapper">
        <table class="data-table">
          <thead>
            <tr>
              <th class="sortable" @click="toggleSort('name')" title="Urutkan Nama A-Z">
                Pengguna
                <span class="sort-icon" :class="{ active: sortBy === 'name' }">
                  {{ sortBy === 'name' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('role')" title="Urutkan Role">
                Role
                <span class="sort-icon" :class="{ active: sortBy === 'role' }">
                  {{ sortBy === 'role' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('unit')" title="Urutkan Unit A-Z">
                Unit
                <span class="sort-icon" :class="{ active: sortBy === 'unit' }">
                  {{ sortBy === 'unit' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th class="sortable" @click="toggleSort('status')" title="Urutkan Status">
                Status
                <span class="sort-icon" :class="{ active: sortBy === 'status' }">
                  {{ sortBy === 'status' ? (sortDir === 'asc' ? '▲' : '▼') : '⇅' }}
                </span>
              </th>
              <th>Terakhir Login</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in sortedUsers" :key="user.id">
              <td>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                  <div class="user-avatar">{{ getInitials(user.name) }}</div>
                  <div>
                    <p style="font-weight:600;color:var(--text-primary);">{{ user.name }}</p>
                    <p style="font-size:0.75rem;color:var(--text-muted);">@{{ user.username }} · {{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge" :class="roleBadgeClass(user.role)">{{ formatRole(user.role) }}</span>
              </td>
              <td style="font-size:0.875rem;color:var(--text-secondary);">
                {{ user.unit?.name || '— (Lintas Unit)' }}
                <span v-if="user.asp_territory && user.asp_territory !== 'none'" class="territory-tag">{{ user.asp_territory }}</span>
              </td>
              <td>
                <span class="badge" :class="user.is_active ? 'badge-success' : 'badge-error'">
                  {{ user.is_active ? '✅ Aktif' : '⛔ Nonaktif' }}
                </span>
                <span v-if="user.password_must_change" class="badge badge-warning" style="margin-left:0.375rem;font-size:0.65rem;">Ganti PW</span>
              </td>
              <td style="font-size:0.8rem;color:var(--text-muted);">
                {{ user.password_changed_at ? formatDate(user.password_changed_at) : 'Belum pernah' }}
              </td>
              <td>
                <div style="display:flex;justify-content:flex-end;gap:0.375rem;">
                  <button class="action-btn action-btn-edit" @click="openEditModal(user)" title="Edit profil">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="action-btn action-btn-lock" @click="openResetPwModal(user)" title="Reset password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  </button>
                  <button
                    :class="['action-btn', user.is_active ? 'action-btn-danger' : 'action-btn-success']"
                    @click="handleToggleActive(user)"
                    :title="user.is_active ? 'Nonaktifkan' : 'Aktifkan kembali'"
                    :disabled="user.id === authStore.user?.id"
                  >
                    <svg v-if="user.is_active" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="meta.last_page > 1" class="pagination">
        <button class="btn btn-outline btn-sm" :disabled="meta.current_page === 1" @click="goToPage(meta.current_page - 1)">‹ Prev</button>
        <span style="font-size:0.875rem;color:var(--text-secondary);">Hal {{ meta.current_page }} dari {{ meta.last_page }} · Total {{ meta.total }} pengguna</span>
        <button class="btn btn-outline btn-sm" :disabled="meta.current_page === meta.last_page" @click="goToPage(meta.current_page + 1)">Next ›</button>
      </div>
    </div>

    <!-- ══════ MODAL: BUAT / EDIT USER ══════ -->
    <teleport to="body">
      <transition name="modal">
        <div v-if="showUserModal" class="modal-overlay" @click.self="closeUserModal">
          <div class="modal-card-wide" role="dialog">
            <div class="modal-header">
              <h2>{{ editingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
              <button class="btn btn-ghost btn-icon" @click="closeUserModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <!-- Generated password info (create only) -->
            <div v-if="generatedPassword" class="generated-pw-box">
              <p style="font-weight:700;margin-bottom:0.375rem;">🔑 Password Auto-Generate:</p>
              <code class="generated-pw-code">{{ generatedPassword }}</code>
              <p style="font-size:0.78rem;color:var(--text-muted);margin-top:0.375rem;">Catat password ini! Pengguna wajib menggantinya saat pertama login.</p>
              <button class="btn btn-outline btn-sm" style="margin-top:0.5rem;" @click="copyPw">Salin Password</button>
            </div>

            <form @submit.prevent="submitUserForm" class="form-grid-2" style="margin-top:1.25rem;" v-if="!generatedPassword">
              <div>
                <label class="form-label">Nama Lengkap <span style="color:var(--color-error);">*</span></label>
                <input v-model="userForm.name" type="text" class="form-input" placeholder="Nama lengkap pengguna" required />
              </div>
              <div>
                <label class="form-label">Username <span style="color:var(--color-error);">*</span></label>
                <input v-model="userForm.username" type="text" class="form-input" placeholder="username_unik" :disabled="!!editingUser" required />
              </div>
              <div>
                <label class="form-label">Email <span style="color:var(--color-error);">*</span></label>
                <input v-model="userForm.email" type="email" class="form-input" placeholder="email@ygpkb.ac.id" required />
              </div>
              <div>
                <label class="form-label">Nomor HP</label>
                <input v-model="userForm.phone_number" type="tel" class="form-input" placeholder="08xxxxxxxxxx" />
              </div>
              <div>
                <label class="form-label">Role <span style="color:var(--color-error);">*</span></label>
                <select v-model="userForm.role" class="form-input" required @change="onRoleChange">
                  <option value="">-- Pilih Role --</option>
                  <option v-for="(label, val) in availableRoles" :key="val" :value="val">{{ label }}</option>
                </select>
              </div>
              <div>
                <label class="form-label">Unit</label>
                <select v-model="userForm.unit_id" class="form-input" :disabled="!needsUnit">
                  <option value="">-- Tanpa Unit (Lintas Unit) --</option>
                  <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div v-if="userForm.role === 'asp_wilayah'">
                <label class="form-label">Wilayah ASP <span style="color:var(--color-error);">*</span></label>
                <select v-model="userForm.asp_territory" class="form-input">
                  <option value="kubu_raya">Kubu Raya</option>
                  <option value="regu">Regu</option>
                  <option value="ibc">IBC</option>
                  <option value="kota">Kota</option>
                  <option value="itbss">ITBSS</option>
                </select>
              </div>

              <div class="col-span-2" style="display:flex;justify-content:flex-end;gap:0.75rem;padding-top:0.75rem;border-top:1px solid var(--border-color);margin-top:0.5rem;">
                <button type="button" class="btn btn-outline" @click="closeUserModal">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                  <span v-if="isSubmitting" class="spinner"></span>
                  {{ editingUser ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                </button>
              </div>
            </form>

            <div v-if="generatedPassword" style="display:flex;justify-content:flex-end;margin-top:1rem;">
              <button class="btn btn-primary" @click="closeUserModal">Selesai & Tutup</button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- ══════ MODAL: RESET PASSWORD ══════ -->
    <teleport to="body">
      <transition name="modal">
        <div v-if="showPwModal" class="modal-overlay" @click.self="showPwModal = false">
          <div class="modal-card-wide" style="max-width:400px;" role="dialog">
            <div class="modal-header">
              <h2>Reset Password — {{ pwTarget?.name }}</h2>
              <button class="btn btn-ghost btn-icon" @click="showPwModal = false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <form @submit.prevent="submitResetPw" style="margin-top:1rem;display:flex;flex-direction:column;gap:1rem;">
              <div>
                <label class="form-label">Password Baru <span style="color:var(--color-error);">*</span></label>
                <input v-model="pwForm.new_password" type="password" class="form-input" placeholder="Min. 8 karakter" required minlength="8" />
              </div>
              <div>
                <label class="form-label">Konfirmasi Password Baru <span style="color:var(--color-error);">*</span></label>
                <input v-model="pwForm.new_password_confirmation" type="password" class="form-input" placeholder="Ulangi password baru" required />
              </div>
              <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                <button type="button" class="btn btn-outline" @click="showPwModal = false">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                  <span v-if="isSubmitting" class="spinner"></span>
                  Reset Password
                </button>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </teleport>

    <ConfirmModal />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';
import { useConfirm } from '@/composables/useConfirm';
import ConfirmModal from '@/components/ConfirmModal.vue';
import api from '@/services/api';

const authStore = useAuthStore();
const toast = useToast();
const { confirm } = useConfirm();

// ── State ────────────────────────────────────────────────────
const users = ref([]);
const units = ref([]);
const availableRoles = ref({});
const isLoading = ref(true);
const isSubmitting = ref(false);
const meta = ref({ current_page: 1, last_page: 1, total: 0, per_page: 20 });

const filters = ref({ search: '', role: '', is_active: '' });

// Modals
const showUserModal = ref(false);
const editingUser   = ref(null);
const generatedPassword = ref('');
const userForm = ref(defaultUserForm());

const showPwModal = ref(false);
const pwTarget = ref(null);
const pwForm = ref({ new_password: '', new_password_confirmation: '' });

// ── Computed ────────────────────────────────────────────────
const canCreate = computed(() => ['superadmin', 'kd_sarpras', 'admin_sarpras'].includes(authStore.user?.role));
const needsUnit  = computed(() => ['kepsek', 'dept_ygpkb'].includes(userForm.value.role));

const allRoles = {
  superadmin: 'Super Administrator', kepsek: 'Kepala Sekolah',
  dept_ygpkb: 'Departemen YGPKB', asp_wilayah: 'ASP Wilayah',
  kd_sarpras: 'KD Sarpras', admin_sarpras: 'Admin Sarpras',
  koordinator_ski: 'Koordinator SKI', koordinator_utama: 'Koordinator Utama',
};

// ── Sorting ──────────────────────────────────────────────────
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

const sortedUsers = computed(() => {
  let list = users.value.slice();
  list.sort((a, b) => {
    let cmp = 0;
    if (sortBy.value === 'name') {
      cmp = (a.name || '').localeCompare(b.name || '', 'id-ID');
    } else if (sortBy.value === 'role') {
      cmp = (a.role || '').localeCompare(b.role || '', 'id-ID');
    } else if (sortBy.value === 'unit') {
      const uA = a.unit?.name || '';
      const uB = b.unit?.name || '';
      cmp = uA.localeCompare(uB, 'id-ID');
    } else if (sortBy.value === 'status') {
      cmp = Number(b.is_active) - Number(a.is_active);
    }
    return sortDir.value === 'asc' ? cmp : -cmp;
  });
  return list;
});

// ── Lifecycle ───────────────────────────────────────────────
onMounted(async () => {
  await Promise.all([fetchUsers(), fetchUnits(), fetchAvailableRoles()]);
});

// ── Fetch ───────────────────────────────────────────────────
async function fetchUsers(page = 1) {
  isLoading.value = true;
  try {
    const res = await api.get('/users', { params: { ...filters.value, page, per_page: 20 } });
    users.value = res.data || [];
    meta.value  = res.meta || meta.value;
  } catch { toast.error('Gagal memuat daftar pengguna.'); }
  finally { isLoading.value = false; }
}
async function fetchUnits() {
  try { const r = await api.get('/master/units'); units.value = r.data || []; } catch { /**/ }
}
async function fetchAvailableRoles() {
  try { const r = await api.get('/users/available-roles'); availableRoles.value = r.data || {}; } catch { /**/ }
}

let debounceTimer = null;
function debouncedFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => fetchUsers(), 400);
}
function resetFilters() { filters.value = { search: '', role: '', is_active: '' }; fetchUsers(); }
function goToPage(p) { fetchUsers(p); }

// ── Modal helpers ────────────────────────────────────────────
function defaultUserForm() {
  return { name: '', username: '', email: '', phone_number: '', role: '', unit_id: '', asp_territory: 'none' };
}
function openCreateModal() {
  editingUser.value = null; generatedPassword.value = ''; userForm.value = defaultUserForm(); showUserModal.value = true;
}
function openEditModal(user) {
  editingUser.value = user; generatedPassword.value = '';
  userForm.value = { name: user.name, username: user.username, email: user.email, phone_number: user.phone_number || '', role: user.role, unit_id: user.unit_id || '', asp_territory: user.asp_territory || 'none' };
  showUserModal.value = true;
}
function closeUserModal() { showUserModal.value = false; editingUser.value = null; generatedPassword.value = ''; }

function openResetPwModal(user) {
  pwTarget.value = user; pwForm.value = { new_password: '', new_password_confirmation: '' }; showPwModal.value = true;
}

function onRoleChange() {
  if (!needsUnit.value) userForm.value.unit_id = '';
  if (userForm.value.role !== 'asp_wilayah') userForm.value.asp_territory = 'none';
}

function copyPw() {
  navigator.clipboard.writeText(generatedPassword.value);
  toast.info('Password disalin ke clipboard.', 'Tersalin!');
}

// ── Submit ───────────────────────────────────────────────────
async function submitUserForm() {
  isSubmitting.value = true;
  try {
    if (editingUser.value) {
      await api.put(`/users/${editingUser.value.id}`, userForm.value);
      toast.success(`Profil ${userForm.value.name} berhasil diperbarui.`);
      closeUserModal();
    } else {
      const res = await api.post('/users', userForm.value);
      generatedPassword.value = res.generated_password || '';
      toast.success(`Akun ${userForm.value.name} berhasil dibuat!`);
    }
    await fetchUsers();
  } catch (e) {
    const msg = e?.errors ? Object.values(e.errors).flat().join(', ') : (e?.message || 'Gagal menyimpan data.');
    toast.error(msg, 'Kesalahan');
  } finally { isSubmitting.value = false; }
}

async function submitResetPw() {
  if (pwForm.value.new_password !== pwForm.value.new_password_confirmation) {
    toast.error('Konfirmasi password tidak cocok.'); return;
  }
  isSubmitting.value = true;
  try {
    await api.post(`/users/${pwTarget.value.id}/change-password`, pwForm.value);
    toast.success(`Password ${pwTarget.value.name} berhasil direset.`);
    showPwModal.value = false;
  } catch (e) {
    toast.error(e?.message || 'Gagal mereset password.');
  } finally { isSubmitting.value = false; }
}

async function handleToggleActive(user) {
  const action = user.is_active ? 'menonaktifkan' : 'mengaktifkan kembali';
  const confirmed = await confirm({
    title: `${user.is_active ? 'Nonaktifkan' : 'Aktifkan'} Akun`,
    message: `Apakah Anda yakin ingin ${action} akun ${user.name} (${formatRole(user.role)})?`,
    confirmLabel: user.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan',
    type: user.is_active ? 'danger' : 'info',
  });
  if (!confirmed) return;
  try {
    await api.post(`/users/${user.id}/toggle-active`);
    const label = user.is_active ? 'dinonaktifkan' : 'diaktifkan kembali';
    toast.success(`Akun ${user.name} berhasil ${label}.`);
    await fetchUsers();
  } catch (e) {
    toast.error(e?.message || 'Gagal mengubah status akun.');
  }
}

// ── Formatters ───────────────────────────────────────────────
const formatRole = (r) => allRoles[r] || r;
const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day:'numeric', month:'short', year:'numeric' }) : '—';
const getInitials = (name) => name?.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() || '?';
const roleBadgeClass = (role) => ({
  superadmin: 'badge-error', kd_sarpras: 'badge-success', admin_sarpras: 'badge-info',
  asp_wilayah: 'badge-warning', koordinator_utama: 'badge-neutral', koordinator_ski: 'badge-neutral',
}[role] || 'badge-neutral');
</script>

<style scoped>
.filter-bar { display: flex; align-items: center; gap: 0.75rem; flex-wrap: nowrap; overflow: hidden; }
.filter-search-wrap { position: relative; flex: 1; min-width: 180px; }
.filter-search-icon { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted); pointer-events: none; }

/* Filter controls: uniform height */
.filter-select {
  height: 42px;
  width: auto;
  min-width: 155px;
  max-width: 200px;
  padding: 0 2.25rem 0 0.875rem;
  font-size: 0.875rem;
  font-family: inherit;
  background: var(--bg-input);
  border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md);
  color: var(--text-primary);
  outline: none;
  cursor: pointer;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.625rem center;
  flex-shrink: 0;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.filter-select:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
}

/* Refresh button same height as filter controls */
.filter-refresh-btn {
  height: 42px;
  padding: 0 1.125rem;
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.875rem;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Delete/toggle button — same icon size as other btn-icon */
.btn-danger-ghost {
  background: var(--color-error-bg);
  color: var(--color-error);
  border: 1px solid var(--color-error-border);
}
.btn-danger-ghost:hover:not(:disabled) {
  background: var(--color-error);
  color: #fff;
}

.user-avatar {
  width: 36px; height: 36px; border-radius: 10px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
  color: #fff; font-size: 0.75rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.territory-tag { display: inline-block; font-size: 0.65rem; background: var(--color-info-bg); color: var(--color-info); border: 1px solid var(--color-info-border); border-radius: 999px; padding: 0.1rem 0.45rem; margin-left: 0.375rem; }
.empty-state { text-align: center; padding: 3rem 1rem; }

.pagination { display: flex; align-items: center; justify-content: center; gap: 1rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border-color); }

/* ── MODALS ── */
.modal-overlay { position: fixed; inset: 0; z-index: 10000; display: flex; align-items: center; justify-content: center; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); padding: 1rem; }
.modal-card-wide { background: var(--bg-card); border-radius: 20px; padding: 1.75rem; width: 100%; max-width: 560px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.modal-header h2 { font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }

.generated-pw-box { background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-md); padding: 1rem 1.25rem; color: var(--color-success); }
.generated-pw-code { display: block; font-size: 1.25rem; font-weight: 800; letter-spacing: 0.1em; margin: 0.5rem 0; color: var(--color-primary); background: var(--bg-card); padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px dashed var(--border-color); }

.form-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.col-span-2 { grid-column: 1 / -1; }
@media (max-width: 520px) { .form-grid-2 { grid-template-columns: 1fr; } .col-span-2 { grid-column: auto; } }

.spinner { width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

.modal-enter-active { animation: modal-in 0.3s cubic-bezier(0.34,1.56,0.64,1); }
.modal-leave-active { animation: modal-out 0.2s ease forwards; }
@keyframes modal-in  { from { opacity: 0; transform: scale(0.85) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
@keyframes modal-out { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.9); } }
</style>
