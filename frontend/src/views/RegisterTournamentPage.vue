<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <router-link to="/tournaments" class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 mb-4 transition-colors">
          <ArrowLeft :size="20" />
          <span>Back to Tournaments</span>
        </router-link>
        <h1 class="section-title mb-2">Register for Tournament</h1>
        <p class="text-slate-400 text-lg">Complete your registration to secure your seat</p>
      </div>
    </div>

    <div class="page-container py-8">
      <div v-if="isLoading" class="card p-12 text-center">
        <LoadingSpinner class="mb-4" />
        <p class="text-slate-400">Loading tournament details...</p>
      </div>

      <div v-else-if="tournament" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Registration Form -->
        <div class="lg:col-span-2">
          <div class="card p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
              <CheckCircle :size="28" class="text-emerald-400" />
              Registration Form
            </h2>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Player Info (Auto-filled) -->
              <div class="p-4 bg-slate-800 rounded-lg border border-slate-700">
                <p class="text-sm text-slate-400 mb-2">Registering as</p>
                <p class="text-lg font-bold text-white">{{ authStore.fullName }}</p>
                <p class="text-sm text-slate-400">{{ authStore.user.email }}</p>
              </div>

              <!-- Agreement -->
              <div class="space-y-4">
                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    v-model="form.agreeToTerms"
                    type="checkbox"
                    class="mt-1 w-5 h-5 rounded bg-slate-800 border-slate-700 text-emerald-600 focus:ring-emerald-600 focus:ring-offset-slate-900"
                  />
                  <span class="text-slate-300 text-sm">
                    I agree to the tournament rules and understand that the buy-in of 
                    <strong class="text-white">₾{{ tournament.buy_in }}</strong> 
                    is required to participate
                  </span>
                </label>

                <label class="flex items-start gap-3 cursor-pointer">
                  <input
                    v-model="form.agreeToPrivacy"
                    type="checkbox"
                    class="mt-1 w-5 h-5 rounded bg-slate-800 border-slate-700 text-emerald-600 focus:ring-emerald-600 focus:ring-offset-slate-900"
                  />
                  <span class="text-slate-300 text-sm">
                    I agree to the privacy policy and terms of service
                  </span>
                </label>
              </div>

              <!-- Error Message -->
              <div v-if="errorMessage" class="p-4 bg-red-900/20 border border-red-800 rounded-lg">
                <div class="flex items-center gap-2 text-red-400">
                  <AlertCircle :size="20" />
                  <span>{{ errorMessage }}</span>
                </div>
              </div>

              <!-- Success Message -->
              <div v-if="successMessage" class="p-4 bg-emerald-900/20 border border-emerald-800 rounded-lg">
                <div class="flex items-center gap-2 text-emerald-400">
                  <CheckCircle :size="20" />
                  <span>{{ successMessage }}</span>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="!canSubmit || isSubmitting"
                class="w-full py-4 text-lg font-bold flex items-center justify-center gap-2"
                :class="[
                  isWaitingListRegistration 
                    ? 'bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white'
                    : 'btn-primary',
                  { 'opacity-50 cursor-not-allowed': !canSubmit || isSubmitting }
                ]"
              >
                <Loader v-if="isSubmitting" :size="24" class="animate-spin" />
                <CheckCircle v-else :size="24" />
                <span>{{ isSubmitting ? 'Processing...' : (isWaitingListRegistration ? 'Join Waiting List' : 'Complete Registration') }}</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Tournament Summary -->
        <div class="space-y-6">
          <!-- Tournament Info -->
          <div class="card p-6">
            <h3 class="text-lg font-bold text-white mb-4">Tournament Summary</h3>
            
            <div class="space-y-4">
              <div>
                <div class="text-slate-400 text-sm mb-1">Tournament Name</div>
                <div class="text-white font-semibold">{{ tournament.name }}</div>
              </div>

              <div class="divider"></div>

              <div class="flex items-center justify-between">
                <div class="text-slate-400 text-sm">Date</div>
                <div class="text-white font-semibold">{{ formatDate(tournament.start_date) }}</div>
              </div>

              <div class="flex items-center justify-between">
                <div class="text-slate-400 text-sm">Game Type</div>
                <div class="text-white font-semibold">{{ formatGameType(tournament.game_type) }}</div>
              </div>

              <div v-if="tournament.venue_name" class="flex items-center justify-between">
                <div class="text-slate-400 text-sm">Location</div>
                <div class="text-white font-semibold text-right text-sm">{{ tournament.venue_name }}</div>
              </div>
            </div>
          </div>

          <!-- Payment Summary -->
          <div class="card bg-gradient-to-br from-emerald-900/30 to-emerald-800/20 border-emerald-700 p-6">
            <h3 class="text-lg font-bold text-white mb-4">Payment Summary</h3>
            
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-slate-300">Buy-in</span>
                <span class="text-white font-bold">₾{{ tournament.buy_in }}</span>
              </div>

              <div v-if="tournament.registration_fee" class="flex items-center justify-between">
                <span class="text-slate-300">Registration Fee</span>
                <span class="text-white font-bold">₾{{ tournament.registration_fee }}</span>
              </div>

              <div class="divider"></div>

              <div class="flex items-center justify-between">
                <span class="text-white font-bold text-lg">Total</span>
                <span class="text-emerald-400 font-black text-2xl">
                  ₾{{ totalCost }}
                </span>
              </div>
            </div>
          </div>

          <!-- Prize Pool -->
          <div class="card p-6 text-center">
            <Trophy :size="48" class="text-amber-400 mx-auto mb-3" />
            <div class="text-slate-400 text-sm mb-2">Prize Pool</div>
            <div class="text-3xl font-black text-amber-400">
              ₾{{ (tournament.guaranteed_prize_pool || 0).toLocaleString() }}
            </div>
          </div>

          <!-- Seats Info -->
          <div class="card p-6">
            <div class="flex items-center justify-between mb-3">
              <span class="text-slate-400 text-sm">Seats Available</span>
              <span class="text-white font-bold">{{ availableSeats }} / {{ tournament.total_seats }}</span>
            </div>
            <div class="progress-bar">
              <div class="progress-fill bg-emerald-500" :style="{ width: `${fillPercentage}%` }"></div>
            </div>
          </div>

          <!-- Waiting List Info -->
          <div v-if="isWaitingListRegistration" class="card p-6 bg-orange-900/20 border border-orange-700/30">
            <div class="flex items-center gap-2 mb-3">
              <span class="text-2xl">⏱</span>
              <h3 class="text-lg font-bold text-orange-300">Waiting List Registration</h3>
            </div>
            <p class="text-sm text-orange-200 mb-3">
              This tournament is currently full. By registering, you'll be added to the waiting list and will be notified if a seat becomes available.
            </p>
            <div v-if="tournament.waiting_list_count > 0" class="text-xs text-orange-300">
              <strong>{{ tournament.waiting_list_count }}</strong> {{ tournament.waiting_list_count === 1 ? 'person' : 'people' }} currently on the waiting list
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else class="card p-12 text-center max-w-md mx-auto">
        <AlertCircle :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">Tournament Not Found</h3>
        <p class="text-slate-400 mb-6">Unable to load tournament details</p>
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
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTournamentsStore } from '../stores/tournaments'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import {
  CheckCircle, Trophy, Calendar, ArrowLeft, AlertCircle, Loader
} from 'lucide-vue-next'
import axios from 'axios'

