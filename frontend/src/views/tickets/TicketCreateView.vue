<template>
  <div>
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title">Buat Tiket Baru</h1>
      <p class="page-subtitle">Isi formulir sesuai jenis kebutuhan yang akan diajukan.</p>
    </div>

    <div class="card">
      <form @submit.prevent="submitTicket" novalidate>

        <!-- ════════════════════════════════════════════
             BAGIAN 1: KATEGORI PROSEDUR
        ════════════════════════════════════════════ -->
        <section class="form-section">
          <div class="form-section-header">
            <span class="form-section-num">1</span>
            <div>
              <h3 class="form-section-title">Kategori Prosedur</h3>
              <p class="form-section-sub">Pilih jenis prosedur yang sesuai dengan kebutuhan Anda.</p>
            </div>
          </div>

          <div class="procedure-cards">
            <label
              v-for="proc in procedures"
              :key="proc.value"
              class="procedure-card"
              :class="{ selected: form.procedure_type === proc.value }"
            >
              <input type="radio" v-model="form.procedure_type" :value="proc.value" class="sr-only" />
              <span class="procedure-icon">{{ proc.icon }}</span>
              <span class="procedure-label">{{ proc.label }}</span>
              <span class="procedure-desc">{{ proc.desc }}</span>
            </label>
          </div>
        </section>

        <!-- ════════════════════════════════════════════
             BAGIAN 2: LOKASI (UMUM — semua tipe)
        ════════════════════════════════════════════ -->
        <section class="form-section" v-if="form.procedure_type !== 'mutasi'">
          <div class="form-section-header">
            <span class="form-section-num">2</span>
            <div>
              <h3 class="form-section-title">Lokasi</h3>
              <p class="form-section-sub">Unit dan ruangan tempat kebutuhan/kendala berada.</p>
            </div>
          </div>
          <SmartCascadingLocation
            v-model:unitId="form.unit_id"
            v-model:locationName="form.location_name"
            v-model:specificLoc="form.specific_location"
          />
        </section>

        <!-- ════════════════════════════════════════════
             BAGIAN 3A: FORM PERBAIKAN (Korektif)
        ════════════════════════════════════════════ -->
        <template v-if="form.procedure_type === 'perbaikan'">
          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">3</span>
              <div><h3 class="form-section-title">Detail Kendala & Perbaikan</h3></div>
            </div>
            <div class="form-grid-2">
              <div class="col-span-2">
                <label class="form-label">Judul Kendala <span class="req">*</span></label>
                <input v-model="form.title" type="text" class="form-input" placeholder="Cth: AC Ruang Guru bocor dan tidak dingin" required />
              </div>
              <div class="col-span-2">
                <label class="form-label">Kategori Objek <span class="req">*</span></label>
                <div class="radio-group">
                  <label class="radio-option" :class="{ active: form.target_object_category === 'fasilitas_ruangan' }">
                    <input type="radio" v-model="form.target_object_category" value="fasilitas_ruangan" />
                    🏢 Fasilitas Ruangan (dinding, atap, lantai, instalasi listrik)
                  </label>
                  <label class="radio-option" :class="{ active: form.target_object_category === 'barang_inventaris' }">
                    <input type="radio" v-model="form.target_object_category" value="barang_inventaris" />
                    📦 Barang Inventaris (AC, komputer, proyektor, meja, dll)
                  </label>
                </div>
              </div>
              <div v-if="form.target_object_category === 'barang_inventaris'">
                <label class="form-label">Nama Barang <span class="req">*</span></label>
                <input v-model="form.item_name" type="text" class="form-input" placeholder="Cth: AC Panasonic 2 PK" />
              </div>
              <div v-if="form.target_object_category === 'barang_inventaris'">
                <label class="form-label">Kode Inventaris / Label Aset</label>
                <input v-model="form.inventory_code" type="text" class="form-input" placeholder="Cth: INV-AC-2023-001 atau -" />
              </div>
              <div class="col-span-2">
                <label class="form-label">Akar Masalah / Uraian Kerusakan <span class="req">*</span></label>
                <textarea v-model="form.problem_cause" rows="3" class="form-input" placeholder="Jelaskan penyebab kerusakan atau kondisi yang ditemukan..."></textarea>
              </div>
              <div>
                <label class="form-label">Tingkat Urgensi <span class="req">*</span></label>
                <select v-model="form.urgency_level" class="form-input">
                  <option value="rendah">🟢 Rendah — Tidak mengganggu operasional</option>
                  <option value="sedang">🟡 Sedang — Mengganggu namun masih bisa dipakai</option>
                  <option value="tinggi_darurat">🔴 Darurat — Harus segera ditangani</option>
                </select>
              </div>
              <div>
                <label class="form-label">Estimasi Biaya Perbaikan (Rp)</label>
                <input v-model="form.estimated_cost" type="number" min="0" step="1000" class="form-input" placeholder="0" />
              </div>
            </div>
          </section>
        </template>

        <!-- ════════════════════════════════════════════
             BAGIAN 3B: FORM PENGADAAN Barang Baru
        ════════════════════════════════════════════ -->
        <template v-if="form.procedure_type === 'pengadaan'">
          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">3</span>
              <div><h3 class="form-section-title">Detail Usulan Pengadaan</h3></div>
            </div>
            <div class="form-grid-2">
              <div class="col-span-2">
                <label class="form-label">Judul Usulan Pengadaan <span class="req">*</span></label>
                <input v-model="form.title" type="text" class="form-input" placeholder="Cth: Pengadaan AC untuk Lab Komputer Lantai 2" required />
              </div>
              <div class="col-span-2">
                <label class="form-label">Justifikasi / Urgensi Kebutuhan <span class="req">*</span></label>
                <textarea v-model="form.problem_cause" rows="4" class="form-input" placeholder="Jelaskan mengapa pengadaan ini diperlukan, dampak jika tidak diadakan, dan dasar pertimbangan..."></textarea>
              </div>
              <div>
                <label class="form-label">Tingkat Urgensi <span class="req">*</span></label>
                <select v-model="form.urgency_level" class="form-input">
                  <option value="rendah">🟢 Rendah</option>
                  <option value="sedang">🟡 Sedang</option>
                  <option value="tinggi_darurat">🔴 Darurat / Prioritas Tinggi</option>
                </select>
              </div>
              <div>
                <label class="form-label">Ruangan Tujuan Penerimaan Barang</label>
                <input v-model="form.specific_location" type="text" class="form-input" placeholder="Cth: Lab Komputer Lt.2, atau sesuai pilihan ruangan di atas" />
              </div>
            </div>
          </section>

          <!-- Tabel Dinamis Item Belanja -->
          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">4</span>
              <div>
                <h3 class="form-section-title">Daftar Item Belanja</h3>
                <p class="form-section-sub">Tambahkan setiap barang yang ingin diadakan beserta estimasi harganya.</p>
              </div>
            </div>

            <div class="item-table-wrapper">
              <table class="item-table">
                <thead>
                  <tr>
                    <th style="width:35%">Nama Barang / Spesifikasi</th>
                    <th style="width:8%">Jumlah</th>
                    <th style="width:10%">Satuan</th>
                    <th style="width:17%">Harga Satuan (Rp)</th>
                    <th style="width:17%">Subtotal (Rp)</th>
                    <th style="width:13%">Catatan</th>
                    <th style="width:5%"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in form.items" :key="idx">
                    <td><input v-model="item.item_description" type="text" class="table-input" placeholder="Nama barang & spek" required /></td>
                    <td><input v-model.number="item.quantity" type="number" min="1" class="table-input" @input="calcSubtotal(idx)" /></td>
                    <td>
                      <select v-model="item.unit_metric" class="table-input">
                        <option>Unit</option><option>Pcs</option><option>Set</option><option>Buah</option><option>Meter</option><option>Kg</option><option>Rim</option><option>Pak</option>
                      </select>
                    </td>
                    <td><input v-model.number="item.estimated_unit_price" type="number" min="0" step="1000" class="table-input" @input="calcSubtotal(idx)" placeholder="0" /></td>
                    <td class="subtotal-cell">{{ formatRp(item.estimated_subtotal) }}</td>
                    <td><input v-model="item.item_notes" type="text" class="table-input" placeholder="Opsional" /></td>
                    <td>
                      <button type="button" class="btn btn-icon btn-sm" style="color:var(--color-error);background:var(--color-error-bg);" @click="removeItem(idx)" :disabled="form.items.length === 1" title="Hapus baris">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="4" style="padding:0.75rem 0.5rem;font-weight:700;color:var(--text-secondary);">Total Estimasi Biaya</td>
                    <td style="padding:0.75rem 0.5rem;font-weight:800;font-size:1rem;color:var(--color-primary);">{{ formatRp(totalEstimate) }}</td>
                    <td colspan="2"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            <button type="button" class="btn btn-outline btn-sm" style="margin-top:0.75rem;" @click="addItem">
              + Tambah Baris Item
            </button>
          </section>
        </template>

        <!-- ════════════════════════════════════════════
             BAGIAN 3C: FORM MUTASI Barang
        ════════════════════════════════════════════ -->
        <template v-if="form.procedure_type === 'mutasi'">
          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">2</span>
              <div><h3 class="form-section-title">Lokasi Asal & Tujuan Pemindahan</h3></div>
            </div>
            <div class="form-grid-2">
              <div>
                <label class="form-label">Unit Asal <span class="req">*</span></label>
                <!-- Kepsek: unit terkunci (read-only) -->
                <div v-if="isUnitLocked" class="mutasi-unit-locked">
                  <span>🏢</span>
                  <span class="mutasi-unit-name">{{ units.find(u => u.id == form.unit_id)?.name || '...' }}</span>
                  <span title="Unit dikunci sesuai akun Anda">🔒</span>
                </div>
                <!-- Role lain: dropdown bebas -->
                <select v-else v-model="form.unit_id" class="form-input" @change="form.location_name = ''">
                  <option value="">-- Pilih Unit Asal --</option>
                  <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div>
                <label class="form-label">Ruangan / Lokasi Asal <span class="req">*</span></label>
                <div class="mutasi-loc-wrap" :class="{ 'disabled-wrap': !form.unit_id }">
                  <span class="mutasi-loc-icon">📍</span>
                  <input
                    v-model="form.location_name"
                    type="text"
                    class="form-input mutasi-loc-input"
                    :placeholder="form.unit_id ? 'Cth: Lab Komputer Lt.2, Ruang Guru...' : 'Pilih unit asal dahulu'"
                    maxlength="150"
                    :disabled="!form.unit_id"
                  />
                </div>
              </div>
              <div>
                <label class="form-label">Unit Tujuan <span class="req">*</span></label>
                <select v-model="form.target_unit_id" class="form-input">
                  <option value="">-- Pilih Unit Tujuan --</option>
                  <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
              </div>
              <div>
                <label class="form-label">Ruangan / Lokasi Tujuan <span class="req">*</span></label>
                <div class="mutasi-loc-wrap" :class="{ 'disabled-wrap': !form.target_unit_id }">
                  <span class="mutasi-loc-icon">🎯</span>
                  <input
                    v-model="form.target_location_name"
                    type="text"
                    class="form-input mutasi-loc-input"
                    :placeholder="form.target_unit_id ? 'Cth: Aula Utama, Ruang Kelas B3...' : 'Pilih unit tujuan dahulu'"
                    maxlength="150"
                    :disabled="!form.target_unit_id"
                  />
                </div>
              </div>
            </div>
          </section>

          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">3</span>
              <div><h3 class="form-section-title">Detail Pemindahan</h3></div>
            </div>
            <div class="form-grid-2">
              <div class="col-span-2">
                <label class="form-label">Judul Permohonan Mutasi <span class="req">*</span></label>
                <input v-model="form.title" type="text" class="form-input" placeholder="Cth: Pemindahan 3 Unit Komputer dari Lab A ke Lab B" required />
              </div>
              <div class="col-span-2">
                <label class="form-label">Alasan Pemindahan <span class="req">*</span></label>
                <textarea v-model="form.problem_cause" rows="3" class="form-input" placeholder="Jelaskan alasan pemindahan barang ini..."></textarea>
              </div>
              <div>
                <label class="form-label">Tingkat Urgensi</label>
                <select v-model="form.urgency_level" class="form-input">
                  <option value="rendah">🟢 Rendah</option>
                  <option value="sedang">🟡 Sedang</option>
                  <option value="tinggi_darurat">🔴 Segera / Mendesak</option>
                </select>
              </div>
            </div>
          </section>

          <!-- Tabel Inventaris Mutasi -->
          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">4</span>
              <div>
                <h3 class="form-section-title">Daftar Inventaris yang Dipindahkan</h3>
                <p class="form-section-sub">Cantumkan setiap barang yang akan dimutasi beserta kondisi dan foto fisiknya.</p>
              </div>
            </div>

            <div class="item-table-wrapper">
              <table class="item-table">
                <thead>
                  <tr>
                    <th>Kode Aset / Label</th>
                    <th>Nama Barang</th>
                    <th>Jumlah</th>
                    <th>Kondisi</th>
                    <th>Catatan</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, idx) in form.items" :key="idx">
                    <td><input v-model="item.inventory_code" type="text" class="table-input" placeholder="INV-001 atau -" /></td>
                    <td><input v-model="item.item_description" type="text" class="table-input" placeholder="Nama barang" required /></td>
                    <td><input v-model.number="item.quantity" type="number" min="1" class="table-input" /></td>
                    <td>
                      <select v-model="item.condition_status" class="table-input">
                        <option value="baik">✅ Baik</option>
                        <option value="rusak_ringan">⚠️ Rusak Ringan</option>
                      </select>
                    </td>
                    <td><input v-model="item.item_notes" type="text" class="table-input" placeholder="Opsional" /></td>
                    <td>
                      <button type="button" class="btn btn-icon btn-sm" style="color:var(--color-error);background:var(--color-error-bg);" @click="removeItem(idx)" :disabled="form.items.length === 1">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <button type="button" class="btn btn-outline btn-sm" style="margin-top:0.75rem;" @click="addItem">
              + Tambah Baris Inventaris
            </button>
          </section>
        </template>

        <!-- ════════════════════════════════════════════
             BAGIAN 3D: FORM PEMUTIHAN (Disposal)
        ════════════════════════════════════════════ -->
        <template v-if="form.procedure_type === 'pemutihan'">
          <section class="form-section">
            <div class="form-section-header">
              <span class="form-section-num">3</span>
              <div><h3 class="form-section-title">Detail Pemutihan / Disposal</h3></div>
            </div>
            <div class="form-grid-2">
              <div class="col-span-2">
                <label class="form-label">Judul / Nama Aset yang Diusulkan <span class="req">*</span></label>
                <input v-model="form.title" type="text" class="form-input" placeholder="Cth: Pemutihan 5 Unit Komputer Rusak Berat" required />
              </div>
              <div class="col-span-2">
                <label class="form-label">Alasan Pemutihan / Kondisi Aset <span class="req">*</span></label>
                <textarea v-model="form.problem_cause" rows="3" class="form-input" placeholder="Jelaskan kondisi aset dan mengapa perlu dimusnahkan/dilepas..."></textarea>
              </div>
              <div>
                <label class="form-label">Tindakan yang Diusulkan <span class="req">*</span></label>
                <select v-model="form.disposal_action" class="form-input">
                  <option value="dimusnahkan">🗑️ Dimusnahkan</option>
                  <option value="dijual">💰 Dijual / Dilelang</option>
                  <option value="dialihkan_disumbangkan">🤝 Dialihkan / Disumbangkan</option>
                </select>
              </div>
              <div>
                <label class="form-label">Estimasi Nilai Sisa / Nilai Lelang (Rp)</label>
                <input v-model.number="form.estimated_salvage_value" type="number" min="0" step="1000" class="form-input" placeholder="0" />
              </div>
              <div class="col-span-2">
                <label class="form-label">Keterangan Tujuan / Catatan Tambahan</label>
                <textarea v-model="form.disposal_destination_or_notes" rows="2" class="form-input" placeholder="Penerima sumbangan, lokasi pemusnahan, atau catatan lain..."></textarea>
              </div>
            </div>
          </section>
        </template>

        <!-- ════════════════════════════════════════════
             BAGIAN FOTO (semua tipe)
        ════════════════════════════════════════════ -->
        <section class="form-section">
          <div class="form-section-header">
            <span class="form-section-num">{{ photoSectionNum }}</span>
            <div>
              <h3 class="form-section-title">Lampiran Foto</h3>
              <p class="form-section-sub">Upload foto bukti kondisi / barang (maks. 5 foto, tiap file maks. 2MB).</p>
            </div>
          </div>
          <ImageCompressorUploader v-model="form.photos" :maxFiles="5" label="" />
        </section>

        <!-- ════════════════════════════════════════════
             TOMBOL SUBMIT
        ════════════════════════════════════════════ -->
        <div class="form-actions">
          <button type="button" class="btn btn-outline" @click="$router.push('/dashboard')">
            Batal
          </button>
          <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner"></span>
            {{ isSubmitting ? 'Mengajukan...' : 'Ajukan Tiket' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useTicketStore } from '@/stores/ticket';
import { useToast } from '@/composables/useToast';
import { useAuthStore } from '@/stores/auth';
import SmartCascadingLocation from '@/components/SmartCascadingLocation.vue';
import ImageCompressorUploader from '@/components/ImageCompressorUploader.vue';
import api from '@/services/api';

const router = useRouter();
const ticketStore = useTicketStore();
const toast = useToast();
const authStore = useAuthStore();

// Unit terkunci untuk kepsek & dept_ygpkb
const isUnitLocked = computed(() => {
  const role = authStore.user?.role;
  return (role === 'kepsek' || role === 'dept_ygpkb') && !!authStore.user?.unit_id;
});

const procedures = [
  { value: 'perbaikan',   icon: '🔧', label: 'Perbaikan',  desc: 'Kerusakan fasilitas/inventaris yang perlu diperbaiki' },
  { value: 'pengadaan',   icon: '🛒', label: 'Pengadaan',  desc: 'Pengajuan pembelian barang/aset baru' },
  { value: 'mutasi',      icon: '🚚', label: 'Mutasi',     desc: 'Pemindahan aset antar unit/ruangan' },
  { value: 'pemutihan',   icon: '🗑️', label: 'Pemutihan', desc: 'Penghapusan/disposal aset yang sudah tidak layak' },
];

const form = ref({
  procedure_type: 'perbaikan',
  // Lokasi
  unit_id: '', location_name: '', specific_location: '',
  // Mutasi: Tujuan
  target_unit_id: '', target_location_name: '',
  // Umum
  title: '', urgency_level: 'sedang', problem_cause: '',
  target_object_category: 'fasilitas_ruangan',
  item_name: '', inventory_code: '',
  estimated_cost: 0,
  // Pemutihan
  disposal_action: 'dimusnahkan',
  disposal_destination_or_notes: '',
  estimated_salvage_value: 0,
  // Items (dinamis)
  items: [defaultItem()],
  photos: [],
});

const units = ref([]);
const isSubmitting = ref(false);

function defaultItem() {
  return { inventory_code: '-', item_description: '', quantity: 1, unit_metric: 'Unit', estimated_unit_price: 0, estimated_subtotal: 0, condition_status: 'baik', item_notes: '' };
}

function addItem() { form.value.items.push(defaultItem()); }
function removeItem(idx) { if (form.value.items.length > 1) form.value.items.splice(idx, 1); }
function calcSubtotal(idx) {
  const item = form.value.items[idx];
  item.estimated_subtotal = (item.quantity || 0) * (item.estimated_unit_price || 0);
}
const totalEstimate = computed(() => form.value.items.reduce((s, i) => s + (i.estimated_subtotal || 0), 0));

const photoSectionNum = computed(() => {
  if (form.value.procedure_type === 'perbaikan') return 4;
  if (form.value.procedure_type === 'pengadaan') return 5;
  if (form.value.procedure_type === 'mutasi') return 5;
  return 4;
});

function formatRp(val) {
  if (!val) return 'Rp 0';
  return 'Rp ' + Number(val).toLocaleString('id-ID');
}

// Load units for mutasi cascading + auto-set unit untuk kepsek
onMounted(async () => {
  try {
    const res = await api.get('/master/units');
    units.value = res.data || [];
  } catch { /* ignore */ }

  // Kepsek & dept_ygpkb: unit sudah ditentukan dari akun login
  if (isUnitLocked.value && authStore.user?.unit_id) {
    form.value.unit_id = authStore.user.unit_id;
  }
});


// Reset items when switching procedure type
watch(() => form.value.procedure_type, () => {
  form.value.items = [defaultItem()];
  form.value.title = '';
  form.value.problem_cause = '';
});

const submitTicket = async () => {
  // Simple validation
  if (!form.value.title.trim()) {
    toast.error('Judul tiket wajib diisi.', 'Validasi Gagal');
    return;
  }

  isSubmitting.value = true;
  try {
    const payload = new FormData();
    // Core fields
    const fields = ['procedure_type', 'unit_id', 'location_name', 'specific_location', 'title', 'urgency_level', 'problem_cause', 'estimated_cost', 'target_object_category', 'item_name', 'inventory_code', 'target_unit_id', 'target_location_name', 'disposal_action', 'disposal_destination_or_notes', 'estimated_salvage_value'];
    fields.forEach(k => { if (form.value[k] !== undefined && form.value[k] !== null) payload.append(k, form.value[k]); });

    // Items
    form.value.items.forEach((item, i) => {
      Object.keys(item).forEach(k => payload.append(`items[${i}][${k}]`, item[k] ?? ''));
    });

    // Photos
    form.value.photos.forEach((photo, i) => payload.append(`photos[${i}]`, photo));

    await ticketStore.createTicket(payload);
    toast.success('Tiket berhasil diajukan dan sedang menunggu proses review.', 'Tiket Dikirim!');
    router.push('/dashboard');
  } catch (error) {
    const msg = error?.message || error?.errors ? Object.values(error.errors || {}).flat().join(', ') : 'Gagal mengajukan tiket. Periksa kembali isian form.';
    toast.error(msg, 'Pengajuan Gagal');
  } finally {
    isSubmitting.value = false;
  }
};
</script>

<style scoped>
/* ── FORM SECTION ── */
.form-section {
  padding: 1.75rem 0;
  border-bottom: 1px solid var(--border-color);
}
.form-section:last-of-type { border-bottom: none; }
.form-section-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.25rem;
}
.form-section-num {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--color-primary);
  color: #fff;
  font-size: 0.875rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}
