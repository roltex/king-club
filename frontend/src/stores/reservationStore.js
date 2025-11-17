import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { reservationApi } from '../services/api'

export const useReservationStore = defineStore('reservation', () => {
  // State
  const currentReservation = ref(null)
  const statistics = ref(null)
  const tableLayout = ref([])
  const waitingList = ref([])
  const loading = ref(false)
  const error = ref(null)

  // Getters
  const hasReservation = computed(() => currentReservation.value !== null)
  const isReserved = computed(() => currentReservation.value?.status === 'reserved')
  const isWaiting = computed(() => currentReservation.value?.status === 'waiting')
  const isCheckedIn = computed(() => currentReservation.value?.status === 'checked_in')
  const availableSeats = computed(() => statistics.value?.available_seats || 0)
  const isFull = computed(() => availableSeats.value === 0)

  // Actions
  async function createReservation(formData) {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.createReservation(formData)
      currentReservation.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to create reservation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getReservation(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.getReservation(id)
      currentReservation.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Reservation not found'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function getReservationByPhone(phone) {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.getReservationByPhone(phone)
      currentReservation.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Reservation not found'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function checkIn(reservationId) {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.checkIn(reservationId)
      if (response.data.success) {
        // Update current reservation if it matches
        if (currentReservation.value?.id === reservationId) {
          currentReservation.value.status = 'checked_in'
          currentReservation.value.checkin_time = response.data.checkin_time
        }
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Check-in failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function cancelReservation(id) {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.cancelReservation(id)
      if (currentReservation.value?.id === id) {
        currentReservation.value = null
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to cancel reservation'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchStatistics() {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.getStatistics()
      statistics.value = response.data
      return response.data
    } catch (err) {
      error.value = 'Failed to fetch statistics'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchTableLayout() {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.getTableLayout()
      tableLayout.value = response.data.tables
      return response.data.tables
    } catch (err) {
      error.value = 'Failed to fetch table layout'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function fetchWaitingList() {
    loading.value = true
    error.value = null
    
    try {
      const response = await reservationApi.getWaitingList()
      waitingList.value = response.data.waiting_list
      return response.data.waiting_list
    } catch (err) {
      error.value = 'Failed to fetch waiting list'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearCurrentReservation() {
    currentReservation.value = null
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    currentReservation,
    statistics,
    tableLayout,
    waitingList,
    loading,
    error,
    
    // Getters
    hasReservation,
    isReserved,
    isWaiting,
    isCheckedIn,
    availableSeats,
    isFull,
    
    // Actions
    createReservation,
    getReservation,
    getReservationByPhone,
    checkIn,
    cancelReservation,
    fetchStatistics,
    fetchTableLayout,
    fetchWaitingList,
    clearCurrentReservation,
    clearError
  }
})