const props = defineProps({
  tournamentId: {
    type: String,
    required: true
  }
})

const router = useRouter()
const authStore = useAuthStore()
const tournamentsStore = useTournamentsStore()

const tournament = ref(null)
const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  agreeToTerms: false,
  agreeToPrivacy: false
})

const totalCost = computed(() => {
  if (!tournament.value) return 0
  return tournament.value.buy_in + (tournament.value.registration_fee || 0)
})

const availableSeats = computed(() => {
  if (!tournament.value) return 0
  return Math.max(0, tournament.value.total_seats - (tournament.value.occupied_seats || 0))
})

const isWaitingListRegistration = computed(() => {
  return tournament.value?.registration_status === 'full' && 
         tournament.value?.waiting_list_enabled === true
})

const fillPercentage = computed(() => {
  if (!tournament.value) return 0
  const occupied = tournament.value.occupied_seats || 0
  const total = tournament.value.total_seats || 1
  return Math.min(100, Math.round((occupied / total) * 100))
})

const canSubmit = computed(() => {
  return form.value.agreeToTerms && form.value.agreeToPrivacy && !isSubmitting.value
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

const formatGameType = (gameType) => {
  return gameType?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Poker'
}

const handleSubmit = async () => {
  if (!canSubmit.value) return

  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const response = await axios.post('/register', {
      tournament_id: tournament.value.id
    })

    // Check if user was added to waiting list
    if (response.data.status === 'waiting') {
      successMessage.value = `You've been added to the waiting list at position ${response.data.waiting_position || ''}! You'll be notified if a seat becomes available. Redirecting...`
    } else {
      successMessage.value = 'Registration successful! Redirecting...'
    }

    setTimeout(() => {
      router.push(`/my-tournaments`)
    }, 3000)
  } catch (error) {
    console.error('Registration failed:', error)
    errorMessage.value = error.response?.data?.message || 'Registration failed. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  try {
    tournament.value = await tournamentsStore.fetchTournament(props.tournamentId)
  } catch (error) {
    console.error('Failed to load tournament:', error)
  } finally {
    isLoading.value = false
  }
})
</script>
