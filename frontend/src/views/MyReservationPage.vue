<template>
  <div class="page-container">
    <div class="content-wrapper max-w-2xl mx-auto">
      <PageHeader
        title="Find My Reservation"
        subtitle="Enter your phone number to view your reservation"
        :icon="Search"
      />

      <div class="glass-card p-8">
        <form @submit.prevent="handleSearch" class="space-y-6">
          <div>
            <label for="phone" class="block text-sm font-medium mb-2">
              Phone Number
            </label>
            <input
              id="phone"
              v-model="phone"
              type="tel"
              required
              class="input-glass"
              placeholder="+995 555 123 123"
              :disabled="loading"
              autofocus
            />
            <p class="text-white/50 text-xs mt-1">
              Enter the phone number you used for reservation
            </p>
          </div>

          <button
            type="submit"
            class="btn-primary w-full"
            :disabled="loading || !phone"
          >
            <LoadingSpinner v-if="loading" size="sm" />
            <template v-else>
              <Search :size="20" class="inline mr-2" />
              Find Reservation
            </template>
          </button>
        </form>

        <!-- Error Message -->
        <div v-if="error" class="mt-6 glass-card p-4 bg-red-500/10 border-red-500/30">
          <div class="flex items-start gap-3">
            <AlertCircle :size="20" class="text-red-400 flex-shrink-0" />
            <div class="text-sm">
              <p class="font-semibold mb-1">Reservation Not Found</p>
              <p class="text-white/70">{{ error }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="text-center mt-8">
        <router-link to="/" class="btn-glass inline-flex items-center gap-2">
          <ArrowLeft :size="20" />
          Back to Home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useReservationStore } from '../stores/reservationStore'
import { Search, AlertCircle, ArrowLeft } from 'lucide-vue-next'
import PageHeader from '../components/PageHeader.vue'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const router = useRouter()
const reservationStore = useReservationStore()

const phone = ref('')
const loading = ref(false)
const error = ref(null)

async function handleSearch() {
  loading.value = true
  error.value = null
  
  try {
    const reservation = await reservationStore.getReservationByPhone(phone.value)
    router.push(`/confirmation/${reservation.id}`)
  } catch (err) {
    error.value = err.response?.data?.message || 'No reservation found for this phone number'
  } finally {
    loading.value = false
  }
}
</script>

