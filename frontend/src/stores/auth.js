import { defineStore } from 'pinia'
import axios from 'axios'
import { useRouter } from 'vue-router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('authToken') || null,
    isAuthenticated: false,
    isLoading: false,
  }),

  getters: {
    fullName: (state) => state.user ? `${state.user.first_name} ${state.user.last_name}` : '',
    isLoggedIn: (state) => state.isAuthenticated && state.token !== null,
  },

  actions: {
    /**
     * Initialize authentication from localStorage
     */
    async init() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        try {
          await this.fetchProfile()
        } catch (error) {
          console.error('Failed to initialize auth:', error)
          this.clearAuth()
        }
      }
    },

    /**
     * Register new player account
     */
    async register(data) {
      this.isLoading = true
      try {
        const response = await axios.post('/player/register', data)
        this.setAuth(response.data.player, response.data.token)
      } catch (error) {
        console.error('Registration error:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Login player
     */
    async login(email, password) {
      this.isLoading = true
      try {
        const response = await axios.post('/player/login', { email, password })
        this.setAuth(response.data.player, response.data.token)
      } catch (error) {
        console.error('Login error:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Logout player
     */
    async logout() {
      try {
        if (this.token) {
          await axios.post('/player/logout')
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuth()
      }
    },

    /**
     * Fetch player profile
     */
    async fetchProfile() {
      try {
        const response = await axios.get('/player/profile')
        this.user = response.data.player
        this.isAuthenticated = true
        return response.data.player
      } catch (error) {
        console.error('Failed to fetch profile:', error)
        this.clearAuth()
        throw error
      }
    },

    /**
     * Update player profile
     */
    async updateProfile(data) {
      this.isLoading = true
      try {
        const response = await axios.put('/player/profile', data)
        this.user = response.data.player
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Update failed',
          errors: error.response?.data?.errors || {}
        }
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Change password
     */
    async changePassword(currentPassword, newPassword, newPasswordConfirmation) {
      this.isLoading = true
      try {
        const response = await axios.post('/player/change-password', {
          current_password: currentPassword,
          new_password: newPassword,
          new_password_confirmation: newPasswordConfirmation
        })
        // Update token after password change
        this.setAuth(this.user, response.data.token)
        return { success: true, message: response.data.message }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Password change failed',
          errors: error.response?.data?.errors || {}
        }
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Get tournament history
     */
    async getTournamentHistory(page = 1) {
      try {
        const response = await axios.get(`/player/tournament-history?page=${page}`)
        return response.data
      } catch (error) {
        console.error('Failed to fetch tournament history:', error)
        throw error
      }
    },

    /**
     * Set authentication data
     */
    setAuth(user, token) {
      this.user = user
      this.token = token
      this.isAuthenticated = true
      localStorage.setItem('authToken', token)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },

    /**
     * Clear authentication data
     */
    clearAuth() {
      this.user = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('authToken')
      delete axios.defaults.headers.common['Authorization']
    }
  }
})

