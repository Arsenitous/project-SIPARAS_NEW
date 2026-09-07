<template>
  <div class="scloc-wrapper">
    <!-- Unit Selector -->
    <div class="scloc-field">
      <label class="scloc-label">
        Unit Kerja <span class="scloc-req">*</span>
      </label>
      <div class="scloc-select-wrap" :class="{ focused: unitFocused, locked: isUnitLocked }">
        <span class="scloc-icon">🏢</span>

        <!-- Jika unit terkunci (kepsek / dept_ygpkb) → tampilkan read-only -->
        <template v-if="isUnitLocked">
          <span class="scloc-locked-text">{{ lockedUnitName }}</span>
          <span class="scloc-lock-badge" title="Unit dikunci sesuai akun Anda">🔒</span>
          <input type="hidden" :value="selectedUnit" />
        </template>

        <!-- Jika bebas → tampilkan dropdown -->
        <template v-else>
          <select
            v-model="selectedUnit"
            @change="handleUnitChange"
            @focus="unitFocused = true"
            @blur="unitFocused = false"
            class="scloc-select"
            required
          >
            <option value="" disabled>— Pilih Unit Kerja —</option>
            <option v-for="unit in units" :key="unit.id" :value="unit.id">
              {{ unit.name }}
            </option>
          </select>
          <span class="scloc-chevron">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </span>
          <span v-if="isLoadingUnits" class="scloc-loader"></span>
        </template>
      </div>
      <p v-if="isUnitLocked" class="scloc-hint">Unit terkunci sesuai akun yang login.</p>
    </div>

    <!-- Lokasi / Ruangan (FREE TEXT) -->
    <div class="scloc-field">
      <label class="scloc-label">
        Ruangan / Lokasi <span class="scloc-req">*</span>
      </label>
      <div class="scloc-input-wrap" :class="{ focused: locationFocused, disabled: !selectedUnit }">
        <span class="scloc-icon">📍</span>
        <input
          type="text"
          v-model="locationName"
          @input="emitChanges"
          @focus="locationFocused = true"
          @blur="locationFocused = false"
          class="scloc-input"
          :placeholder="selectedUnit ? 'Cth: Lab Komputer Lt.2, Ruang Guru, Aula Utama...' : 'Pilih unit kerja dahulu'"
          :disabled="!selectedUnit"
          maxlength="150"
        />
        <span v-if="locationName" class="scloc-char-count">{{ locationName.length }}/150</span>
      </div>
      <p class="scloc-hint">Ketik nama ruangan atau titik lokasi secara bebas.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/services/api';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
  unitId: [Number, String],
  locationName: String,
  specificLoc: String,
});

const emit = defineEmits(['update:unitId', 'update:locationName', 'update:specificLoc']);

const authStore = useAuthStore();
const units = ref([]);
const selectedUnit = ref('');
const locationName = ref(props.locationName || '');
const isLoadingUnits = ref(false);
const unitFocused = ref(false);
const locationFocused = ref(false);

// ── Unit terkunci jika role kepsek atau dept_ygpkb ─────────────────
const isUnitLocked = computed(() => {
  const role = authStore.user?.role;
  return (role === 'kepsek' || role === 'dept_ygpkb') && !!authStore.user?.unit_id;
});

const lockedUnitName = computed(() => {
  if (!isUnitLocked.value) return '';
  const found = units.value.find(u => u.id === authStore.user?.unit_id);
  return found?.name || `Unit #${authStore.user?.unit_id}`;
});

const fetchUnits = async () => {
  isLoadingUnits.value = true;
  try {
    const res = await api.get('/master/units');
    units.value = res.data || [];
  } catch (error) {
    console.error('Failed to fetch units', error);
  } finally {
    isLoadingUnits.value = false;
  }
};

const handleUnitChange = () => {
  emitChanges();
};

const emitChanges = () => {
  emit('update:unitId', selectedUnit.value);
  emit('update:locationName', locationName.value);
  emit('update:specificLoc', locationName.value);
};

