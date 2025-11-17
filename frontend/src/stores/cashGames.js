import { defineStore } from 'pinia'
import axios from 'axios'

export const useCashGamesStore = defineStore('cashGames', {
  state: () => ({
    cashGames: [],
    featuredCashGames: [],
    activeCashGames: [],
    currentCashGame: null,
    isLoading: false,
    filters: {
      gameType: null,
      status: null,
      search: ''
    },
    pagination: {
      currentPage: 1,
      lastPage: 1,
      total: 0,
      perPage: 12
    }
  }),

  getters: {
    filteredCashGames: (state) => {
      let filtered = state.cashGames

      if (state.filters.gameType) {
        filtered = filtered.filter(cg => cg.game_type === state.filters.gameType)
      }

      if (state.filters.status) {
        filtered = filtered.filter(cg => cg.status === state.filters.status)
      }

      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()
        filtered = filtered.filter(cg => 
          cg.name.toLowerCase().includes(search) ||
          cg.venue_name?.toLowerCase().includes(search) ||
          cg.stakes_display?.toLowerCase().includes(search)
        )
      }

      return filtered
    },

    openCashGames: (state) => {
      return state.cashGames.filter(cg => ['open', 'active', 'running'].includes(cg.status))
    }
  },

  actions: {
    /**
     * Fetch all cash games
     */
    async fetchCashGames(params = {}) {
      this.isLoading = true
      try {
        const response = await axios.get('/cash-games', { params })
        this.cashGames = response.data.data || response.data
        
        if (response.data.meta) {
          this.pagination = {
            currentPage: response.data.meta.current_page,
            lastPage: response.data.meta.last_page,
            total: response.data.meta.total,
            perPage: response.data.meta.per_page
          }
        }
      } catch (error) {
        console.error('Failed to fetch cash games:', error)
        this.cashGames = []
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch featured cash games
     */
    async fetchFeaturedCashGames() {
      try {
        const response = await axios.get('/cash-games/featured')
        this.featuredCashGames = response.data.data || response.data
      } catch (error) {
        console.error('Failed to fetch featured cash games:', error)
        this.featuredCashGames = []
      }
    },

    /**
     * Fetch active cash games
     */
    async fetchActiveCashGames() {
      try {
        const response = await axios.get('/cash-games/active')
        this.activeCashGames = response.data.data || response.data
      } catch (error) {
        console.error('Failed to fetch active cash games:', error)
        this.activeCashGames = []
      }
    },

    /**
     * Fetch cash game by ID
     */
    async fetchCashGame(id) {
      this.isLoading = true
      try {
        const response = await axios.get(`/cash-games/${id}`)
        this.currentCashGame = response.data.cash_game || response.data
        return this.currentCashGame
      } catch (error) {
        console.error('Failed to fetch cash game:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Register/Join a cash game (requires authentication)
     */
    async registerForCashGame(cashGameId, buyInAmount = null) {
      try {
        const response = await axios.post('/cash-game/register', {
          cash_game_id: cashGameId,
          buy_in_amount: buyInAmount
        })
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Registration failed'
        }
      }
    },

    /**
     * Leave cash game
     */
    async leaveCashGame(seatId) {
      try {
        const response = await axios.post(`/cash-game-seat/${seatId}/leave`)
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Failed to leave cash game'
        }
      }
    },

    /**
     * Get player's cash game seats
     */
    async getMyCashGameSeats() {
      try {
        const response = await axios.get('/cash-game/my-seats')
        return response.data.data || []
      } catch (error) {
        console.error('Failed to fetch my cash game seats:', error)
        return []
      }
    },

    /**
     * Set filters
     */
    setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
    },

    /**
     * Clear filters
     */
    clearFilters() {
      this.filters = {
        gameType: null,
        status: null,
        search: ''
      }
    },

    /**
     * Clear current cash game
     */
    clearCurrentCashGame() {
      this.currentCashGame = null
    }
  }
})