.form-section-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); }
.form-section-sub { font-size: 0.825rem; color: var(--text-muted); margin-top: 2px; }

/* ── PROCEDURE CARDS ── */
.procedure-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 0.875rem;
}
.procedure-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 1.25rem 1rem;
  border: 2px solid var(--border-color);
  border-radius: var(--radius-lg);
  cursor: pointer;
  transition: all 0.2s;
  background: var(--bg-card);
}
.procedure-card:hover { border-color: var(--color-primary); background: var(--color-info-bg); }
.procedure-card.selected { border-color: var(--color-primary); background: rgba(26,58,107,0.06); box-shadow: 0 0 0 3px rgba(26,58,107,0.1); }
.procedure-icon { font-size: 2rem; margin-bottom: 0.5rem; }
.procedure-label { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); }
.procedure-desc { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.25rem; line-height: 1.4; }

/* ── FORM GRID ── */
.form-grid-2 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}
.col-span-2 { grid-column: 1 / -1; }
@media (max-width: 600px) { .form-grid-2 { grid-template-columns: 1fr; } .col-span-2 { grid-column: auto; } }

.req { color: var(--color-error); }

/* ── RADIO GROUP ── */
.radio-group { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.radio-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  border: 1.5px solid var(--border-color);
  border-radius: var(--radius-md);
  cursor: pointer;
  font-size: 0.875rem;
  color: var(--text-secondary);
  transition: all 0.2s;
}
.radio-option.active { border-color: var(--color-primary); background: rgba(26,58,107,0.05); color: var(--color-primary); font-weight: 600; }
.radio-option input { display: none; }

