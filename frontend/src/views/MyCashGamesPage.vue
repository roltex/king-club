<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <h1 class="section-title mb-2">My Cash Games</h1>
        <p class="text-slate-400 text-lg">View and manage your cash game seats</p>
      </div>
    </div>

    <div class="page-container py-8">
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="stat-card">
          <div class="text-4xl font-black text-blue-400 mb-2">{{ stats.active }}</div>
          <div class="text-slate-400">Active Seats</div>
        </div>
        <div class="stat-card">
          <div class="text-4xl font-black text-emerald-400 mb-2">₾{{ formatNumber(stats.totalStack) }}</div>
          <div class="text-slate-400">Total Stack</div>
        </div>
        <div class="stat-card">
          <div class="text-4xl font-black text-amber-400 mb-2">₾{{ formatNumber(stats.totalBuyIn) }}</div>
          <div class="text-slate-400">Total Buy-in</div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <SkeletonCard v-for="i in 3" :key="i" />
      </div>

      <!-- Cash Game Seats List -->
      <div v-else-if="seats.length > 0" class="grid grid-cols-1 gap-4">
        <div
          v-for="seat in seats"
          :key="seat.id"
          class="card p-6 hover:border-emerald-700/50 transition-all"
        >
          <div class="flex flex-col md:flex-row gap-6">
            <!-- Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="text-xl font-bold text-white mb-2">
                    {{ seat.cash_game.name }}
                  </h3>
                  <div class="flex flex-wrap gap-4 text-sm text-slate-400">
                    <div class="flex items-center gap-2">
                      <Users :size="16" class="text-emerald-400" />
                      Table {{ seat.cash_game.table_number }}
                    </div>
                    <div class="flex items-center gap-2">
                      <DollarSign :size="16" class="text-emerald-400" />
                      {{ seat.cash_game.stakes_display }}
                    </div>
                  </div>
                </div>
                <span class="badge" :class="getStatusClass(seat.status)">
                  {{ formatStatus(seat.status) }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-4 mb-4">
                <div v-if="seat.seat_number" class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Seat:</div>
                  <div class="text-white font-bold">{{ seat.seat_number }}</div>
                </div>
                <div v-if="seat.waiting_position" class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Waiting Position:</div>
                  <div class="text-white font-bold">#{{ seat.waiting_position }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Buy-in:</div>
                  <div class="text-white font-bold">₾{{ formatNumber(seat.buy_in_amount) }}</div>
                </div>
                <div v-if="seat.current_stack" class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Stack:</div>
                  <div class="text-emerald-400 font-bold">₾{{ formatNumber(seat.current_stack) }}</div>
                </div>
              </div>

              <div class="flex flex-wrap gap-3">
                <router-link
                  :to="`/cash-game/${seat.cash_game.id}`"
                  class="btn-primary px-4 py-2 text-sm flex items-center gap-2"
                >
                  <Eye :size="16" />
                  View Details
                </router-link>

                <button
                  v-if="seat.status !== 'left' && seat.status !== 'removed'"
                  @click="leaveSeat(seat.id)"
                  class="btn-secondary px-4 py-2 text-sm flex items-center gap-2 text-red-400 hover:bg-red-900/20"
                >
                  <XCircle :size="16" />
                  Leave Seat
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <DollarSign :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">No Active Cash Game Seats</h3>
        <p class="text-slate-400 mb-6">
          You haven't joined any cash games yet
        </p>
        <router-link to="/cash-games" class="btn-primary px-6 py-3 inline-flex items-center gap-2">
          <DollarSign :size="20" />
          Browse Cash Games
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useCashGamesStore } from '../stores/cashGames'
import SkeletonCard from '../components/SkeletonCard.vue'
import {
  DollarSign, Users, Eye, XCircle
} from 'lucide-vue-next'

const authStore = useAuthStore()
const cashGamesStore = useCashGamesStore()
const isLoading = ref(true)
const seats = ref([])

const stats = computed(() => {
  const active = seats.value.filter(s => ['seated', 'playing', 'away'].includes(s.status)).length
  const totalStack = seats.value.reduce((sum, s) => sum + (s.current_stack || 0), 0)
  const totalBuyIn = seats.value.reduce((sum, s) => sum + (s.buy_in_amount || 0), 0)
  
  return {
    active,
    totalStack,
    totalBuyIn
  }
})

const getStatusClass = (status) => {
  switch (status) {
    case 'seated':
      return 'badge-info'
    case 'playing':
      return 'badge-success'
    case 'away':
      return 'badge-warning'
    case 'waiting':
      return 'badge-warning'
    case 'left':
    case 'removed':
      return 'badge-error'
    default:
      return 'badge-warning'
  }
}

const formatStatus = (status) => {
  return status?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || status
}

const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const leaveSeat = async (seatId) => {
  if (!confirm('Are you sure you want to leave this cash game?')) return

  try {
    const result = await cashGamesStore.leaveCashGame(seatId)
    if (result.success) {
      await fetchSeats()
    } else {
      alert(result.message || 'Failed to leave cash game')
    }
  } catch (error) {
    console.error('Failed to leave seat:', error)
    alert('Failed to leave cash game')
  }
}

const fetchSeats = async () => {
  try {
    const seatsData = await cashGamesStore.getMyCashGameSeats()
    seats.value = seatsData
  } catch (error) {
    console.error('Failed to load cash game seats:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchSeats()
})
</script>

