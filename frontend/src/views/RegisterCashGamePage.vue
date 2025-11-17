<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <router-link to="/cash-games" class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 mb-4 transition-colors">
          <ArrowLeft :size="20" />
          <span>Back to Cash Games</span>
        </router-link>
        <h1 class="section-title mb-2">Join Cash Game</h1>
        <p class="text-slate-400 text-lg">Complete your registration to join the table</p>
      </div>
    </div>

    <div class="page-container py-8">
      <div v-if="isLoading" class="card p-12 text-center">
        <LoadingSpinner class="mb-4" />
        <p class="text-slate-400">Loading cash game details...</p>
      </div>

      <div v-else-if="cashGame" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Registration Form -->
        <div class="lg:col-span-2">
          <div class="card p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
              <CheckCircle :size="28" class="text-emerald-400" />
              Join Form
            </h2>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Player Info (Auto-filled) -->
              <div class="p-4 bg-slate-800 rounded-lg border border-slate-700">
                <p class="text-sm text-slate-400 mb-2">Joining as</p>
                <p class="text-lg font-bold text-white">{{ authStore.fullName }}</p>
                <p class="text-sm text-slate-400">{{ authStore.user.email }}</p>
              </div>

              <!-- Buy-in Amount -->
              <div>
                <label class="block text-sm font-semibold text-white mb-2">
                  Buy-in Amount
                </label>
                <div class="relative">
                  <DollarSign :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                  <input
                    v-model.number="form.buyInAmount"
                    type="number"
                    :min="cashGame.min_buy_in"
                    :max="cashGame.max_buy_in"
                    :step="cashGame.small_blind || 1"
                    class="input pl-10 w-full"
                    placeholder="Enter buy-in amount"
                    required
                  />
                </div>
                <p class="text-xs text-slate-400 mt-1">
                  Min: ₾{{ formatNumber(cashGame.min_buy_in) }} | 
                  Max: ₾{{ formatNumber(cashGame.max_buy_in) }} | 
                  Default: ₾{{ formatNumber(cashGame.default_buy_in || cashGame.min_buy_in) }}
                </p>
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
                    I agree to the cash game rules and understand that a buy-in of 
                    <strong class="text-white">₾{{ form.buyInAmount || cashGame.default_buy_in || cashGame.min_buy_in }}</strong> 
                    is required to join
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
                class="w-full py-4 text-lg font-bold flex items-center justify-center gap-2 btn-primary"
                :class="{ 'opacity-50 cursor-not-allowed': !canSubmit || isSubmitting }"
              >
                <Loader v-if="isSubmitting" :size="24" class="animate-spin" />
                <CheckCircle v-else :size="24" />
                <span>{{ isSubmitting ? 'Processing...' : 'Join Cash Game' }}</span>
              </button>
            </form>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Cash Game Summary -->
          <div class="card p-6">
            <h3 class="text-lg font-bold text-white mb-4">Cash Game Summary</h3>
            <div class="space-y-3">
              <div>
                <div class="text-xs text-slate-400 mb-1">Name</div>
                <div class="text-white font-semibold">{{ cashGame.name }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400 mb-1">Table</div>
                <div class="text-white font-semibold">Table {{ cashGame.table_number }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400 mb-1">Stakes</div>
                <div class="text-emerald-400 font-semibold">{{ cashGame.stakes_display }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400 mb-1">Game Type</div>
                <div class="text-white font-semibold">{{ formatGameType(cashGame.game_type) }}</div>
              </div>
              <div>
                <div class="text-xs text-slate-400 mb-1">Available Seats</div>
                <div class="text-white font-semibold">{{ cashGame.available_seats || 0 }} / {{ cashGame.seats_per_table }}</div>
              </div>
            </div>
          </div>
        </div>
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
  CheckCircle, AlertCircle, ArrowLeft, DollarSign, Loader
} from 'lucide-vue-next'

const props = defineProps({
  cashGameId: {
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
const isSubmitting = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  buyInAmount: null,
  agreeToTerms: false,
  agreeToPrivacy: false
})

const canSubmit = computed(() => {
  if (!form.value.agreeToTerms || !form.value.agreeToPrivacy) return false
  if (!form.value.buyInAmount) return false
  if (form.value.buyInAmount < cashGame.value?.min_buy_in) return false
  if (form.value.buyInAmount > cashGame.value?.max_buy_in) return false
  return true
})

const formatNumber = (num) => {
  return num?.toLocaleString() || '0'
}

const formatGameType = (gameType) => {
  return gameType?.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Cash Game'
}

const handleSubmit = async () => {
  if (!canSubmit.value) return

  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const result = await cashGamesStore.registerForCashGame(
      cashGame.value.id,
      form.value.buyInAmount
    )

    if (result.success) {
      successMessage.value = 'Successfully joined the cash game!'
      setTimeout(() => {
        router.push({
          name: 'Confirmation',
          params: { id: result.data.seat?.id || result.data.id }
        })
      }, 1500)
    } else {
      errorMessage.value = result.message || 'Failed to join cash game'
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'An error occurred'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(async () => {
  try {
    cashGame.value = await cashGamesStore.fetchCashGame(props.cashGameId)
    // Set default buy-in
    if (cashGame.value) {
      form.value.buyInAmount = cashGame.value.default_buy_in || cashGame.value.min_buy_in
    }
  } catch (error) {
    console.error('Failed to load cash game:', error)
    errorMessage.value = 'Failed to load cash game details'
  } finally {
    isLoading.value = false
  }
})
</script>

