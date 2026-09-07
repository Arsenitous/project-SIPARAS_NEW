// composables/useToast.js
// Global Toast Notification System for SIPARAS
import { reactive, readonly } from 'vue';

const state = reactive({
  toasts: [],
  nextId: 1,
});

const DURATION = {
  success: 4000,
  error: 6000,
  warning: 5000,
  info: 4000,
};

function addToast({ type = 'info', title, message, duration }) {
  const id = state.nextId++;
  const toast = {
    id,
    type,
    title,
    message,
    duration: duration ?? DURATION[type] ?? 4000,
    createdAt: Date.now(),
  };
  state.toasts.push(toast);

  // Auto dismiss
  setTimeout(() => removeToast(id), toast.duration + 300);

  return id;
}

function removeToast(id) {
  const index = state.toasts.findIndex((t) => t.id === id);
  if (index !== -1) state.toasts.splice(index, 1);
}

export function useToast() {
  return {
    toasts: readonly(state.toasts),

    success(message, title = 'Berhasil') {
      return addToast({ type: 'success', title, message });
    },

    error(message, title = 'Terjadi Kesalahan') {
      return addToast({ type: 'error', title, message });
    },

    warning(message, title = 'Perhatian') {
      return addToast({ type: 'warning', title, message });
    },

    info(message, title = 'Informasi') {
      return addToast({ type: 'info', title, message });
    },

    dismiss(id) {
      removeToast(id);
    },
  };
}

// Named export for App.vue to pass toasts to ToastNotification
export const toastState = readonly(state);
