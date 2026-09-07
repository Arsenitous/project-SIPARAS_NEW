import { defineStore } from 'pinia';
import api from '@/services/api';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('access_token') || null,
    user: null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isKepsek: (state) => state.user?.role === 'kepsek',
    isDeptYGPKB: (state) => state.user?.role === 'dept_ygpkb',
    isASP: (state) => state.user?.role === 'asp_wilayah',
    isKD: (state) => state.user?.role === 'kd_sarpras',
    isAdmin: (state) => state.user?.role === 'admin_sarpras',
    isKoordinatorSKI: (state) => state.user?.role === 'koordinator_ski',
    isKoordinatorUtama: (state) => state.user?.role === 'koordinator_utama',
  },
  actions: {
    async login(credentials) {
      const response = await api.post('/auth/login', credentials);
      this.token = response.data.access_token;
      this.user = response.data.user;
      localStorage.setItem('access_token', this.token);
      return response;
    },
    async fetchUserProfile() {
      if (!this.token) return;
      try {
        const response = await api.get('/auth/me');
        this.user = response.data;
      } catch (error) {
        this.logout();
      }
    },
    async logout() {
      try {
        if (this.token) await api.post('/auth/logout');
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('access_token');
      }
    }
  }
});
