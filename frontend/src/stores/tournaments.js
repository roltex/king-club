import { defineStore } from 'pinia'
import axios from 'axios'

export const useTournamentsStore = defineStore('tournaments', {
  state: () => ({
    tournaments: [],
    featuredTournaments: [],
    upcomingTournaments: [],
    currentTournament: null,
    isLoading: false,
    filters: {
      type: null,
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
    filteredTournaments: (state) => {
      let filtered = state.tournaments

      if (state.filters.type) {
        filtered = filtered.filter(t => t.tournament_type === state.filters.type)
      }

      if (state.filters.gameType) {
        filtered = filtered.filter(t => t.game_type === state.filters.gameType)
      }

      if (state.filters.status) {
        filtered = filtered.filter(t => t.status === state.filters.status)
      }

      if (state.filters.search) {
        const search = state.filters.search.toLowerCase()
        filtered = filtered.filter(t => 
          t.name.toLowerCase().includes(search) ||
          t.venue_name?.toLowerCase().includes(search) ||
          t.location?.toLowerCase().includes(search)
        )
      }

      return filtered
    },

    openTournaments: (state) => {
      return state.tournaments.filter(t => t.registration_status === 'open')
    }
  },

  actions: {
    /**
     * Fetch all tournaments
     */
    async fetchTournaments(params = {}) {
      this.isLoading = true
      try {
        const response = await axios.get('/tournaments', { params })
        this.tournaments = response.data.data || response.data
        
        if (response.data.meta) {
          this.pagination = {
            currentPage: response.data.meta.current_page,
            lastPage: response.data.meta.last_page,
            total: response.data.meta.total,
            perPage: response.data.meta.per_page
          }
        }
      } catch (error) {
        console.error('Failed to fetch tournaments:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch featured tournaments
     */
    async fetchFeaturedTournaments() {
      try {
        const response = await axios.get('/tournaments/featured')
        this.featuredTournaments = response.data.data || response.data
      } catch (error) {
        console.error('Failed to fetch featured tournaments:', error)
      }
    },

    /**
     * Fetch upcoming tournaments
     */
    async fetchUpcomingTournaments() {
      try {
        const response = await axios.get('/tournaments/upcoming')
        this.upcomingTournaments = response.data.data || response.data
      } catch (error) {
        console.error('Failed to fetch upcoming tournaments:', error)
      }
    },

    /**
     * Fetch tournament by ID
     */
    async fetchTournament(id) {
      this.isLoading = true
      try {
        const response = await axios.get(`/tournaments/${id}`)
        this.currentTournament = response.data.tournament || response.data
        return this.currentTournament
      } catch (error) {
        console.error('Failed to fetch tournament:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Fetch tournament by slug
     */
    async fetchTournamentBySlug(slug) {
      this.isLoading = true
      try {
        const response = await axios.get(`/tournament/${slug}`)
        this.currentTournament = response.data.tournament || response.data
        return this.currentTournament
      } catch (error) {
        console.error('Failed to fetch tournament:', error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Get tournament statistics
     */
    async getTournamentStatistics(tournamentId) {
      try {
        const response = await axios.get(`/tournaments/${tournamentId}/statistics`)
        return response.data
      } catch (error) {
        console.error('Failed to fetch tournament statistics:', error)
        throw error
      }
    },

    /**
     * Get tournament table layout
     */
    async getTournamentTables(tournamentId) {
      try {
        const response = await axios.get(`/tournaments/${tournamentId}/tables`)
        return response.data.tables || []
      } catch (error) {
        console.error('Failed to fetch tournament tables:', error)
        throw error
      }
    },

    /**
     * Get tournament types
     */
    async getTournamentTypes() {
      try {
        const response = await axios.get('/tournaments/types')
        return response.data
      } catch (error) {
        console.error('Failed to fetch tournament types:', error)
        return {}
      }
    },

    /**
     * Register for tournament (requires authentication)
     */
    async registerForTournament(tournamentId) {
      try {
        const response = await axios.post('/register', {
          tournament_id: tournamentId
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
     * Cancel registration
     */
    async cancelRegistration(registrationId) {
      try {
        const response = await axios.post(`/registration/${registrationId}/cancel`)
        return { success: true, data: response.data }
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Cancellation failed'
        }
      }
    },

    /**
     * Get registration by ID
     */
    async getRegistration(registrationId) {
      try {
        const response = await axios.get(`/registration/${registrationId}`)
        return response.data
      } catch (error) {
        console.error('Failed to fetch registration:', error)
        throw error
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
        type: null,
        gameType: null,
        status: null,
        search: ''
      }
    },

    /**
     * Clear current tournament
     */
    clearCurrentTournament() {
      this.currentTournament = null
    }
  }
})

