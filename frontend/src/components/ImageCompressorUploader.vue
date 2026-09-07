<template>
  <div class="uploader">
    <!-- Drop Zone / Upload Trigger -->
    <div
      class="uploader-dropzone"
      :class="{
        'is-dragging': isDragging,
        'is-full': files.length >= maxFiles
      }"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
      @click="files.length < maxFiles && triggerFileInput()"
    >
      <input
        type="file"
        accept="image/jpeg,image/png,image/jpg,image/webp"
        multiple
        class="uploader-hidden-input"
        ref="fileInput"
        @change="handleFileUpload"
      />

      <div v-if="isUploading" class="uploader-state">
        <div class="uploader-spinner"></div>
        <p class="uploader-state-text">Mengompresi gambar...</p>
      </div>

      <div v-else-if="files.length >= maxFiles" class="uploader-state">
        <span class="uploader-icon">✅</span>
        <p class="uploader-state-text">Maksimal {{ maxFiles }} foto sudah terpilih</p>
      </div>

      <div v-else class="uploader-state">
        <span class="uploader-icon">📷</span>
        <p class="uploader-state-title">
          {{ isDragging ? 'Lepaskan untuk upload' : 'Klik atau seret foto ke sini' }}
        </p>
        <p class="uploader-state-sub">
          JPG, PNG, WebP · Maks {{ maxFiles }} foto · Auto-kompresi ≤500KB
        </p>
        <button type="button" class="btn btn-outline btn-sm uploader-btn" @click.stop="triggerFileInput">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Pilih Foto
          <span style="opacity:0.6;font-size:0.75rem;">({{ files.length }}/{{ maxFiles }})</span>
        </button>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="errorMessage" class="uploader-error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      {{ errorMessage }}
    </div>

    <!-- Preview Grid -->
    <div v-if="files.length > 0" class="uploader-previews">
      <div
        v-for="(file, index) in files"
        :key="index"
        class="preview-card"
      >
        <div class="preview-img-wrap">
          <img :src="file.preview" :alt="file.name" class="preview-img" />
          <button
            type="button"
            class="preview-remove"
            @click="removeFile(index)"
            title="Hapus foto"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:12px;height:12px;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <div class="preview-badge">
            {{ formatSize(file.file.size) }}
          </div>
        </div>
        <p class="preview-name">{{ file.name }}</p>
      </div>

      <!-- Add more slot (if not full) -->
      <div
        v-if="files.length < maxFiles"
        class="preview-add"
        @click="triggerFileInput"
        title="Tambah foto"
      >
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;color:var(--text-muted);"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import imageCompression from 'browser-image-compression';

const props = defineProps({
  label: { type: String, default: 'Lampiran Foto' },
  maxFiles: { type: Number, default: 5 },
  modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const files = ref([]);
const isUploading = ref(false);
const isDragging = ref(false);
const errorMessage = ref('');

const triggerFileInput = () => {
  fileInput.value?.click();
};

const formatSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
  return (bytes / 1024 / 1024).toFixed(1) + ' MB';
};

const processFiles = async (selectedFiles) => {
  errorMessage.value = '';

  const remaining = props.maxFiles - files.value.length;
  if (selectedFiles.length > remaining) {
    errorMessage.value = `Hanya bisa menambah ${remaining} foto lagi (maks. ${props.maxFiles}).`;
    selectedFiles = selectedFiles.slice(0, remaining);
  }

  if (selectedFiles.length === 0) return;

  isUploading.value = true;
  const options = { maxSizeMB: 0.5, maxWidthOrHeight: 1920, useWebWorker: true };

  try {
    for (const file of selectedFiles) {
      if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Hanya format gambar (JPG/PNG/WebP) yang diperbolehkan.';
        continue;
      }
      const compressedFile = await imageCompression(file, options);
      const previewUrl = URL.createObjectURL(compressedFile);
      files.value.push({ file: compressedFile, preview: previewUrl, name: file.name });
    }
    emit('update:modelValue', files.value.map(f => f.file));
  } catch {
    errorMessage.value = 'Gagal memproses gambar. Coba file lain.';
  } finally {
    isUploading.value = false;
    if (fileInput.value) fileInput.value.value = '';
  }
};

