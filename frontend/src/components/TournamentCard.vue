<template>
  <router-link
    :to="`/tournament/${tournament.id}`"
    class="tournament-card block"
  >
    <!-- Tournament Image -->
    <div class="relative h-48 overflow-hidden">
      <img
        :src="tournament.image_url_full || '/images/tournament-default.png'"
        :alt="tournament.name"
        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
        @error="handleImageError"
      />
      
      <!-- Overlay Gradient -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>
      
      <!-- Featured Badge -->
      <div v-if="featured" class="absolute top-3 left-3">
        <div class="badge bg-amber-500 text-slate-900 border-0 font-bold">
          <Star :size="14" class="fill-current" />
          Featured
        </div>
      </div>
      
      <!-- Status Badge -->
      <div class="absolute top-3 right-3">
        <span class="badge" :class="statusClass">
          {{ statusText }}
        </span>
      </div>

      <!-- Prize Pool -->
      <div class="absolute bottom-3 left-3 right-3">
        <div class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm rounded-lg px-3 py-2 border border-slate-800">
          <Trophy :size="18" class="text-amber-400" />
          <span class="text-lg font-bold text-white">₾{{ formatNumber(tournament.guaranteed_prize_pool || 0) }}</span>
          <span class="text-slate-400 text-sm ml-auto">Prize Pool</span>
        </div>
      </div>
    </div>

    <!-- Card Content -->
    <div class="p-6">
      <!-- Tournament Name -->
      <h3 class="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:text-emerald-400 transition-colors">
        {{ tournament.name }}
      </h3>

      <!-- Details Grid -->
      <div class="space-y-2.5 mb-4">
        <!-- Tournament Type -->
        <div class="flex items-center gap-2 text-slate-300 text-sm">
          <Trophy :size="16" class="text-amber-400 flex-shrink-0" />
          <span>{{ formatType(tournament.tournament_type) }}</span>
        </div>

        <!-- Date -->
        <div class="flex items-center gap-2 text-slate-300 text-sm">
          <Calendar :size="16" class="text-emerald-400 flex-shrink-0" />
          <span>{{ formatDate(tournament.start_date) }}</span>
        </div>

        <!-- Location -->
        <div v-if="tournament.venue_name" class="flex items-center gap-2 text-slate-300 text-sm">
          <MapPin :size="16" class="text-emerald-400 flex-shrink-0" />
          <span class="truncate">{{ tournament.venue_name }}</span>
        </div>

        <!-- Game Type -->
        <div class="flex items-center gap-2 text-slate-300 text-sm">
          <Sparkles :size="16" class="text-emerald-400 flex-shrink-0" />
          <span>{{ formatGameType(tournament.game_type) }}</span>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-3 gap-2 mb-4">
        <div class="bg-slate-800 rounded-lg p-2 text-center">
          <div class="text-xs text-slate-400 mb-0.5">Buy-in</div>
          <div class="text-sm font-bold text-white">₾{{ tournament.buy_in }}</div>
        </div>
        <div class="bg-slate-800 rounded-lg p-2 text-center">
          <div class="text-xs text-slate-400 mb-0.5">Players</div>
          <div class="text-sm font-bold text-white">{{ tournament.total_seats }}</div>
        </div>
        <div class="bg-slate-800 rounded-lg p-2 text-center">
          <div class="text-xs text-slate-400 mb-0.5">Seats</div>
          <div class="text-sm font-bold" :class="seatsColor">
            {{ tournament.occupied_seats || 0 }}
          </div>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mb-4">
        <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
          <span>Registration Progress</span>
          <span class="font-semibold">{{ fillPercentage }}%</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" :class="progressColor" :style="{ width: `${fillPercentage}%` }"></div>
        </div>
      </div>

      <!-- Action Button -->
      <button
        class="w-full py-2.5 text-sm font-semibold flex items-center justify-center gap-2"
        :class="{
          'btn-primary': canRegister && !isUserRegistered,
          'bg-emerald-600 text-white hover:bg-emerald-700': isUserRegistered,
          'opacity-50 cursor-not-allowed': !canRegister && !isUserRegistered
        }"
        :disabled="!canRegister && !isUserRegistered"
      >
        <component :is="buttonIcon" :size="18" />
        <span>{{ buttonText }}</span>
      </button>
    </div>
  </router-link>
</template>

<script setup>
import { computed } from 'vue'
import {
  Trophy, Calendar, MapPin, Sparkles, Star, CheckCircle, Users, XCircle
} from 'lucide-vue-next'

const props = defineProps({
  tournament: {
    type: Object,
    required: true
  },
  featured: {
    type: Boolean,
    default: false
  }
})

const statusClass = computed(() => {
  switch (props.tournament.registration_status) {
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
  switch (props.tournament.registration_status) {
    case 'open':
      return 'Open'
    case 'closing_soon':
      return 'Closing Soon'
    case 'full':
      return 'Full'
    case 'closed':
      return 'Closed'
    default:
      return props.tournament.status || 'Upcoming'
  }
})

const fillPercentage = computed(() => {
  const occupied = props.tournament.occupied_seats || 0
  const total = props.tournament.total_seats || 1
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
  return props.tournament.user_is_registered > 0
})

const canRegister = computed(() => {
  // If user is already registered, can't register again
  if (isUserRegistered.value) return false
  
  return props.tournament.registration_status === 'open' || 
         props.tournament.registration_status === 'closing_soon'
})

const buttonText = computed(() => {
  // If user is registered, show registered status
  if (isUserRegistered.value) return 'You\'re Registered!'
  
  if (props.tournament.registration_status === 'full') return 'Tournament Full'
  if (props.tournament.registration_status === 'closed') return 'Registration Closed'
  return 'View & Register'
})

const buttonIcon = computed(() => {
  // If user is registered, show check icon
  if (isUserRegistered.value) return CheckCircle
  
  if (props.tournament.registration_status === 'full') return Users
  if (props.tournament.registration_status === 'closed') return XCircle
  return CheckCircle
})

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

const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const handleImageError = (event) => {
  event.target.src = '/images/tournament-default.png'
}
</script>
