<template>
  <div class="page-container">
    <div class="content-wrapper max-w-3xl mx-auto">
      <LoadingSpinner v-if="loading" size="lg" text="Loading your reservation..." />

      <template v-else-if="reservation">
        <!-- Success Header -->
        <div class="text-center mb-12">
          <div class="glass-card w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
            <CheckCircle v-if="!reservation.isWaiting" :size="48" class="text-green-400" />
            <Clock v-else :size="48" class="text-yellow-400" />
          </div>

          <h1 class="text-5xl font-bold mb-4">
            {{ reservation.status === 'waiting' ? 'Added to Waiting List' : 'Reservation Confirmed!' }}
          </h1>

          <p class="text-xl text-white/70">
            {{ reservation.status === 'waiting' 
              ? 'We\'ll notify you when a seat becomes available' 
              : 'Your seat is secured. Save your QR code for check-in.' 
            }}
          </p>
        </div>

        <!-- Waiting List Status -->
        <div v-if="reservation.status === 'waiting'" class="glass-card p-8 mb-8 text-center">
          <div class="mb-6">
            <div class="text-6xl font-bold text-yellow-400 mb-2">
              #{{ reservation.waiting_position }}
            </div>
            <p class="text-white/70">Your position in waiting list</p>
          </div>

          <div class="glass-card p-4 bg-yellow-500/10">
            <div class="flex items-start gap-3">
              <Info :size="20" class="text-yellow-400 flex-shrink-0" />
              <p class="text-sm text-left">
                Save your phone number to check your reservation status later. 
                You'll be automatically assigned a seat when one becomes available.
              </p>
            </div>
          </div>
        </div>

        <!-- Reservation Details (Reserved/Checked-In) -->
        <div v-else class="space-y-8">
          <!-- Seat Information -->
          <div class="glass-card p-8">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
              <MapPin :size="24" class="text-poker-400" />
              Your Seat Assignment
            </h2>

            <div class="grid grid-cols-2 gap-6">
              <div class="text-center glass-card p-6 bg-white/5">
                <p class="text-white/60 text-sm mb-2">Table Number</p>
                <p class="text-5xl font-bold text-poker-400">{{ reservation.table }}</p>
              </div>

              <div class="text-center glass-card p-6 bg-white/5">
                <p class="text-white/60 text-sm mb-2">Seat Number</p>
                <p class="text-5xl font-bold text-accent-400">{{ reservation.seat }}</p>
              </div>
            </div>
          </div>

          <!-- QR Code -->
          <div class="glass-card p-8 text-center">
            <h2 class="text-2xl font-bold mb-6 flex items-center justify-center gap-2">
              <QrCodeIcon :size="24" class="text-poker-400" />
              Your Check-In QR Code
            </h2>

            <div class="qr-container mx-auto mb-6 bg-white p-4 rounded-2xl">
              <qrcode-vue
                :value="reservation.qr"
                :size="280"
                level="H"
                class="mx-auto"
              />
            </div>

            <div class="glass-card p-4 bg-white/5 max-w-md mx-auto">
              <div class="flex items-start gap-3 text-left">
                <Smartphone :size="20" class="text-poker-400 flex-shrink-0" />
                <div class="text-sm text-white/70">
                  <p class="font-semibold mb-1">How to use:</p>
                  <p>Show this QR code at the venue entrance. Staff will scan it to confirm your check-in.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Personal Information -->
          <div class="glass-card p-8">
            <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
              <User :size="24" class="text-poker-400" />
              Personal Information
            </h2>

            <div class="space-y-4">
              <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-white/60">Name</span>
                <span class="font-semibold">{{ reservation.first_name }} {{ reservation.last_name }}</span>
              </div>

              <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-white/60">Phone</span>
                <span class="font-semibold">{{ reservation.phone }}</span>
              </div>

              <div v-if="reservation.email" class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-white/60">Email</span>
                <span class="font-semibold">{{ reservation.email }}</span>
              </div>

              <div class="flex justify-between items-center py-3">
                <span class="text-white/60">Status</span>
                <span :class="statusBadgeClass">{{ statusText }}</span>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-col sm:flex-row gap-4">
            <button
              @click="downloadQR"
              class="btn-secondary flex-1 flex items-center justify-center gap-2"
            >
              <Download :size="20" />
              Download QR Code
            </button>

            <button
              v-if="reservation.status === 'reserved'"
              @click="showCancelConfirm = true"
              class="btn-secondary flex-1 flex items-center justify-center gap-2 border-red-500/30 hover:bg-red-500/10"
            >
              <X :size="20" />
              Cancel Reservation
            </button>
          </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-12">
          <router-link to="/" class="btn-glass inline-flex items-center gap-2">
            <ArrowLeft :size="20" />
            Back to Home
          </router-link>
        </div>

        <!-- Cancel Confirmation Modal -->
        <div
          v-if="showCancelConfirm"
          class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
          @click.self="showCancelConfirm = false"
        >
          <div class="glass-card p-8 max-w-md">
            <h3 class="text-2xl font-bold mb-4">Cancel Reservation?</h3>
            <p class="text-white/70 mb-6">
              Are you sure you want to cancel your reservation? This action cannot be undone.
            </p>
            <div class="flex gap-4">
              <button
                @click="showCancelConfirm = false"
                class="btn-secondary flex-1"
              >
                Keep Reservation
              </button>
              <button
                @click="handleCancel"
                class="btn-primary flex-1 bg-red-500 hover:bg-red-600"
              >
                Yes, Cancel
              </button>
            </div>
          </div>
        </div>
      </template>

      <template v-else>
        <div class="glass-card p-12 text-center">
          <AlertCircle :size="64" class="text-red-400 mx-auto mb-4" />
          <h2 class="text-3xl font-bold mb-4">Reservation Not Found</h2>
          <p class="text-white/70 mb-8">
            We couldn't find your reservation. Please check the link or try again.
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
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useReservationStore } from '../stores/reservationStore'
import { useToastStore } from '../stores/toastStore'
import QrcodeVue from 'qrcode.vue'
import {
  CheckCircle, Clock, MapPin, QrCode as QrCodeIcon, User, Download, X,
  ArrowLeft, AlertCircle, Info, Smartphone
} from 'lucide-vue-next'
import LoadingSpinner from '../components/LoadingSpinner.vue'

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

