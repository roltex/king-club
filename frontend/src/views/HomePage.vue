<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950">
      <div class="absolute inset-0 bg-[url('/grid.svg')] opacity-5"></div>
      
      <div class="page-container py-24 relative">
        <div class="max-w-4xl mx-auto text-center animate-fade-in-up">
          <div class="inline-block mb-6">
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-900/50 border border-emerald-700 rounded-full text-emerald-400 text-sm font-semibold">
              <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
              Live Tournaments Available
            </span>
          </div>
          
          <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight">
            Find Your Next
            <span class="gradient-text block">Poker Tournament</span>
          </h1>
          
          <p class="text-xl text-slate-300 mb-10 max-w-2xl mx-auto">
            Join competitive Kings Club, compete with skilled players, and win amazing cash prizes
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <router-link to="/tournaments" class="btn-primary text-lg px-8 py-4 inline-flex items-center gap-2">
              <Trophy :size="24" />
              <span>Browse Tournaments</span>
              <ArrowRight :size="20" />
            </router-link>
            
            <router-link v-if="!authStore.isLoggedIn" to="/register" class="btn-accent text-lg px-8 py-4 inline-flex items-center gap-2">
              <UserPlus :size="24" />
              <span>Create Account</span>
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section with Tabs -->
    <section class="page-container py-12">
      <div class="card p-0 overflow-hidden">
        <!-- Tabs Header -->
        <div class="flex border-b border-slate-800">
          <button
            @click="activeStatsTab = 'tournaments'"
            :class="activeStatsTab === 'tournaments' 
              ? 'flex-1 px-6 py-4 text-base font-bold text-emerald-400 bg-emerald-900/20 border-b-2 border-emerald-500 transition-all' 
              : 'flex-1 px-6 py-4 text-base font-semibold text-slate-400 hover:text-slate-300 hover:bg-slate-900/30 transition-all'"
          >
            <div class="flex items-center justify-center gap-2">
              <Trophy :size="20" />
              <span>Tournament Statistics</span>
            </div>
          </button>
          <button
            @click="activeStatsTab = 'cashgames'"
            :class="activeStatsTab === 'cashgames' 
              ? 'flex-1 px-6 py-4 text-base font-bold text-emerald-400 bg-emerald-900/20 border-b-2 border-emerald-500 transition-all' 
              : 'flex-1 px-6 py-4 text-base font-semibold text-slate-400 hover:text-slate-300 hover:bg-slate-900/30 transition-all'"
          >
            <div class="flex items-center justify-center gap-2">
              <DollarSign :size="20" />
              <span>Cash Game Statistics</span>
            </div>
          </button>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Tournament Stats Tab -->
          <div v-if="activeStatsTab === 'tournaments'" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-emerald-400 mb-2">{{ stats.total_tournaments || 0 }}+</div>
              <div class="text-slate-400 font-medium">Tournaments</div>
            </div>
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-amber-400 mb-2">{{ stats.open_tournaments || 0 }}</div>
              <div class="text-slate-400 font-medium">Open Now</div>
            </div>
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-blue-400 mb-2">{{ stats.active_registrations || 0 }}+</div>
              <div class="text-slate-400 font-medium">Registered</div>
            </div>
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-yellow-400 mb-2">₾{{ formatNumber(stats.total_prize_pool || 0) }}</div>
              <div class="text-slate-400 font-medium">Prize Pool</div>
            </div>
          </div>

          <!-- Cash Game Stats Tab -->
          <div v-if="activeStatsTab === 'cashgames'" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-emerald-400 mb-2">{{ stats.total_cash_games || 0 }}+</div>
              <div class="text-slate-400 font-medium">Cash Games</div>
            </div>
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-amber-400 mb-2">{{ stats.active_cash_games || 0 }}</div>
              <div class="text-slate-400 font-medium">Active Now</div>
            </div>
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-blue-400 mb-2">{{ stats.total_cash_game_players || 0 }}+</div>
              <div class="text-slate-400 font-medium">Players</div>
            </div>
            <div class="stat-card text-center">
              <div class="text-4xl md:text-5xl font-black text-yellow-400 mb-2">₾{{ formatNumber(stats.total_cash_game_pot || 0) }}</div>
              <div class="text-slate-400 font-medium">Total Pot</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Active Cash Games -->
    <section class="page-container py-12">
      <div class="mb-8">
        <h2 class="section-title mb-2">Active Cash Games</h2>
        <p class="text-slate-400">Join live cash game tables and play now</p>
      </div>

      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <SkeletonCard v-for="i in 6" :key="i" />
      </div>

      <div v-else-if="activeCashGames.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <CashGameCard
          v-for="cashGame in activeCashGames"
          :key="cashGame.id"
          :cash-game="cashGame"
        />
      </div>

      <div v-else class="text-center py-16">
        <DollarSign :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">No Active Cash Games</h3>
        <p class="text-slate-400 mb-6">Check back soon for new cash game tables!</p>
      </div>

      <div v-if="activeCashGames.length > 0" class="text-center mt-12">
        <router-link to="/cash-games" class="btn-primary px-8 py-3 inline-flex items-center gap-2">
          <DollarSign :size="20" />
          <span>View All Cash Games</span>
          <ArrowRight :size="20" />
        </router-link>
      </div>
    </section>

    <!-- Upcoming Tournaments -->
    <section class="page-container py-12">
      <div class="mb-8">
        <h2 class="section-title mb-2">Upcoming Tournaments</h2>
        <p class="text-slate-400">Register now and secure your seat at the table</p>
      </div>

      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <SkeletonCard v-for="i in 6" :key="i" />
      </div>

      <div v-else-if="upcomingTournaments.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <TournamentCard
          v-for="tournament in upcomingTournaments"
          :key="tournament.id"
          :tournament="tournament"
        />
      </div>

      <div v-else class="text-center py-16">
        <Calendar :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">No Upcoming Tournaments</h3>
        <p class="text-slate-400 mb-6">Check back soon for new exciting events!</p>
      </div>

      <div v-if="upcomingTournaments.length > 0" class="text-center mt-12">
        <router-link to="/tournaments" class="btn-primary px-8 py-3 inline-flex items-center gap-2">
          <span>View All Tournaments</span>
          <ArrowRight :size="20" />
        </router-link>
      </div>
    </section>

    <!-- How It Works -->
    <section class="page-container py-16">
      <div class="text-center mb-12">
        <h2 class="section-title mb-2">How It Works</h2>
        <p class="text-slate-400 text-lg">Simple steps to start playing</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="card p-8 text-center group hover:border-emerald-700">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50">
            <UserPlus :size="32" class="text-white" />
          </div>
          <div class="text-2xl font-bold text-white mb-2">1. Register</div>
          <p class="text-slate-400">Create your free player account in seconds</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50">
            <Search :size="32" class="text-white" />
          </div>
          <div class="text-2xl font-bold text-white mb-2">2. Browse</div>
          <p class="text-slate-400">Find tournaments that match your style</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50">
            <CheckCircle2 :size="32" class="text-white" />
          </div>
          <div class="text-2xl font-bold text-white mb-2">3. Join</div>
          <p class="text-slate-400">Register for your tournament and get seated</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-900/50">
            <Trophy :size="32" class="text-slate-900" />
          </div>
          <div class="text-2xl font-bold text-white mb-2">4. Win</div>
          <p class="text-slate-400">Play your best and take home the prize</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="page-container py-16">
      <div class="card bg-gradient-to-br from-emerald-900/50 to-emerald-800/30 border-emerald-700 p-12 text-center">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-4">
          Ready to Join the Action?
        </h2>
        <p class="text-xl text-emerald-100 mb-8 max-w-2xl mx-auto">
          Create your account now and start competing in exciting Kings Club today
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link v-if="!authStore.isLoggedIn" to="/register" class="btn-accent text-lg px-8 py-4 inline-flex items-center gap-2">
            <UserPlus :size="24" />
            <span>Create Free Account</span>
          </router-link>
          <router-link to="/tournaments" class="btn-secondary text-lg px-8 py-4 inline-flex items-center gap-2">
            <Trophy :size="24" />
            <span>Browse Tournaments</span>
          </router-link>
          <router-link to="/cash-games" class="btn-secondary text-lg px-8 py-4 inline-flex items-center gap-2">
            <DollarSign :size="24" />
            <span>Browse Cash Games</span>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useTournamentsStore } from '../stores/tournaments'
