import { defineStore } from 'pinia';
import api from '@/services/api';

export const useMasterStore = defineStore('master', {
  state: () => ({
    units: [],
    vendors: [],
    isLoading: false,
    isSubmitting: false,
  }),
  actions: {
    // ── Units ──────────────────────────────────────────
    async fetchUnits(params = {}) {
      this.isLoading = true;
      try {
        const response = await api.get('/master/units', { params });
        this.units = response.data;
      } finally {
        this.isLoading = false;
      }
    },

    async createUnit(payload) {
      this.isSubmitting = true;
      try {
        const response = await api.post('/master/units', payload);
        this.units.push(response.data);
        return response.data;
      } finally {
        this.isSubmitting = false;
      }
    },

    async updateUnit(id, payload) {
      this.isSubmitting = true;
      try {
        const response = await api.put(`/master/units/${id}`, payload);
        const idx = this.units.findIndex(u => u.id === id);
        if (idx !== -1) this.units[idx] = response.data;
        return response.data;
      } finally {
        this.isSubmitting = false;
      }
    },

    async deleteUnit(id) {
      await api.delete(`/master/units/${id}`);
      this.units = this.units.filter(u => u.id !== id);
    },

    // ── Vendors ────────────────────────────────────────
    async fetchVendors(params = {}) {
      this.isLoading = true;
      try {
        const response = await api.get('/master/vendors', { params });
        this.vendors = response.data;
      } finally {
        this.isLoading = false;
      }
    },

    async createVendor(payload) {
      this.isSubmitting = true;
      try {
        const response = await api.post('/master/vendors', payload);
        this.vendors.push(response.data);
        return response.data;
      } finally {
        this.isSubmitting = false;
      }
    },

    async updateVendor(id, payload) {
      this.isSubmitting = true;
      try {
        const response = await api.put(`/master/vendors/${id}`, payload);
        const idx = this.vendors.findIndex(v => v.id === id);
        if (idx !== -1) this.vendors[idx] = response.data;
        return response.data;
      } finally {
        this.isSubmitting = false;
      }
    },

    async toggleVendorActive(id) {
      const response = await api.post(`/master/vendors/${id}/toggle-active`);
      const idx = this.vendors.findIndex(v => v.id === id);
      if (idx !== -1) this.vendors[idx] = response.data;
      return response.data;
    },

    async deleteVendor(id) {
      await api.delete(`/master/vendors/${id}`);
      this.vendors = this.vendors.filter(v => v.id !== id);
    },
  },
});