onMounted(async () => {
  await fetchUnits();

  // Set unit sesuai login user (untuk kepsek & dept_ygpkb)
  if (isUnitLocked.value) {
    selectedUnit.value = authStore.user.unit_id;
    emitChanges();
  } else if (props.unitId) {
    selectedUnit.value = props.unitId;
  }
});
</script>

<style scoped>
.scloc-wrapper {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

@media (max-width: 600px) {
  .scloc-wrapper { grid-template-columns: 1fr; }
}

.scloc-field {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.scloc-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--text-secondary);
  letter-spacing: 0.01em;
}

.scloc-req {
  color: var(--color-error, #e53e3e);
  margin-left: 2px;
}

/* ── Select / Locked Wrapper ── */
.scloc-select-wrap {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--bg-input, #fff);
  border: 1.5px solid var(--border-input, #e2e8f0);
  border-radius: var(--radius-md, 10px);
  transition: border-color 0.2s, box-shadow 0.2s;
  overflow: hidden;
  min-height: 46px;
}

.scloc-select-wrap.focused {
  border-color: var(--color-primary, #1a3a6b);
  box-shadow: 0 0 0 3px rgba(26, 58, 107, 0.12);
}

.scloc-select-wrap.locked {
  background: var(--bg-page, #f7f8fc);
  border-color: var(--border-color, #e2e8f0);
  cursor: default;
}

.scloc-icon {
  font-size: 1rem;
  padding: 0 0.125rem 0 0.875rem;
  flex-shrink: 0;
  user-select: none;
}

/* Teks saat unit terkunci */
.scloc-locked-text {
  flex: 1;
  padding: 0.75rem 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-primary, #1a202c);
}

.scloc-lock-badge {
  padding-right: 0.875rem;
  font-size: 0.9rem;
  flex-shrink: 0;
}

/* Dropdown */
.scloc-select {
  flex: 1;
  appearance: none;
  -webkit-appearance: none;
  background: transparent;
  border: none;
  outline: none;
  padding: 0.75rem 2.5rem 0.75rem 0.5rem;
  font-size: 0.9rem;
  font-family: inherit;
  color: var(--text-primary, #1a202c);
  cursor: pointer;
  width: 100%;
}

.scloc-select:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.scloc-chevron {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: var(--text-muted, #a0aec0);
}

.scloc-chevron svg {
  width: 16px;
  height: 16px;
  display: block;
}

.scloc-loader {
  position: absolute;
  right: 0.75rem;
  width: 16px;
  height: 16px;
  border: 2px solid var(--border-color, #e2e8f0);
  border-top-color: var(--color-primary, #1a3a6b);
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* ── Input Wrapper ── */
.scloc-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--bg-input, #fff);
  border: 1.5px solid var(--border-input, #e2e8f0);
  border-radius: var(--radius-md, 10px);
  transition: border-color 0.2s, box-shadow 0.2s;
}

.scloc-input-wrap.focused {
  border-color: var(--color-primary, #1a3a6b);
  box-shadow: 0 0 0 3px rgba(26, 58, 107, 0.12);
}

.scloc-input-wrap.disabled {
  background: var(--bg-page, #f7f8fc);
  opacity: 0.6;
}

.scloc-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  padding: 0.75rem 0.5rem;
  font-size: 0.9rem;
  font-family: inherit;
  color: var(--text-primary, #1a202c);
  width: 100%;
}

.scloc-input::placeholder {
  color: var(--text-muted, #a0aec0);
  font-style: italic;
}

.scloc-input:disabled {
  cursor: not-allowed;
}

.scloc-char-count {
  font-size: 0.7rem;
  color: var(--text-muted, #a0aec0);
  padding-right: 0.75rem;
  flex-shrink: 0;
  white-space: nowrap;
}

.scloc-hint {
  font-size: 0.75rem;
  color: var(--text-muted, #a0aec0);
  margin: 0;
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