const route = useRoute()
const router = useRouter()
const reservationStore = useReservationStore()
const toastStore = useToastStore()

const reservation = ref(null)
const loading = ref(true)
const showCancelConfirm = ref(false)

const statusText = computed(() => {
  const status = reservation.value?.status
  const map = {
    reserved: 'Reserved',
    waiting: 'Waiting',
    checked_in: 'Checked In',
    cancelled: 'Cancelled'
  }
  return map[status] || status
})

const statusBadgeClass = computed(() => {
  const status = reservation.value?.status
  const classes = {
    reserved: 'badge-info',
    waiting: 'badge-warning',
    checked_in: 'badge-success',
    cancelled: 'badge-danger'
  }
  return classes[status] || 'badge-info'
})

onMounted(async () => {
  try {
    reservation.value = await reservationStore.getReservation(props.id)
  } catch (error) {
    toastStore.error('Failed to load reservation')
  } finally {
    loading.value = false
  }
})

function downloadQR() {
  const canvas = document.querySelector('canvas')
  if (canvas) {
    const url = canvas.toDataURL('image/png')
    const link = document.createElement('a')
    link.download = `poker-ticket-table${reservation.value.table}-seat${reservation.value.seat}.png`
    link.href = url
    link.click()
    toastStore.success('QR code downloaded')
  }
}

async function handleCancel() {
  try {
    await reservationStore.cancelReservation(props.id)
    toastStore.success('Reservation cancelled successfully')
    showCancelConfirm.value = false
    router.push('/')
  } catch (error) {
    toastStore.error('Failed to cancel reservation')
  }
}
</script>

