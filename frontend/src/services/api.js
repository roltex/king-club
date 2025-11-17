import axios from 'axios'

// Base URL from config
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor
api.interceptors.request.use(
  (config) => {
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response) {
      // Server responded with error
      console.error('API Error:', error.response.data)
    } else if (error.request) {
      // Request made but no response
      console.error('Network Error:', error.request)
    } else {
      // Something else happened
      console.error('Error:', error.message)
    }
    return Promise.reject(error)
  }
)

export const reservationApi = {
  // Create new reservation
  createReservation(data) {
    return api.post('/reserve', data)
  },

  // Get reservation by ID
  getReservation(id) {
    return api.get(`/reservation/${id}`)
  },

  // Get reservation by phone
  getReservationByPhone(phone) {
    return api.get(`/reservation/phone/${phone}`)
  },

  // Check in
  checkIn(reservationId) {
    return api.post('/checkin', { reservation_id: reservationId })
  },

  // Cancel reservation
  cancelReservation(id) {
    return api.post(`/reservation/${id}/cancel`)
  },

  // Get statistics
  getStatistics() {
    return api.get('/statistics')
  },

  // Get table layout
  getTableLayout() {
    return api.get('/tables')
  },

  // Get waiting list
  getWaitingList() {
    return api.get('/waiting-list')
  }
}

export default api

