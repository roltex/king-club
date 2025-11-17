<template>
  <div class="min-h-screen bg-slate-950">
    <LoadingSpinner v-if="isLoading" class="py-20" />

    <div v-else-if="cashGame">
      <!-- Hero Banner with Overlay - Text Left, Image Right -->
      <div class="relative h-[450px] md:h-[500px] overflow-hidden">
        <!-- Banner Image -->
        <img
          :src="cashGame.image_url_full || '/images/tournament-default.png'"
          :alt="cashGame.name"
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
                <span v-if="cashGame.is_featured" class="badge bg-amber-500 text-slate-900 border-0 font-bold">
                  <Star :size="14" class="fill-current" />
                  Featured
                </span>
                <span class="badge badge-info backdrop-blur-sm">
                  <DollarSign :size="14" />
                  {{ formatGameType(cashGame.game_type) }}
                </span>
              </div>

              <!-- Cash Game Title -->
              <h1 class="text-4xl md:text-6xl font-black text-white mb-4 leading-tight" style="text-shadow: 0 4px 12px rgba(0,0,0,0.5)">
                {{ cashGame.name }}
              </h1>

              <!-- Info Pills -->
              <div class="flex flex-wrap items-center gap-3 mb-6">
                <div class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-700">
                  <Users :size="18" class="text-emerald-400" />
                  <span class="text-white font-semibold">Table {{ cashGame.table_number }}</span>
                </div>
                <div class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-700">
                  <DollarSign :size="18" class="text-emerald-400" />
                  <span class="text-white font-semibold">{{ cashGame.stakes_display }}</span>
                </div>
                <div v-if="cashGame.venue_name" class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm px-4 py-2 rounded-full border border-slate-700">
                  <MapPin :size="18" class="text-emerald-400" />
                  <span class="text-white font-semibold">{{ cashGame.venue_name }}</span>
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
          <!-- Stakes -->
          <div class="card bg-gradient-to-br from-emerald-900/30 to-emerald-800/20 border-emerald-700/50 p-6 text-center hover:scale-105 transition-transform">
            <DollarSign :size="24" class="text-emerald-400 mx-auto mb-2" />
            <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Stakes</div>
            <div class="text-2xl font-black text-emerald-400">
              {{ cashGame.stakes_display }}
            </div>
          </div>

          <!-- Buy-in Range -->
          <div class="card p-6 text-center hover:scale-105 transition-transform">
            <div class="text-3xl font-black text-emerald-400 mb-1">₾{{ formatNumber(cashGame.min_buy_in) }}-{{ formatNumber(cashGame.max_buy_in) }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Buy-in Range</div>
          </div>

          <!-- Available Seats -->
          <div class="card p-6 text-center hover:scale-105 transition-transform">
            <div class="text-3xl font-black" :class="seatsColor">{{ cashGame.available_seats || 0 }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Available</div>
            <div class="progress-bar mt-2">
              <div class="progress-fill" :class="progressColor" :style="{ width: `${fillPercentage}%` }"></div>
            </div>
          </div>

          <!-- Active Players -->
          <div class="card p-6 text-center hover:scale-105 transition-transform">
            <div class="text-3xl font-black text-white">{{ cashGame.active_seats_count || 0 }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Playing</div>
            <div class="text-xs text-slate-500 mt-1">of {{ cashGame.seats_per_table }} seats</div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Left Column: Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Cash Game Details -->
            <div class="card p-6">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-full bg-emerald-600/20 flex items-center justify-center">
                  <Info :size="20" class="text-emerald-400" />
                </div>
                <h2 class="text-xl font-bold text-white">Cash Game Details</h2>
              </div>
              
              <div class="grid grid-cols-2 gap-5">
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Game Type</div>
                  <div class="text-white font-bold text-lg">{{ formatGameType(cashGame.game_type) }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Structure</div>
                  <div class="text-emerald-400 font-bold text-lg">{{ cashGame.structure || 'Standard' }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Min Buy-in</div>
                  <div class="text-emerald-400 font-bold text-lg">₾{{ formatNumber(cashGame.min_buy_in) }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Max Buy-in</div>
                  <div class="text-emerald-400 font-bold text-lg">₾{{ formatNumber(cashGame.max_buy_in) }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Default Buy-in</div>
                  <div class="text-emerald-400 font-bold text-lg">₾{{ formatNumber(cashGame.default_buy_in || cashGame.min_buy_in) }}</div>
                </div>
                <div class="space-y-1">
                  <div class="text-xs text-slate-500 uppercase tracking-wider">Table</div>
                  <div class="text-white font-bold text-lg">{{ cashGame.table_number }}</div>
                </div>
              </div>

              <div v-if="cashGame.description" class="mt-6 pt-6 border-t border-slate-800">
                <div class="text-sm text-slate-300 leading-relaxed" v-html="cashGame.description"></div>
              </div>
            </div>
          </div>

          <!-- Right Column: Sidebar -->
          <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card p-6 bg-gradient-to-br from-emerald-900/20 to-slate-900">
              <h3 class="text-lg font-bold text-white mb-4">Quick Actions</h3>
              <button
                v-if="canJoin || isUserSeated"
                @click="handleJoin"
                :class="isUserSeated 
                  ? 'w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105' 
                  : 'w-full bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-900 py-4 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105'"
              >
                <CheckCircle :size="20" />
                {{ isUserSeated ? 'You\'re Seated!' : 'Join Now' }}
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
                :to="`/cash-game/${id}/tables`"
                class="w-full mt-3 bg-slate-700 hover:bg-slate-600 text-white py-3 font-bold rounded-lg flex items-center justify-center gap-2 transition-all hover:scale-105"
              >
                <LayoutGrid :size="20" />
                See Tables
              </router-link>
            </div>

            <!-- Operating Hours -->
            <div v-if="cashGame.opens_at || cashGame.closes_at" class="card p-6 bg-gradient-to-r from-slate-900/50 to-emerald-900/20 border-emerald-700/30">
              <div class="flex items-center gap-2 mb-4">
                <Clock :size="18" class="text-emerald-400" />
                <h3 class="text-lg font-bold text-white">Operating Hours</h3>
              </div>
              <div class="space-y-3">
                <div v-if="cashGame.opens_at" class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                    <Calendar :size="16" class="text-emerald-400" />
                  </div>
                  <div class="flex-1">
                    <div class="text-xs text-slate-400 mb-1">Opens</div>
                    <div class="text-white font-semibold text-sm">{{ formatDate(cashGame.opens_at) }}</div>
                  </div>
                </div>
                <div v-if="cashGame.closes_at" class="flex items-start gap-3">
                  <div class="w-8 h-8 rounded-full bg-red-600/20 flex items-center justify-center flex-shrink-0">
                    <Clock :size="16" class="text-red-400" />
                  </div>
                  <div class="flex-1">
                    <div class="text-xs text-slate-400 mb-1">Closes</div>
                    <div class="text-white font-semibold text-sm">{{ formatDate(cashGame.closes_at) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Location -->
            <div v-if="cashGame.address || cashGame.google_maps_url" class="card p-6">
              <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-emerald-600/20 flex items-center justify-center">
                  <MapPin :size="18" class="text-emerald-400" />
                </div>
                <h3 class="text-lg font-bold text-white">Location</h3>
              </div>
              <div class="space-y-2 text-sm text-slate-300">
                <p v-if="cashGame.venue_name" class="font-semibold text-white">{{ cashGame.venue_name }}</p>
                <p v-if="cashGame.address">{{ cashGame.address }}</p>
                <p v-if="cashGame.city || cashGame.country">
                  {{ [cashGame.city, cashGame.country].filter(Boolean).join(', ') }}
                </p>
                <a
                  v-if="cashGame.google_maps_url"
                  :href="cashGame.google_maps_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 mt-2"
                >
                  <MapPin :size="16" />
                  <span>View on Google Maps</span>
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
        <h3 class="text-2xl font-bold text-white mb-2">Cash Game Not Found</h3>
        <p class="text-slate-400 mb-6">The cash game you're looking for doesn't exist or has been removed</p>
        <router-link to="/cash-games" class="btn-primary px-6 py-3 inline-flex items-center gap-2">
          <ArrowLeft :size="20" />
          Back to Cash Games
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCashGamesStore } from '../stores/cashGames'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import {
  DollarSign, Calendar, MapPin, Sparkles, Star, CheckCircle, Info, LayoutGrid,
  Phone, Mail, AlertCircle, ArrowLeft, Clock, Users
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
const cashGamesStore = useCashGamesStore()

const cashGame = ref(null)
const isLoading = ref(true)

const statusClass = computed(() => {
  switch (cashGame.value?.status) {
    case 'open':
    case 'active':
      return 'badge-success'
    case 'running':
      return 'badge-info'
    case 'closed':
      return 'bg-slate-700 text-slate-400 border-slate-600'
    default:
      return 'badge-info'
  }
})

const statusText = computed(() => {
  switch (cashGame.value?.status) {
    case 'open':
      return 'Open'
    case 'active':
      return 'Active'
    case 'running':
      return 'Running'
    case 'closed':
      return 'Closed'
    default:
      return cashGame.value?.status || 'Available'
  }
})

const fillPercentage = computed(() => {
  return cashGame.value?.fill_percentage || 0
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

const isUserSeated = computed(() => {
  return cashGame.value?.user_is_seated > 0
})

const canJoin = computed(() => {
  // If user is already seated, can't join again
  if (isUserSeated.value) return false
  
  // If cash game is open/active/running, allow joining
  if (['open', 'active', 'running'].includes(cashGame.value?.status)) {
    return true
  }
  
  return false
})

const formatDate = (dateString) => {
  if (!dateString) return 'TBA'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatGameType = (gameType) => {
  return gameType?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Cash Game'
}

const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const handleImageError = (event) => {
  event.target.src = '/images/tournament-default.png'
}

const handleJoin = () => {
  if (!authStore.isLoggedIn) {
    router.push({
      name: 'Login',
      query: { redirect: route.fullPath }
    })
    return
  }

  if (isUserSeated.value) {
    // Already seated, maybe show seat details
    return
  }

  // Navigate to registration page
  router.push(`/register-cash-game/${cashGame.value.id}`)
}

onMounted(async () => {
  try {
    cashGame.value = await cashGamesStore.fetchCashGame(props.id)
  } catch (error) {
    console.error('Failed to load cash game:', error)
  } finally {
    isLoading.value = false
  }
})
</script>

