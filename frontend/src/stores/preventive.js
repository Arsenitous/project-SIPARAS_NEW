import { defineStore } from 'pinia';
import api from '@/services/api';

export const usePreventiveStore = defineStore('preventive', {
  state: () => ({
    schedules: [],
    isLoading: false,
  }),
  actions: {
    async fetchSchedules(params = {}) {
      this.isLoading = true;
      try {
        const response = await api.get('/preventive-schedules', { params });
        this.schedules = response.data;
      } finally {
        this.isLoading = false;
      }
    },
    async createSchedule(payload) {
      this.isLoading = true;
      try {
        const response = await api.post('/preventive-schedules', payload);
        return response;
      } finally {
        this.isLoading = false;
      }
    },
    async triggerService(id) {
      this.isLoading = true;
      try {
        const response = await api.post(`/preventive-schedules/${id}/trigger-service`);
        return response;
      } finally {
        this.isLoading = false;
      }
    }
  }
});
