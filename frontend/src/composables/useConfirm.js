// composables/useConfirm.js
// Programmatic Confirmation Modal System for SIPARAS
import { reactive, readonly } from 'vue';

const state = reactive({
  isOpen: false,
  title: '',
  message: '',
  confirmLabel: 'Ya, Lanjutkan',
  cancelLabel: 'Batal',
  type: 'warning', // 'warning' | 'danger' | 'info'
  resolve: null,
});

export function useConfirm() {
  /**
   * Show a confirmation modal and return a Promise<boolean>
   * @param {Object} options
   * @param {string} options.title
   * @param {string} options.message
   * @param {string} [options.confirmLabel]
   * @param {string} [options.cancelLabel]
   * @param {'warning'|'danger'|'info'} [options.type]
   */
  function confirm({ title, message, confirmLabel, cancelLabel, type = 'warning' }) {
    state.isOpen = true;
    state.title = title || 'Konfirmasi Aksi';
    state.message = message || 'Apakah Anda yakin ingin melanjutkan?';
    state.confirmLabel = confirmLabel || 'Ya, Lanjutkan';
    state.cancelLabel = cancelLabel || 'Batal';
    state.type = type;

    return new Promise((resolve) => {
      state.resolve = resolve;
    });
  }

  function onConfirm() {
    state.isOpen = false;
    if (state.resolve) state.resolve(true);
  }

  function onCancel() {
    state.isOpen = false;
    if (state.resolve) state.resolve(false);
  }

  return {
    confirmState: readonly(state),
    confirm,
    onConfirm,
    onCancel,
  };
}
