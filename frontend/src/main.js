import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './style.css'
import axios from 'axios'

// Configure axios - detect environment and use appropriate API URL
const getApiBaseURL = () => {
  // Check if we're in production (Railway)
  if (window.location.hostname.includes('railway.app') || window.location.hostname.includes('king-club-frontend')) {
    return 'https://king-club-backend.up.railway.app'
  }
  // Use environment variable if set (for build-time)
  if (import.meta.env.VITE_API_BASE_URL) {
    return import.meta.env.VITE_API_BASE_URL
  }
  // Fallback to localhost for development
  return 'http://127.0.0.1:8000'
}

axios.defaults.baseURL = getApiBaseURL()
console.log('API Base URL:', axios.defaults.baseURL)
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.headers.common['Accept'] = 'application/json'

// Axios interceptors for error handling
axios.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      // Unauthorized - clear auth and redirect to login
      const { useAuthStore } = await import('./stores/auth')
      const authStore = useAuthStore()
      authStore.clearAuth()
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// Initialize auth store
import { useAuthStore } from './stores/auth'
const authStore = useAuthStore()
authStore.init()

app.mount('#app')

