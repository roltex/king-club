<template>
  <div class="page-container">
    <div class="content-wrapper max-w-2xl mx-auto">
      <LoadingSpinner v-if="loading" size="lg" text="Processing check-in..." />

      <template v-else-if="result">
        <div class="text-center">
          <div class="glass-card w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
            <CheckCircle v-if="result.success" :size="48" class="text-green-400" />
            <XCircle v-else :size="48" class="text-red-400" />
          </div>

          <h1 class="text-5xl font-bold mb-4">
            {{ result.success ? 'Welcome!' : 'Check-In Failed' }}
          </h1>

          <div v-if="result.success" class="glass-card p-8 mb-8">
            <p class="text-xl mb-6">{{ result.user }}</p>
            
            <div class="grid grid-cols-2 gap-6">
              <div class="glass-card p-6 bg-white/5">
                <p class="text-white/60 text-sm mb-2">Table</p>
                <p class="text-5xl font-bold text-poker-400">{{ result.table }}</p>
              </div>

              <div class="glass-card p-6 bg-white/5">
                <p class="text-white/60 text-sm mb-2">Seat</p>
                <p class="text-5xl font-bold text-accent-400">{{ result.seat }}</p>
              </div>
            </div>
          </div>

          <div v-else class="glass-card p-6 bg-red-500/10 border border-red-500/30 mb-8">
            <p class="text-red-300">{{ result.message }}</p>
          </div>

          <router-link to="/" class="btn-primary inline-flex items-center gap-2">
            <ArrowLeft :size="20" />
            Back to Home
          </router-link>
        </div>
      </template>

      <template v-else>
        <div class="glass-card p-12 text-center">
          <AlertCircle :size="64" class="text-red-400 mx-auto mb-4" />
          <h2 class="text-3xl font-bold mb-4">Invalid Check-In Link</h2>
          <p class="text-white/70 mb-8">
            Please scan the QR code again or contact staff for assistance.
          </p>
          <router-link to="/" class="btn-primary inline-flex items-center gap-2">
            <ArrowLeft :size="20" />
            Back to Home
          </router-link>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useReservationStore } from '../stores/reservationStore'
import { CheckCircle, XCircle, AlertCircle, ArrowLeft } from 'lucide-vue-next'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const route = useRoute()
const reservationStore = useReservationStore()

const loading = ref(true)
const result = ref(null)

onMounted(async () => {
  const reservationId = route.query.id
  
  if (!reservationId) {
    loading.value = false
    return
  }
  
  try {
    result.value = await reservationStore.checkIn(reservationId)
  } catch (error) {
    result.value = {
      success: false,
      message: error.response?.data?.message || 'Check-in failed'
    }
  } finally {
    loading.value = false
  }
})
</script>