import { useCashGamesStore } from '../stores/cashGames'
import TournamentCard from '../components/TournamentCard.vue'
import CashGameCard from '../components/CashGameCard.vue'
import SkeletonCard from '../components/SkeletonCard.vue'
import {
  Trophy, DollarSign, Calendar, ArrowRight, UserPlus, Search, CheckCircle2
} from 'lucide-vue-next'

const authStore = useAuthStore()
const tournamentsStore = useTournamentsStore()
const cashGamesStore = useCashGamesStore()

const isLoading = ref(true)
const activeStatsTab = ref('tournaments')

const upcomingTournaments = computed(() => tournamentsStore.upcomingTournaments.slice(0, 6))
const activeCashGames = computed(() => cashGamesStore.activeCashGames.slice(0, 6))

const stats = ref({
  // Tournament Stats
  total_tournaments: 0,
  open_tournaments: 0,
  active_registrations: 0,
  total_prize_pool: 0,
  // Cash Game Stats
  total_cash_games: 0,
  active_cash_games: 0,
  total_cash_game_players: 0,
  total_cash_game_pot: 0,
  // Combined
  total_players: 0,
})

const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const fetchStatistics = async () => {
  try {
    const { default: axios } = await import('axios')
    const response = await axios.get('/statistics')
    
    stats.value = {
      // Tournament Stats
      total_tournaments: response.data.total_tournaments || 0,
      open_tournaments: response.data.open_now || 0,
      active_registrations: response.data.active_registrations || 0,
      total_prize_pool: response.data.total_prize_pool || 0,
      // Cash Game Stats
      total_cash_games: response.data.total_cash_games || 0,
      active_cash_games: response.data.active_cash_games || 0,
      total_cash_game_players: response.data.total_cash_game_players || 0,
      total_cash_game_pot: response.data.total_cash_game_pot || 0,
      // Combined
      total_players: response.data.total_players || 0,
    }
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

onMounted(async () => {
  try {
    await Promise.all([
      fetchStatistics(),
      tournamentsStore.fetchUpcomingTournaments(),
      cashGamesStore.fetchActiveCashGames()
    ])
  } catch (error) {
    console.error('Failed to load homepage data:', error)
  } finally {
    isLoading.value = false
  }
})
</script>
