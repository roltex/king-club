<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <h1 class="section-title mb-2">My Tournaments</h1>
        <p class="text-slate-400 text-lg">View and manage your tournament registrations</p>
      </div>
    </div>

    <div class="page-container py-8">
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="stat-card">
          <div class="text-4xl font-black text-blue-400 mb-2">{{ stats.upcoming }}</div>
          <div class="text-slate-400">Upcoming Tournaments</div>
        </div>
        <div class="stat-card">
          <div class="text-4xl font-black text-emerald-400 mb-2">{{ stats.attended }}</div>
          <div class="text-slate-400">Attended</div>
        </div>
        <div class="stat-card">
          <div class="text-4xl font-black text-amber-400 mb-2">₾{{ stats.totalSpent }}</div>
          <div class="text-slate-400">Total Spent</div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="flex gap-2 mb-6">
        <button
          @click="activeTab = 'upcoming'"
          class="px-6 py-3 rounded-lg font-semibold transition-colors"
          :class="activeTab === 'upcoming' 
            ? 'bg-emerald-600 text-white' 
            : 'bg-slate-800 text-slate-400 hover:text-white'"
        >
          Upcoming
        </button>
        <button
          @click="activeTab = 'past'"
          class="px-6 py-3 rounded-lg font-semibold transition-colors"
          :class="activeTab === 'past' 
            ? 'bg-emerald-600 text-white' 
            : 'bg-slate-800 text-slate-400 hover:text-white'"
        >
          Past
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <SkeletonCard v-for="i in 3" :key="i" />
      </div>

      <!-- Tournaments List -->
      <div v-else-if="displayedTournaments.length > 0" class="grid grid-cols-1 gap-4">
        <div
          v-for="registration in displayedTournaments"
          :key="registration.id"
          class="card p-6 hover:border-emerald-700/50 transition-all"
        >
          <div class="flex flex-col md:flex-row gap-6">
            <!-- Tournament Image -->
            <div class="w-full md:w-48 h-32 rounded-lg overflow-hidden flex-shrink-0">
              <img
                :src="registration.tournament?.image_url_full || '/images/tournament-default.png'"
                :alt="registration.tournament?.name"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
            </div>

            <!-- Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <h3 class="text-xl font-bold text-white mb-2">
                    {{ registration.tournament?.name }}
                  </h3>
                  <div class="flex flex-wrap gap-4 text-sm text-slate-400">
                    <div class="flex items-center gap-2">
                      <Calendar :size="16" class="text-emerald-400" />
                      {{ formatDate(registration.tournament?.start_date) }}
                    </div>
                    <div v-if="registration.tournament?.venue_name" class="flex items-center gap-2">
                      <MapPin :size="16" class="text-emerald-400" />
                      {{ registration.tournament?.venue_name }}
                    </div>
                  </div>
                </div>
                <span class="badge" :class="getStatusClass(registration.status)">
                  {{ registration.status }}
                </span>
              </div>

              <div class="flex flex-wrap items-center gap-4 mb-4">
                <div class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Table:</div>
                  <div class="text-white font-bold">{{ registration.table_number || 'TBA' }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Seat:</div>
                  <div class="text-white font-bold">{{ registration.seat_number || 'TBA' }}</div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="text-slate-400 text-sm">Buy-in:</div>
                  <div class="text-white font-bold">₾{{ registration.tournament?.buy_in }}</div>
                </div>
              </div>

              <div class="flex flex-wrap gap-3">
                <router-link
                  :to="`/tournament/${registration.tournament_id}`"
                  class="btn-primary px-4 py-2 text-sm flex items-center gap-2"
                >
                  <Eye :size="16" />
                  View Details
                </router-link>

                <button
                  v-if="registration.status === 'confirmed' && activeTab === 'upcoming'"
                  @click="cancelRegistration(registration.id)"
                  class="btn-secondary px-4 py-2 text-sm flex items-center gap-2 text-red-400 hover:bg-red-900/20"
                >
                  <XCircle :size="16" />
                  Cancel Registration
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <Trophy :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">
          {{ activeTab === 'upcoming' ? 'No Upcoming Tournaments' : 'No Past Tournaments' }}
        </h3>
        <p class="text-slate-400 mb-6">
          {{ activeTab === 'upcoming' 
            ? 'You haven\'t registered for any tournaments yet' 
            : 'You haven\'t attended any tournaments yet' }}
        </p>
        <router-link to="/tournaments" class="btn-primary px-6 py-3 inline-flex items-center gap-2">
          <Trophy :size="20" />
          Browse Tournaments
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import SkeletonCard from '../components/SkeletonCard.vue'
import {
  Trophy, Calendar, MapPin, Eye, XCircle
} from 'lucide-vue-next'
import axios from 'axios'

const authStore = useAuthStore()
const isLoading = ref(true)
const activeTab = ref('upcoming')
const registrations = ref([])

const stats = ref({
  upcoming: 0,
  attended: 0,
  totalSpent: 0
})

const displayedTournaments = computed(() => {
  const now = new Date()
  return registrations.value.filter(reg => {
    const tournamentDate = new Date(reg.tournament?.start_date)
    if (activeTab.value === 'upcoming') {
      return tournamentDate >= now
    } else {
      return tournamentDate < now
    }
  })
})

const getStatusClass = (status) => {
  switch (status) {
    case 'confirmed':
      return 'badge-success'
    case 'checked_in':
      return 'badge-info'
    case 'cancelled':
      return 'badge-error'
    default:
      return 'badge-warning'
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleImageError = (event) => {
  event.target.src = '/images/tournament-default.png'
}

const cancelRegistration = async (registrationId) => {
  if (!confirm('Are you sure you want to cancel this registration?')) return

  try {
    await axios.delete(`/registrations/${registrationId}`)
    await fetchRegistrations()
  } catch (error) {
    console.error('Failed to cancel registration:', error)
    alert('Failed to cancel registration')
  }
}

const fetchRegistrations = async () => {
  try {
    const response = await axios.get('/player/tournament-history')
    const history = response.data.history || []
    
    // Map the tournament history to registrations format
    registrations.value = history.map(item => ({
      id: item.id,
      tournament_id: item.tournament.id,
      tournament: {
        id: item.tournament.id,
        name: item.tournament.name,
        start_date: item.tournament.start_date,
        tournament_type: item.tournament.tournament_type,
        game_type: item.tournament.game_type,
        buy_in: item.tournament.buy_in,
        venue_name: item.tournament.venue_name || 'TBA',
        image_url_full: item.tournament.image_url_full || '/images/tournament-default.png'
      },
      status: item.status,
      table_number: item.table_number,
      seat_number: item.seat_number
    }))

    const now = new Date()
    stats.value.upcoming = registrations.value.filter(r => 
      new Date(r.tournament?.start_date) >= now && r.status !== 'cancelled'
    ).length

    stats.value.attended = registrations.value.filter(r => 
      new Date(r.tournament?.start_date) < now && r.status === 'checked_in'
    ).length

    stats.value.totalSpent = registrations.value.reduce((sum, r) => 
      sum + (r.tournament?.buy_in || 0), 0
    )
  } catch (error) {
    console.error('Failed to load registrations:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchRegistrations()
})
</script>
