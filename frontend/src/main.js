import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import './style.css'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Inisialisasi auth state sebelum mount aplikasi
import { useAuthStore } from '@/stores/auth'
const authStore = useAuthStore()

const initApp = async () => {
  if (authStore.token) {
    try {
      await authStore.fetchUserProfile()
    } catch (e) {
      console.error('Failed to restore session')
    }
  }
  app.mount('#app')
}

initApp()