const handleFileUpload = (event) => {
  processFiles(Array.from(event.target.files));
};

const handleDrop = (event) => {
  isDragging.value = false;
  const droppedFiles = Array.from(event.dataTransfer.files);
  processFiles(droppedFiles);
};

const removeFile = (index) => {
  URL.revokeObjectURL(files.value[index].preview);
  files.value.splice(index, 1);
  emit('update:modelValue', files.value.map(f => f.file));
};
</script>

<style scoped>
.uploader { display: flex; flex-direction: column; gap: 0.875rem; }

/* ── Drop Zone ── */
.uploader-dropzone {
  border: 2px dashed var(--border-color, #e2e8f0);
  border-radius: var(--radius-lg, 14px);
  padding: 2rem 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  background: var(--bg-page, #f7f8fc);
  position: relative;
}

.uploader-dropzone:hover:not(.is-full) {
  border-color: var(--color-primary, #1a3a6b);
  background: rgba(26, 58, 107, 0.03);
}

.uploader-dropzone.is-dragging {
  border-color: var(--color-primary, #1a3a6b);
  background: rgba(26, 58, 107, 0.06);
  border-style: solid;
}

.uploader-dropzone.is-full {
  cursor: default;
  opacity: 0.7;
}

.uploader-hidden-input {
  display: none;
}

/* ── State (icon + text) ── */
.uploader-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.uploader-icon {
  font-size: 2.25rem;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.uploader-state-title {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--text-primary, #1a202c);
  margin: 0;
}

.uploader-state-sub {
  font-size: 0.775rem;
  color: var(--text-muted, #a0aec0);
  margin: 0;
}

.uploader-state-text {
  font-size: 0.875rem;
  color: var(--text-secondary, #4a5568);
  margin: 0;
}

.uploader-btn {
  margin-top: 0.5rem;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
}

.uploader-spinner {
  width: 32px; height: 32px;
  border: 3px solid var(--border-color, #e2e8f0);
  border-top-color: var(--color-primary, #1a3a6b);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

/* ── Error ── */
.uploader-error {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8125rem;
  color: var(--color-error, #e53e3e);
  background: var(--color-error-bg, #fff5f5);
  padding: 0.5rem 0.875rem;
  border-radius: var(--radius-md, 10px);
  border: 1px solid rgba(229, 62, 62, 0.2);
}

/* ── Preview Grid ── */
.uploader-previews {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 0.75rem;
}

.preview-card {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.preview-img-wrap {
  position: relative;
  border-radius: var(--radius-md, 10px);
  overflow: hidden;
  aspect-ratio: 1;
  background: var(--bg-page, #f7f8fc);
  border: 1.5px solid var(--border-color, #e2e8f0);
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.preview-remove {
  position: absolute;
  top: 4px; right: 4px;
  width: 22px; height: 22px;
  border-radius: 50%;
  background: rgba(0,0,0,0.65);
  color: #fff;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.15s;
}

.preview-img-wrap:hover .preview-remove { opacity: 1; }

.preview-badge {
  position: absolute;
  bottom: 4px; left: 4px;
  font-size: 0.65rem;
  font-weight: 700;
  background: rgba(0,0,0,0.55);
  color: #fff;
  padding: 1px 5px;
  border-radius: 4px;
}

.preview-name {
  font-size: 0.7rem;
  color: var(--text-muted, #a0aec0);
  text-overflow: ellipsis;
  overflow: hidden;
  white-space: nowrap;
  margin: 0;
  text-align: center;
}

/* ── Add More Slot ── */
.preview-add {
  aspect-ratio: 1;
  border: 2px dashed var(--border-color, #e2e8f0);
  border-radius: var(--radius-md, 10px);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
}

.preview-add:hover {
  border-color: var(--color-primary, #1a3a6b);
  background: rgba(26, 58, 107, 0.04);
}

@keyframes spin { to { transform: rotate(360deg); } }
</style>
