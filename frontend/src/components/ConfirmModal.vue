<template>
  <teleport to="body">
    <transition name="modal">
      <div v-if="confirmState.isOpen" class="modal-overlay" @click.self="onCancel">
        <div class="modal-card" role="dialog" aria-modal="true" :aria-labelledby="`modal-title`">
          <!-- Icon -->
          <div class="modal-icon-wrap" :class="`modal-icon-${confirmState.type}`">
            <svg v-if="confirmState.type === 'danger'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <svg v-else-if="confirmState.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 14 14"/></svg>
          </div>

          <!-- Content -->
          <h2 id="modal-title" class="modal-title">{{ confirmState.title }}</h2>
          <p class="modal-message">{{ confirmState.message }}</p>

          <!-- Actions -->
          <div class="modal-actions">
            <button class="btn btn-outline btn-cancel" @click="onCancel">
              {{ confirmState.cancelLabel }}
            </button>
            <button
              class="btn"
              :class="confirmState.type === 'danger' ? 'btn-danger' : 'btn-primary'"
              @click="onConfirm"
            >
              {{ confirmState.confirmLabel }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script setup>
import { useConfirm } from '@/composables/useConfirm';
const { confirmState, onConfirm, onCancel } = useConfirm();
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  padding: 1rem;
}

.modal-card {
  background: var(--bg-card, #fff);
  border-radius: 20px;
  padding: 2rem;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2), 0 8px 20px rgba(0,0,0,0.12);
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
}

.modal-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}
.modal-icon-wrap svg { width: 28px; height: 28px; }
.modal-icon-danger  { background: #fef2f2; color: #dc2626; }
.modal-icon-warning { background: #fffbeb; color: #d97706; }
.modal-icon-info    { background: #eff6ff; color: #2563eb; }

.modal-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--text-primary, #0f172a);
  margin-bottom: 0.5rem;
}

.modal-message {
  font-size: 0.9rem;
  color: var(--text-secondary, #475569);
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 0.75rem;
  width: 100%;
}
.modal-actions .btn { flex: 1; justify-content: center; }

/* Transition */
.modal-enter-active { animation: modal-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-leave-active { animation: modal-out 0.2s ease forwards; }

@keyframes modal-in {
  from { opacity: 0; transform: scale(0.85) translateY(20px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes modal-out {
  from { opacity: 1; transform: scale(1); }
  to   { opacity: 0; transform: scale(0.9); }
}
</style>