/* ── ITEM TABLE ── */
.item-table-wrapper { overflow-x: auto; border: 1px solid var(--border-color); border-radius: var(--radius-md); }
.item-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; min-width: 700px; }
.item-table thead tr { background: var(--bg-page); }
.item-table thead th { padding: 0.625rem 0.5rem; text-align: left; font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.04em; white-space: nowrap; border-bottom: 1px solid var(--border-color); }
.item-table tbody tr { border-bottom: 1px solid var(--border-color); }
.item-table tbody tr:last-child { border-bottom: none; }
.item-table td { padding: 0.375rem 0.375rem; vertical-align: middle; }
.item-table tfoot td { border-top: 2px solid var(--border-color); background: var(--bg-page); }
.table-input {
  width: 100%;
  padding: 0.4rem 0.5rem;
  font-size: 0.85rem;
  font-family: inherit;
  background: var(--bg-input);
  border: 1px solid var(--border-input);
  border-radius: 6px;
  color: var(--text-primary);
  outline: none;
}
.table-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 2px rgba(26,58,107,0.1); }
.subtotal-cell { font-weight: 700; color: var(--color-primary); white-space: nowrap; padding: 0.375rem 0.5rem; }

/* ── ACTIONS ── */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
  margin-top: 0;
}
.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}
/* ── MUTASI LOCATION INPUT ── */
.mutasi-loc-wrap {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--bg-input);
  border: 1.5px solid var(--border-input);
  border-radius: var(--radius-md);
  padding: 0 0.75rem;
  transition: border-color 0.2s, box-shadow 0.2s, opacity 0.2s;
}
.mutasi-loc-wrap.disabled-wrap {
  opacity: 0.6;
  background: var(--bg-page);
  pointer-events: none;
}
.mutasi-loc-wrap:focus-within {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(26,58,107,0.1);
}
.mutasi-loc-icon {
  font-size: 1.1rem;
  flex-shrink: 0;
}
.mutasi-loc-input {
  border: none !important;
  box-shadow: none !important;
  background: transparent !important;
  padding-left: 0 !important;
  flex: 1;
}

/* ── MUTASI UNIT LOCKED ── */
.mutasi-unit-locked {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  background: var(--bg-page, #f7f8fc);
  border: 1.5px solid var(--border-color, #e2e8f0);
  border-radius: var(--radius-md, 10px);
  padding: 0.75rem 0.875rem;
  min-height: 46px;
  font-size: 0.9rem;
}
.mutasi-unit-name {
  flex: 1;
  font-weight: 600;
  color: var(--text-primary, #1a202c);
}

@keyframes spin { to { transform: rotate(360deg); } }


</style>
