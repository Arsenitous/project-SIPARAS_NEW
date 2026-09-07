<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Restore user session on page reload
onMounted(async () => {
  if (authStore.token && !authStore.user) {
    await authStore.fetchUserProfile();
  }
});
</script>
