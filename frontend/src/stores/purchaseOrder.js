import { defineStore } from 'pinia';
import api from '@/services/api';

export const usePurchaseOrderStore = defineStore('purchaseOrder', {
  state: () => ({
    purchaseOrders: [],
    currentPo: null,
    isLoading: false,
    meta: {
      current_page: 1,
      last_page: 1,
      total: 0
    }
  }),
  actions: {
    async fetchPurchaseOrders(params = {}) {
      this.isLoading = true;
      try {
        const response = await api.get('/purchase-orders', { params });
        this.purchaseOrders = response.data;
        this.meta = response.meta;
      } finally {
        this.isLoading = false;
      }
    },
    async fetchPurchaseOrder(id) {
      this.isLoading = true;
      try {
        const response = await api.get(`/purchase-orders/${id}`);
        this.currentPo = response.data;
        return response.data;
      } finally {
        this.isLoading = false;
      }
    },
    async createDraft(payload) {
      this.isLoading = true;
      try {
        const response = await api.post('/purchase-orders/draft', payload);
        return response;
      } finally {
        this.isLoading = false;
      }
    },
    async issueNumber(id) {
      this.isLoading = true;
      try {
        const response = await api.post(`/purchase-orders/${id}/issue-number`);
        return response;
      } finally {
        this.isLoading = false;
      }
    }
  }
});
