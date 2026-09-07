<template>
  <div class="py-4">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Rekam Jejak Status (Audit Log)</h3>
    
    <div v-if="isLoading" class="text-gray-500">Memuat log...</div>
    
    <div v-else-if="logs.length === 0" class="text-gray-500 italic">
      Belum ada rekam jejak untuk tiket ini.
    </div>

    <ol v-else class="relative border-l border-gray-200 ml-3">                  
      <li v-for="(log, idx) in logs" :key="log.id" class="mb-6 ml-6">
        <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white">
          <!-- Icon placeholder -->
          <svg class="w-3 h-3 text-blue-800" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
        </span>
        <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">
          {{ formatAction(log.action) }}
          <span v-if="idx === 0" class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded ml-3">Terbaru</span>
        </h3>
        <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
          {{ formatDate(log.created_at) }} &bull; Oleh: {{ log.user?.name || 'Sistem' }} ({{ formatRole(log.role) }})
        </time>
        <p class="mb-4 text-sm font-normal text-gray-500">{{ log.description }}</p>
      </li>
    </ol>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const props = defineProps({
  ticketId: {
    type: [Number, String],
    required: true
  }
});

const logs = ref([]);
const isLoading = ref(true);

const fetchTimeline = async () => {
  try {
    const res = await api.get(`/audit-logs/timeline/${props.ticketId}`);
    // Assuming backend sorts ASC, we want to display DESC (newest top)
    logs.value = res.data.reverse();
  } catch (error) {
    console.error('Failed to load audit logs', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

const formatAction = (action) => {
  const map = {
    'CREATED': 'Tiket Dibuat',
    'ASP_APPROVED': 'Disetujui ASP',
    'ASP_REJECTED': 'Ditolak/Revisi ASP',
    'KD_APPROVED': 'Disetujui KD Sarpras',
    'KD_REJECTED': 'Ditolak/Revisi KD Sarpras',
    'PO_ISSUED': 'Purchase Order Diterbitkan',
    'BAST_ISSUED': 'BAST Diterbitkan',
    'COMPLETED': 'Tiket Selesai',
  };
  return map[action] || action;
};

const formatRole = (role) => {
  if (!role) return 'System';
  return role.replace('_', ' ').toUpperCase();
};

onMounted(() => {
  fetchTimeline();
});
</script>
