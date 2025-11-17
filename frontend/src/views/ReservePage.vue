<template>
  <div class="page-container">
    <div class="content-wrapper max-w-2xl mx-auto">
      <PageHeader
        title="Reserve Your Seat"
        subtitle="Fill in your details to secure your spot at the tournament"
        :icon="Ticket"
      />

      <!-- Availability Status -->
      <div class="glass-card p-6 mb-8 text-center">
        <div class="flex items-center justify-center gap-4">
          <div class="flex items-center gap-2">
            <div :class="availabilityColor" class="w-3 h-3 rounded-full animate-pulse"></div>
            <span class="text-lg font-semibold">
              {{ availableSeats }} seats available
            </span>
          </div>
        </div>
        <p v-if="availableSeats === 0" class="text-yellow-300 mt-2 text-sm">
          All seats are full. You will be added to the waiting list.
        </p>
      </div>

      <!-- Reservation Form -->
      <form @submit.prevent="handleSubmit" class="glass-card p-8 space-y-6">
        <div>
          <label for="first_name" class="block text-sm font-medium mb-2">
            First Name *
          </label>
          <input
            id="first_name"
            v-model="formData.first_name"
            type="text"
            required
            class="input-glass"
            placeholder="John"
            :disabled="loading"
          />
        </div>

        <div>
          <label for="last_name" class="block text-sm font-medium mb-2">
            Last Name *
          </label>
          <input
            id="last_name"
            v-model="formData.last_name"
            type="text"
            required
            class="input-glass"
            placeholder="Doe"
            :disabled="loading"
          />
        </div>

        <div>
          <label for="phone" class="block text-sm font-medium mb-2">
            Phone Number *
          </label>
          <input
            id="phone"
            v-model="formData.phone"
            type="tel"
            required
            class="input-glass"
            placeholder="+995 555 123 123"
            :disabled="loading"
          />
          <p class="text-white/50 text-xs mt-1">
            This will be used to look up your reservation
          </p>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium mb-2">
            Email (Optional)
          </label>
          <input
            id="email"
            v-model="formData.email"
            type="email"
            class="input-glass"
            placeholder="john@example.com"
            :disabled="loading"
          />
        </div>

        <div class="glass-card p-4 bg-white/5">
          <div class="flex items-start gap-3">
            <Info :size="20" class="text-poker-400 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-white/70">
              <p class="font-semibold mb-1">Random Seat Assignment</p>
              <p>Your table and seat will be randomly assigned for fairness. You'll receive a QR code for check-in.</p>
            </div>
          </div>
        </div>

        <button
          type="submit"
          class="btn-primary w-full"
          :disabled="loading"
        >
          <LoadingSpinner v-if="loading" size="sm" />
          <template v-else>
            <Ticket class="inline mr-2" :size="20" />
            Reserve My Seat
          </template>
        </button>

        <router-link
          to="/"
          class="btn-secondary w-full text-center block"
        >
          <ArrowLeft class="inline mr-2" :size="20" />
          Back to Home
        </router-link>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useReservationStore } from '../stores/reservationStore'
import { useToastStore } from '../stores/toastStore'
import { Ticket, Info, ArrowLeft } from 'lucide-vue-next'
import PageHeader from '../components/PageHeader.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const router = useRouter()
const reservationStore = useReservationStore()
const toastStore = useToastStore()

const formData = ref({
  first_name: '',
  last_name: '',
  phone: '',
  email: ''
})

const loading = ref(false)
const stats = ref(null)

const availableSeats = computed(() => stats.value?.available_seats ?? 0)
const availabilityColor = computed(() => {
  const seats = availableSeats.value
  if (seats > 20) return 'bg-green-500'
  if (seats > 10) return 'bg-yellow-500'
  if (seats > 0) return 'bg-orange-500'
  return 'bg-red-500'
})

onMounted(async () => {
  try {
    stats.value = await reservationStore.fetchStatistics()
  } catch (error) {
    console.error('Failed to fetch statistics:', error)
  }
})

async function handleSubmit() {
  loading.value = true
  
  try {
    const result = await reservationStore.createReservation(formData.value)
    
    if (result.status === 'reserved') {
      toastStore.success('Seat reserved successfully!')
      router.push(`/confirmation/${result.reservation_id}`)
    } else if (result.status === 'waiting') {
      toastStore.warning('Added to waiting list')
      router.push(`/confirmation/${result.reservation_id}`)
    }
  } catch (error) {
    const errorMessage = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join(', ')
      : error.response?.data?.message || 'Failed to create reservation'
    
    toastStore.error(errorMessage)
  } finally {
    loading.value = false
  }
}
</script>

