<template>
  <div class="min-h-screen bg-slate-950">
    <LoadingSpinner v-if="isLoading" class="py-20" />

    <div v-else-if="tournament">
      <!-- Hero Banner with Overlay - Text Left, Image Right -->
      <div class="relative h-[450px] md:h-[500px] overflow-hidden">
        <!-- Banner Image -->
        <img
          :src="tournament.banner_url_full || tournament.image_url_full || '/images/tournament-default.png'"
          :alt="tournament.name"
          class="w-full h-full object-cover"
          @error="handleImageError"
        />
        
        <!-- Multi-layer Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-slate-950/20 via-slate-950/60 to-slate-950"></div>
        <div class="absolute inset-0 bg-gradient-to-l from-slate-950/80 via-transparent to-slate-950/80"></div>
        
        <!-- Content Overlay - Positioned on Left -->
        <div class="absolute inset-0 flex items-end">
          <div class="w-full page-container pb-8">
            <div class="max-w-4xl">
              <!-- Status Badges -->
              <div class="flex flex-wrap items-center gap-2 mb-4">
                <span class="badge" :class="statusClass">
                  {{ statusText }}
                </span>
                <span v-if="tournament.is_featured" class="badge bg-amber-500 text-slate-900 border-0 font-bold">
                  <Star :size="14" class="fill-current" />
                  Featured
                </span>
                <span class="badge badge-info backdrop-blur-sm">
                  <Trophy :size="14" />
                  {{ formatType(tournament.tournament_type) }}
                </span>
              </div>

              <!-- Tournament Title -->
              <h1 class="text-4xl md:text-6xl font-black text-white mb-4 leading-tight" style="text-shadow: 0 4px 12px rgba(0,0,0,0.5)">
                {{ tournament.name }}
              </h1>

              <!-- Info Pills -->
              <div class="flex flex-wrap items-center gap-3 mb-6">
                <div class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-700">
                  <Calendar :size="18" class="text-emerald-400" />
                  <span class="text-white font-semibold">{{ formatDate(tournament.start_date) }}</span>
                </div>
                <div v-if="tournament.venue_name" class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-700">
                  <MapPin :size="18" class="text-emerald-400" />
                  <span class="text-white font-semibold">{{ tournament.venue_name }}</span>
                </div>
                <div class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-700">
                  <Sparkles :size="18" class="text-emerald-400" />
                  <span class="text-white font-semibold">{{ formatGameType(tournament.game_type) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats & Content -->
      <div class="page-container -mt-12 relative z-10">
        <!-- Elevated Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <!-- Prize Pool -->
          <div class="card bg-gradient-to-br from-amber-900/30 to-amber-800/20 border-amber-700/50 p-6 text-center hover:scale-105 transition-transform">
            <Trophy :size="24" class="text-amber-400 mx-auto mb-2" />
            <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Prize Pool</div>
            <div class="text-2xl font-black text-amber-400">
              ₾{{ (tournament.actual_prize_pool || tournament.guaranteed_prize_pool || 0).toLocaleString() }}
            </div>
            <div v-if="tournament.guaranteed_prize_pool" class="text-xs text-slate-500 mt-1">
              GTD ₾{{ tournament.guaranteed_prize_pool.toLocaleString() }}
            </div>
          </div>

          <!-- Buy-in -->
          <div class="card p-6 text-center hover:scale-105 transition-transform">
            <div class="text-3xl font-black text-emerald-400 mb-1">₾{{ tournament.buy_in }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Buy-in</div>
          </div>

          <!-- Available Seats -->
          <div class="card p-6 text-center hover:scale-105 transition-transform">
            <div class="text-3xl font-black" :class="seatsColor">{{ Math.max(0, tournament.total_seats - (tournament.occupied_seats || 0)) }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Available</div>
            <div class="progress-bar mt-2">
              <div class="progress-fill" :class="progressColor" :style="{ width: `${fillPercentage}%` }"></div>
            </div>
          </div>

          <!-- Registered -->
          <div class="card p-6 text-center hover:scale-105 transition-transform">
            <div class="text-3xl font-black text-white">{{ tournament.occupied_seats || 0 }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Registered</div>
            <div class="text-xs text-slate-500 mt-1">of {{ tournament.total_seats }} seats</div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column: Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Tournament Details -->
            <div class="card p-6">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-emerald-600/20 flex items-center justify-center">
                  <Info :size="20" class="text-emerald-400" />
                </div>
                <h2 class="text-xl font-bold text-white">Tournament Details</h2>
              </div>
              
              <div class="grid grid-cols-2 gap-5">
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Type</div>
                  <div class="text-white font-bold text-lg">{{ formatType(tournament.tournament_type) }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Game</div>
                  <div class="text-white font-bold text-lg">{{ formatGameType(tournament.game_type) }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Starting Stack</div>
                  <div class="text-emerald-400 font-bold text-lg">{{ tournament.starting_chips?.toLocaleString() || 'TBA' }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Level Duration</div>
                  <div class="text-emerald-400 font-bold text-lg">{{ tournament.level_duration || 20 }} min</div>
                </div>
              </div>

              <div v-if="tournament.description" class="mt-6 pt-6 border-t border-slate-800">
                <div class="text-sm text-slate-300 leading-relaxed" v-html="tournament.description"></div>
              </div>
            </div>

            <!-- Blind Structure -->
            <div v-if="tournament.blind_structure && tournament.blind_structure.length > 0" class="card p-6">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-emerald-600/20 flex items-center justify-center">
                  <TrendingUp :size="20" class="text-emerald-400" />
                </div>
                <h2 class="text-xl font-bold text-white">Blind Structure</h2>
              </div>
              
              <div class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="border-b-2 border-slate-800">
                      <th class="text-left py-3 px-2 text-slate-400 font-bold uppercase tracking-wider text-xs">Level</th>
                      <th class="text-right py-3 px-2 text-slate-400 font-bold uppercase tracking-wider text-xs">Small</th>
                      <th class="text-right py-3 px-2 text-slate-400 font-bold uppercase tracking-wider text-xs">Big</th>
                      <th class="text-right py-3 px-2 text-slate-400 font-bold uppercase tracking-wider text-xs">Ante</th>
                      <th class="text-right py-3 px-2 text-slate-400 font-bold uppercase tracking-wider text-xs">Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="level in tournament.blind_structure.slice(0, showAllBlinds ? undefined : 8)" :key="level.level" class="border-b border-slate-800/30 hover:bg-slate-900/30 transition-colors">
                      <td class="py-3 px-2 font-black text-emerald-400">Lvl {{ level.level }}</td>
                      <td class="text-right px-2 text-white font-semibold">{{ level.small }}</td>
                      <td class="text-right px-2 text-white font-semibold">{{ level.big }}</td>
                      <td class="text-right px-2 text-slate-400">{{ level.ante || 0 }}</td>
                      <td class="text-right px-2 text-slate-400">{{ level.duration }}m</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <button
                v-if="tournament.blind_structure.length > 8"
                @click="showAllBlinds = !showAllBlinds"
                class="mt-4 w-full py-2 text-emerald-400 hover:text-emerald-300 hover:bg-emerald-950/30 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 transition-colors"
              >
                {{ showAllBlinds ? 'Show Less' : `Show All ${tournament.blind_structure.length} Levels` }}
                <ChevronDown :size="16" :class="{ 'rotate-180': showAllBlinds }" class="transition-transform" />
              </button>
              </div>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card p-6 bg-gradient-to-br from-emerald-900/20 to-slate-900">
              <h3 class="text-lg font-bold text-white mb-4">Quick Actions</h3>
              <button
                v-if="canRegister || isUserRegistered"
                @click="handleRegister"
                :class="isUserRegistered 
                  ? 'w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105' 
                  : isWaitingListRegistration
                    ? 'w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white py-4 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105'
                    : 'w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-900 py-4 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105'"
              >
                <CheckCircle :size="20" />
                {{ isUserRegistered ? 'Registered!' : (isWaitingListRegistration ? 'Join Waiting List' : 'Register Now') }}
              </button>
              <button
                v-else
                disabled
                class="w-full bg-slate-800 text-slate-500 py-4 font-bold rounded-lg cursor-not-allowed"
              >
                {{ statusText }}
              </button>
              
              <!-- See Tables Button -->
              <router-link
                :to="`/tournament/${id}/tables`"
                class="w-full mt-3 bg-slate-700 hover:bg-slate-600 text-white py-3 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105"
              >
                <LayoutGrid :size="20" />
                See Tables
              </router-link>
              
              <!-- Waiting List Info -->
              <div v-if="isWaitingListRegistration && !isUserRegistered" class="mt-4 p-3 bg-orange-500/20 border border-orange-500/30 rounded-lg">
                <p class="text-sm text-orange-300 flex items-center gap-2">
                  <span>⏱</span>
                  <span>Tournament is full. You'll be added to the waiting list and notified if a seat becomes available.</span>
                </p>
                <p v-if="tournament?.waiting_list_count > 0" class="text-xs text-orange-400 mt-2">
                  {{ tournament.waiting_list_count }} {{ tournament.waiting_list_count === 1 ? 'person' : 'people' }} currently waiting
                </p>
              </div>
            </div>

            <!-- Registration Timeline -->
            <div class="card p-6 bg-gradient-to-r from-slate-900/50 to-emerald-900/20 border-emerald-700/30">
              <div class="flex items-center gap-2 mb-4">
                <Clock :size="18" class="text-emerald-400" />
                <h3 class="text-lg font-bold text-white">Registration Timeline</h3>
              </div>
              <div class="space-y-3">
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                    <Calendar :size="16" class="text-emerald-400" />
                  </div>
                  <div class="flex-1">
                    <div class="text-xs text-slate-400 mb-1">Opens</div>
                    <div class="text-white font-semibold text-sm">{{ formatDate(tournament.registration_start) }}</div>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-red-600/20 flex items-center justify-center flex-shrink-0">
                    <Clock :size="16" class="text-red-400" />
                  </div>
                  <div class="flex-1">
                    <div class="text-xs text-slate-400 mb-1">Closes</div>
                    <div class="text-white font-semibold text-sm">{{ formatDate(tournament.registration_end) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Players List with Tabs -->
            <div class="card p-0 overflow-hidden">
              <!-- Tabs Header -->
              <div class="flex border-b border-slate-800">
                <button
                  @click="activeTab = 'registered'"
                  :class="activeTab === 'registered' 
                    ? 'flex-1 px-4 py-3 text-sm font-bold text-emerald-400 bg-emerald-900/20 border-b-2 border-emerald-500 transition-all' 
                    : 'flex-1 px-4 py-3 text-sm font-semibold text-slate-400 hover:text-slate-300 hover:bg-slate-900/30 transition-all'"
                >
                  <div class="flex items-center justify-center gap-2">
                    <Trophy :size="16" />
                    <span>Players</span>
                    <span v-if="registeredPlayers.length > 0" class="badge badge-success text-xs ml-1">{{ registeredPlayers.length }}</span>
                  </div>
                </button>
                <button
                  @click="activeTab = 'waiting'"
                  :class="activeTab === 'waiting' 
                    ? 'flex-1 px-4 py-3 text-sm font-bold text-amber-400 bg-amber-900/20 border-b-2 border-amber-500 transition-all' 
                    : 'flex-1 px-4 py-3 text-sm font-semibold text-slate-400 hover:text-slate-300 hover:bg-slate-900/30 transition-all'"
                >
                  <div class="flex items-center justify-center gap-2">
                    <Clock :size="16" />
                    <span>Waiting</span>
                    <span v-if="waitingList.length > 0" class="badge badge-warning text-xs ml-1">{{ waitingList.length }}</span>
                  </div>
                </button>
              </div>

              <!-- Tab Content -->
              <div class="p-4">
                <!-- Registered Players Tab -->
                <div v-if="activeTab === 'registered'">
                  <div v-if="registeredPlayers.length > 0" class="space-y-2 max-h-80 overflow-y-auto custom-scrollbar">
                    <div v-for="(player, index) in registeredPlayers" :key="player.id" class="flex items-start gap-2 p-2 rounded-lg bg-slate-900/30 hover:bg-slate-900/50 border border-slate-800/50 transition-all">
                      <div class="w-6 h-6 rounded-full bg-emerald-600/20 flex items-center justify-center text-emerald-400 font-bold text-xs flex-shrink-0 mt-0.5">
                        {{ index + 1 }}
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="text-white font-semibold text-sm truncate">{{ player.player_name }}</div>
                        <div class="flex items-center gap-2 mt-1">
                          <span v-if="player.table_number && player.seat_number" class="text-xs text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded">
                            T{{ player.table_number }}-S{{ player.seat_number }}
                          </span>
                          <span v-if="player.status === 'checked_in'" class="text-xs text-emerald-400">
                            ✓
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 text-slate-500">
                    <Trophy :size="32" class="mx-auto mb-2 text-slate-700" />
                    <p class="text-xs">No players registered yet</p>
                  </div>
                </div>

                <!-- Waiting List Tab -->
                <div v-if="activeTab === 'waiting'">
                  <div v-if="waitingList.length > 0" class="space-y-2 max-h-80 overflow-y-auto custom-scrollbar">
                    <div v-for="player in waitingList" :key="player.id" class="flex items-start gap-2 p-2 rounded-lg bg-slate-900/30 hover:bg-slate-900/50 border border-slate-800/50 transition-all">
                      <div class="w-6 h-6 rounded-full bg-amber-600/20 flex items-center justify-center text-amber-400 font-bold text-xs flex-shrink-0 mt-0.5">
                        #{{ player.position || '?' }}
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="text-white font-semibold text-sm truncate">{{ player.player_name }}</div>
                        <div v-if="player.city || player.country" class="text-xs text-slate-500 truncate mt-0.5">
                          {{ [player.city, player.country].filter(Boolean).join(', ') }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-8 text-slate-500">
                    <Clock :size="32" class="mx-auto mb-2 text-slate-700" />
                    <p class="text-xs">No waiting list</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact -->
            <div v-if="tournament.contact_email || tournament.contact_phone" class="card p-6">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-emerald-600/20 flex items-center justify-center">
                  <Phone :size="18" class="text-emerald-400" />
                </div>
                <h3 class="text-lg font-bold text-white">Contact</h3>
              </div>
              <div class="space-y-3">
                <a v-if="tournament.contact_email" :href="`mailto:${tournament.contact_email}`" class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 hover:bg-slate-900 border border-slate-800 hover:border-emerald-700 transition-all group">
                  <Mail :size="18" class="text-emerald-400 group-hover:scale-110 transition-transform" />
                  <span class="text-slate-300 group-hover:text-white text-sm truncate">{{ tournament.contact_email }}</span>
                </a>
                <a v-if="tournament.contact_phone" :href="`tel:${tournament.contact_phone}`" class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 hover:bg-slate-900 border border-slate-800 hover:border-emerald-700 transition-all group">
                  <Phone :size="18" class="text-emerald-400 group-hover:scale-110 transition-transform" />
                  <span class="text-slate-300 group-hover:text-white text-sm">{{ tournament.contact_phone }}</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="page-container py-20">
      <div class="card p-12 text-center max-w-md mx-auto">
        <AlertCircle :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">Tournament Not Found</h3>
        <p class="text-slate-400 mb-6">The tournament you're looking for doesn't exist or has been removed</p>
        <router-link to="/tournaments" class="btn-primary px-6 py-3 inline-flex items-center gap-2">
          <ArrowLeft :size="20" />
          Back to Tournaments
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTournamentsStore } from '../stores/tournaments'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import {
  Trophy, Calendar, MapPin, Sparkles, Star, CheckCircle, Info, TrendingUp,
  ChevronDown, Phone, Mail, AlertCircle, ArrowLeft, Clock, LayoutGrid
} from 'lucide-vue-next'

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const tournamentsStore = useTournamentsStore()

const tournament = ref(null)
const isLoading = ref(true)
const showAllBlinds = ref(false)
const registeredPlayers = ref([])
const waitingList = ref([])
const isLoadingPlayers = ref(false)
const activeTab = ref('registered')

const statusClass = computed(() => {
  switch (tournament.value?.registration_status) {
    case 'open':
      return 'badge-success'
    case 'closing_soon':
      return 'badge-warning'
    case 'full':
      return 'badge-error'
    case 'closed':
      return 'bg-slate-700 text-slate-400 border-slate-600'
    default:
      return 'badge-info'
  }
})

const statusText = computed(() => {
  switch (tournament.value?.registration_status) {
    case 'open':
      return 'Registration Open'
    case 'closing_soon':
      return 'Closing Soon'
    case 'full':
      return 'Tournament Full'
    case 'closed':
      return 'Registration Closed'
    default:
      return tournament.value?.status || 'Upcoming'
  }
})

const fillPercentage = computed(() => {
  if (!tournament.value) return 0
  const occupied = tournament.value.occupied_seats || 0
  const total = tournament.value.total_seats || 1
  return Math.min(100, Math.round((occupied / total) * 100))
})

const progressColor = computed(() => {
  const percentage = fillPercentage.value
  if (percentage >= 90) return 'bg-red-500'
  if (percentage >= 70) return 'bg-amber-500'
  return 'bg-emerald-500'
})

const seatsColor = computed(() => {
  const percentage = fillPercentage.value
  if (percentage >= 90) return 'text-red-400'
  if (percentage >= 70) return 'text-amber-400'
  return 'text-emerald-400'
})

const isUserRegistered = computed(() => {
  return tournament.value?.user_is_registered > 0
})

const canRegister = computed(() => {
  // If user is already registered, can't register again
  if (isUserRegistered.value) return false
  
  // If registration is open or closing soon, allow registration
  if (tournament.value?.registration_status === 'open' || 
      tournament.value?.registration_status === 'closing_soon') {
    return true
  }
  
  // If tournament is full, allow registration only if waiting list is enabled
  if (tournament.value?.registration_status === 'full') {
    return tournament.value?.waiting_list_enabled === true
  }
  
  return false
})

const isWaitingListRegistration = computed(() => {
  return tournament.value?.registration_status === 'full' && 
         tournament.value?.waiting_list_enabled === true
})

const handleRegister = () => {
  if (!authStore.isLoggedIn) {
    router.push({
      name: 'Login',
      query: { redirect: route.fullPath }
    })
    return
  }

  // If already registered, go to my tournaments
  if (isUserRegistered.value) {
    router.push('/my-tournaments')
    return
  }

  router.push({
    name: 'RegisterTournament',
    params: { tournamentId: tournament.value.id }
  })
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

const formatType = (type) => {
  return type?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Tournament'
}

const formatGameType = (gameType) => {
  return gameType?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Poker'
}

const handleImageError = (event) => {
  event.target.src = '/images/tournament-default.png'
}

const fetchRegisteredPlayers = async () => {
  try {
    const { default: axios } = await import('axios')
    console.log('Fetching registered players for tournament:', props.id)
    const response = await axios.get(`/tournaments/${props.id}/registered-players`)
    console.log('Registered players response:', response.data)
    registeredPlayers.value = response.data.registered_players || []
    console.log('Registered players count:', registeredPlayers.value.length)
  } catch (error) {
    console.error('Failed to load registered players:', error)
    console.error('Error details:', error.response?.data || error.message)
  }
}

const fetchWaitingList = async () => {
  try {
    const { default: axios } = await import('axios')
    console.log('Fetching waiting list for tournament:', props.id)
    const response = await axios.get(`/tournaments/${props.id}/waiting-list`)
    console.log('Waiting list response:', response.data)
    waitingList.value = response.data.waiting_list || []
    console.log('Waiting list count:', waitingList.value.length)
  } catch (error) {
    console.error('Failed to load waiting list:', error)
    console.error('Error details:', error.response?.data || error.message)
  }
}

onMounted(async () => {
  try {
    tournament.value = await tournamentsStore.fetchTournament(props.id)
    // Fetch players and waiting list in parallel
    await Promise.all([
      fetchRegisteredPlayers(),
      fetchWaitingList()
    ])
  } catch (error) {
    console.error('Failed to load tournament:', error)
  } finally {
    isLoading.value = false
  }
})
</script>
