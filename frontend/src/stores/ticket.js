import { defineStore } from 'pinia';
import api from '@/services/api';

export const useTicketStore = defineStore('ticket', {
  state: () => ({
    tickets: [],
    currentTicket: null,
    isLoading: false,
    meta: {
      current_page: 1,
      last_page: 1,
      total: 0
    }
  }),
  actions: {
    async fetchTickets(params = {}) {
      this.isLoading = true;
      try {
        const response = await api.get('/tickets', { params });
        this.tickets = response.data || [];
        this.meta = response.meta || { current_page: 1, last_page: 1, total: 0 };
        return response; // return so callers can use it
      } finally {
        this.isLoading = false;
      }
    },
    async fetchAuditTimeline(ticketId) {
      try {
        const response = await api.get(`/audit-logs/timeline/${ticketId}`);
        return response.data || [];
      } catch {
        return [];
      }
    },
    async fetchTicket(id) {
      this.isLoading = true;
      try {
        const response = await api.get(`/tickets/${id}`);
        this.currentTicket = response.data;
        return response.data;
      } finally {
        this.isLoading = false;
      }
    },
    async createTicket(payload) {
      this.isLoading = true;
      try {
        const response = await api.post('/tickets', payload);
        return response;
      } finally {
        this.isLoading = false;
      }
    },
    async reviewAsp(id, payload) {
      this.isLoading = true;
      try {
        const response = await api.post(`/tickets/${id}/review-asp`, payload);
        return response;
      } finally {
        this.isLoading = false;
      }
    },
    async approvalKd(id, payload) {
      this.isLoading = true;
      try {
        const response = await api.post(`/tickets/${id}/approval-kd`, payload);
        return response;
      } finally {
        this.isLoading = false;
      }
    },
    async deleteTicket(id) {
      this.isLoading = true;
      try {
        const response = await api.delete(`/tickets/${id}`);
        if (this.currentTicket && this.currentTicket.id === Number(id)) {
          this.currentTicket = null;
        }
        this.tickets = this.tickets.filter((t) => t.id !== Number(id));
        return response;
      } finally {
        this.isLoading = false;
      }
    }
  }
});
