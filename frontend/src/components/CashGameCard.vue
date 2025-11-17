<template>
  <router-link
    :to="`/cash-game/${cashGame.id}`"
    class="tournament-card block"
  >
    <!-- Cash Game Image -->
    <div class="relative h-48 overflow-hidden">
      <img
        :src="cashGame.image_url_full || '/images/tournament-default.png'"
        :alt="cashGame.name"
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

      <!-- Stakes Display -->
      <div class="absolute bottom-3 left-3 right-3">
        <div class="flex items-center gap-2 bg-slate-900/80 backdrop-blur-sm rounded-lg px-3 py-2 border border-slate-800">
          <DollarSign :size="18" class="text-emerald-400" />
          <span class="text-lg font-bold text-white">{{ cashGame.stakes_display }}</span>
          <span class="text-slate-400 text-sm ml-auto">Stakes</span>
        </div>
      </div>
    </div>

    <!-- Card Content -->
    <div class="p-6">
      <!-- Cash Game Name -->
      <h3 class="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:text-emerald-400 transition-colors">
        {{ cashGame.name }}
      </h3>

      <!-- Details Grid -->
      <div class="space-y-2.5 mb-4">
        <!-- Table Number -->
        <div class="flex items-center gap-2 text-slate-300 text-sm">
          <Users :size="16" class="text-emerald-400 flex-shrink-0" />
          <span>Table {{ cashGame.table_number }}</span>
        </div>

        <!-- Game Type -->
        <div class="flex items-center gap-2 text-slate-300 text-sm">
          <Sparkles :size="16" class="text-emerald-400 flex-shrink-0" />
          <span>{{ formatGameType(cashGame.game_type) }}</span>
        </div>

        <!-- Location -->
        <div v-if="cashGame.venue_name" class="flex items-center gap-2 text-slate-300 text-sm">
          <MapPin :size="16" class="text-emerald-400 flex-shrink-0" />
          <span class="truncate">{{ cashGame.venue_name }}</span>
        </div>

        <!-- Buy-in Range -->
        <div class="flex items-center gap-2 text-slate-300 text-sm">
          <DollarSign :size="16" class="text-emerald-400 flex-shrink-0" />
          <span>₾{{ formatNumber(cashGame.min_buy_in) }} - ₾{{ formatNumber(cashGame.max_buy_in) }}</span>
        </div>
      </div>

      <!-- Stats Row -->
      <div class="grid grid-cols-3 gap-2 mb-4">
        <div class="bg-slate-800 rounded-lg p-2 text-center">
          <div class="text-xs text-slate-400 mb-0.5">Buy-in</div>
          <div class="text-sm font-bold text-white">₾{{ formatNumber(cashGame.default_buy_in || cashGame.min_buy_in) }}</div>
        </div>
        <div class="bg-slate-800 rounded-lg p-2 text-center">
          <div class="text-xs text-slate-400 mb-0.5">Players</div>
          <div class="text-sm font-bold text-white">{{ cashGame.active_seats_count || 0 }}</div>
        </div>
        <div class="bg-slate-800 rounded-lg p-2 text-center">
          <div class="text-xs text-slate-400 mb-0.5">Seats</div>
          <div class="text-sm font-bold" :class="seatsColor">
            {{ cashGame.available_seats || 0 }}
          </div>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="mb-4">
        <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
          <span>Fill Rate</span>
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
          'btn-primary': canJoin && !isUserSeated,
          'bg-emerald-600 text-white hover:bg-emerald-700': isUserSeated,
          'opacity-50 cursor-not-allowed': !canJoin && !isUserSeated
        }"
        :disabled="!canJoin && !isUserSeated"
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
  DollarSign, MapPin, Sparkles, Star, CheckCircle, Users, XCircle
} from 'lucide-vue-next'

const props = defineProps({
  cashGame: {
    type: Object,
    required: true
  },
  featured: {
    type: Boolean,
    default: false
  }
})

const statusClass = computed(() => {
  switch (props.cashGame.status) {
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
  switch (props.cashGame.status) {
    case 'open':
      return 'Open'
    case 'active':
      return 'Active'
    case 'running':
      return 'Running'
    case 'closed':
      return 'Closed'
    default:
      return props.cashGame.status || 'Available'
  }
})

const fillPercentage = computed(() => {
  return props.cashGame.fill_percentage || 0
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
  return props.cashGame.user_is_seated > 0
})

const canJoin = computed(() => {
  // If user is already seated, can't join again
  if (isUserSeated.value) return false
  
  // If cash game is open/active/running, allow joining
  if (['open', 'active', 'running'].includes(props.cashGame.status)) {
    return true
  }
  
  return false
})

const buttonText = computed(() => {
  // If user is seated, show seated status
  if (isUserSeated.value) {
    if (props.cashGame.user_seat?.status === 'waiting') {
      return `Waiting #${props.cashGame.user_seat.waiting_position}`
    }
    return 'You\'re Seated!'
  }
  
  if (props.cashGame.status === 'closed') return 'Closed'
  if (fillPercentage.value >= 100) {
    return props.cashGame.enable_waiting_list ? 'Join Waiting List' : 'Full'
  }
  return 'Join Now'
})

const buttonIcon = computed(() => {
  // If user is seated, show check icon
  if (isUserSeated.value) return CheckCircle
  
  if (props.cashGame.status === 'closed') return XCircle
  if (fillPercentage.value >= 100 && props.cashGame.enable_waiting_list) return Users
  return CheckCircle
})

const formatGameType = (gameType) => {
  return gameType?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Cash Game'
}

const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const handleImageError = (event) => {
  event.target.src = '/images/tournament-default.png'
}
</script>

