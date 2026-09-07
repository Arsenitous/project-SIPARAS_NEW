<template>
  <teleport to="body">
    <div class="toast-container" aria-live="polite" aria-atomic="false">
      <transition-group name="toast" tag="div">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast-item"
          :class="`toast-${toast.type}`"
          role="alert"
        >
          <!-- Icon -->
          <div class="toast-icon">
            <svg v-if="toast.type === 'success'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else-if="toast.type === 'error'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            <svg v-else-if="toast.type === 'warning'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>

          <!-- Content -->
          <div class="toast-content">
            <p class="toast-title">{{ toast.title }}</p>
            <p v-if="toast.message" class="toast-message">{{ toast.message }}</p>
          </div>

          <!-- Close -->
          <button class="toast-close" @click="dismiss(toast.id)" aria-label="Tutup">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>

          <!-- Progress bar -->
          <div class="toast-progress" :style="{ animationDuration: `${toast.duration}ms` }"></div>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup>
import { useToast } from '@/composables/useToast';

const { toasts, dismiss } = useToast();
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 1.25rem;
  right: 1.25rem;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  max-width: 360px;
  pointer-events: none;
}

.toast-item {
  pointer-events: all;
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 12px;
  border: 1px solid transparent;
  box-shadow: 0 8px 24px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.08);
  overflow: hidden;
  backdrop-filter: blur(8px);
  cursor: default;
}

/* Type colors */
.toast-success { background: #f0fdf4; border-color: #86efac; color: #15803d; }
.toast-error   { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
.toast-warning { background: #fffbeb; border-color: #fcd34d; color: #b45309; }
.toast-info    { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }

.toast-icon {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  margin-top: 2px;
}
.toast-icon svg { width: 100%; height: 100%; }

.toast-content { flex: 1; min-width: 0; }
.toast-title   { font-size: 0.875rem; font-weight: 700; line-height: 1.3; }
.toast-message { font-size: 0.8125rem; margin-top: 0.2rem; opacity: 0.85; line-height: 1.4; }

.toast-close {
  flex-shrink: 0;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  cursor: pointer;
  opacity: 0.5;
  padding: 0;
  color: inherit;
  transition: opacity 0.15s;
}
.toast-close:hover { opacity: 1; }
.toast-close svg { width: 14px; height: 14px; }

/* Progress bar */
.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  height: 3px;
  width: 100%;
  background: currentColor;
  opacity: 0.25;
  transform-origin: left;
  animation: toast-shrink linear forwards;
}
@keyframes toast-shrink {
  from { transform: scaleX(1); }
  to   { transform: scaleX(0); }
}

/* Transition animations */
.toast-enter-active { animation: toast-in 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-leave-active { animation: toast-out 0.25s ease forwards; }
.toast-move         { transition: transform 0.25s ease; }

@keyframes toast-in {
  from { opacity: 0; transform: translateX(120%) scale(0.9); }
  to   { opacity: 1; transform: translateX(0) scale(1); }
}
@keyframes toast-out {
  from { opacity: 1; transform: translateX(0) scale(1); }
  to   { opacity: 0; transform: translateX(120%) scale(0.9); }
}
</style>
