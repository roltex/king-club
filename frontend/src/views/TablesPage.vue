<template>
  <div class="page-container">
    <div class="content-wrapper">
      <PageHeader
        title="Table Layout"
        subtitle="View all tables and seat assignments"
        :icon="LayoutGrid"
      />

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12 max-w-5xl mx-auto">
        <StatCard
          label="Total Seats"
          :value="stats?.total_seats ?? 54"
          :icon="Grid3x3"
          iconColor="text-poker-400"
        />
        <StatCard
          label="Occupied"
          :value="stats?.occupied_seats ?? 0"
          :icon="Users"
          iconColor="text-green-400"
        />
        <StatCard
          label="Available"
          :value="stats?.available_seats ?? 0"
          :icon="CheckCircle"
          iconColor="text-blue-400"
        />
        <StatCard
          label="Checked In"
          :value="stats?.checked_in ?? 0"
          :icon="UserCheck"
          iconColor="text-purple-400"
        />
      </div>

      <LoadingSpinner v-if="loading" size="lg" text="Loading table layout..." />

      <!-- Tables Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="table in tables"
          :key="table.table_number"
          class="glass-card p-6"
        >
          <h3 class="text-2xl font-bold text-center mb-6 flex items-center justify-center gap-2">
            <span class="text-poker-400">Table {{ table.table_number }}</span>
          </h3>

          <!-- Seats Grid -->
          <div class="grid grid-cols-3 gap-3">
            <div
              v-for="seat in table.seats"
              :key="seat.seat_number"
              :class="getSeatClass(seat)"
              class="aspect-square rounded-xl border-2 flex flex-col items-center justify-center p-2 transition-all duration-300 hover:scale-105"
              :title="seat.occupied ? seat.player.name : 'Available'"
            >
              <span class="text-xs opacity-60 mb-1">Seat</span>
              <span class="text-2xl font-bold">{{ seat.seat_number }}</span>
              
              <div v-if="seat.occupied" class="mt-2 text-center">
                <div class="text-xs truncate w-full px-1">
                  {{ truncateName(seat.player.name) }}
                </div>
                <div v-if="seat.player.checked_in" class="flex items-center justify-center gap-1 mt-1">
                  <CheckCircle :size="12" />
                  <span class="text-xs">In</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Table Stats -->
          <div class="mt-6 pt-4 border-t border-white/10 text-center text-sm text-white/60">
            {{ getOccupiedCount(table) }}/9 seats occupied
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="glass-card p-6 mt-12 max-w-3xl mx-auto">
        <h3 class="text-lg font-bold mb-4 text-center">Legend</h3>
        <div class="flex flex-wrap justify-center gap-6 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-gray-700 border-2 border-gray-600"></div>
            <span>Available</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-blue-500/20 border-2 border-blue-500"></div>
            <span>Reserved</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-6 h-6 rounded bg-green-500/20 border-2 border-green-500"></div>
            <span>Checked In</span>
          </div>
        </div>
      </div>

      <!-- Refresh Button -->
      <div class="text-center mt-8 space-y-4">
        <button
          @click="refreshData"
          class="btn-secondary inline-flex items-center gap-2"
          :disabled="loading"
        >
          <RotateCcw :size="20" :class="{ 'animate-spin': loading }" />
          Refresh
        </button>

        <div>
          <router-link to="/" class="btn-glass inline-flex items-center gap-2">
            <ArrowLeft :size="20" />
            Back to Home
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useReservationStore } from '../stores/reservationStore'
import {
  Grid3x3, Users, CheckCircle, UserCheck, RotateCcw, ArrowLeft, LayoutGrid
} from 'lucide-vue-next'
import PageHeader from '../components/PageHeader.vue'
import StatCard from '../components/StatCard.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const reservationStore = useReservationStore()

const tables = ref([])
const stats = ref(null)
const loading = ref(true)

onMounted(async () => {
  await loadData()
})

async function loadData() {
  loading.value = true
  
  try {
    [tables.value, stats.value] = await Promise.all([
      reservationStore.fetchTableLayout(),
      reservationStore.fetchStatistics()
    ])
  } catch (error) {
    console.error('Failed to load data:', error)
  } finally {
    loading.value = false
  }
}

function refreshData() {
  loadData()
}

function getSeatClass(seat) {
  if (!seat.occupied) {
    return 'bg-gray-700/30 border-gray-600 text-gray-400'
  }
  
  if (seat.status === 'checked_in') {
    return 'bg-green-500/20 border-green-500 text-green-300'
  }
  
  return 'bg-blue-500/20 border-blue-500 text-blue-300'
}

function getOccupiedCount(table) {
  return table.seats.filter(seat => seat.occupied).length
}

function truncateName(name) {
  if (name.length <= 12) return name
  return name.substring(0, 12) + '...'
}
</script>

