import axios from 'axios';
import { useAuthStore } from '@/stores/auth';
// import { useToast } from '@/composables/useToast'; // will implement later
import router from '@/router';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    'ngrok-skip-browser-warning': '69420'
  }
});

// Request Interceptor: Menyuntikkan Bearer Token Sanctum
api.interceptors.request.use((config) => {
  const authStore = useAuthStore();
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`;
  }
  return config;
}, (error) => Promise.reject(error));

// Response Interceptor: Penanganan Kode Status Global
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    const { status, data } = error.response || {};
    // const toast = useToast();
    const authStore = useAuthStore();

    if (status === 401) {
      // Sesi berakhir / Token kedaluwarsa
      authStore.logout();
      router.push({ name: 'login', query: { session: 'expired' } });
      // toast.error('Sesi Anda telah berakhir. Silakan login kembali.');
    } else if (status === 403) {
      // toast.error(data?.message || 'Akses Ditolak: Anda tidak memiliki wewenang.');
    } else if (status === 422) {
      // Validasi gagal diteruskan ke komponen form
      return Promise.reject(data);
    } else if (status >= 500) {
      // toast.error('Terjadi gangguan server. Silakan hubungi tim IT Sarpras.');
    }

    return Promise.reject(error);
  }
);

export default api;
