<template>
  <div class="page-container">
    <div class="content-wrapper max-w-4xl mx-auto">
      <PageHeader
        title="QR Code Scanner"
        subtitle="Scan customer QR codes for check-in"
        :icon="ScanLine"
      />

      <div class="glass-card p-8">
        <!-- Scanner Area -->
        <div v-if="!scanned" class="space-y-6">
          <div class="glass-card bg-black/30 rounded-2xl overflow-hidden">
            <div id="qr-reader" class="w-full"></div>
          </div>

          <div class="glass-card p-4 bg-white/5">
            <div class="flex items-start gap-3">
              <Info :size="20" class="text-poker-400 flex-shrink-0" />
              <div class="text-sm text-white/70">
                <p class="font-semibold mb-1">How to scan:</p>
                <p>Position the customer's QR code within the frame. The system will automatically detect and process it.</p>
              </div>
            </div>
          </div>

          <button
            v-if="isScanning"
            @click="stopScanner"
            class="btn-secondary w-full"
          >
            <X :size="20" class="inline mr-2" />
            Stop Scanner
          </button>
          <button
            v-else
            @click="startScanner"
            class="btn-primary w-full"
          >
            <Camera :size="20" class="inline mr-2" />
            Start Scanner
          </button>
        </div>

        <!-- Check-in Result -->
        <div v-else class="text-center">
          <div class="mb-8">
            <div v-if="checkInResult.success" class="glass-card w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 bg-green-500/20">
              <CheckCircle :size="48" class="text-green-400" />
            </div>
            <div v-else class="glass-card w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 bg-red-500/20">
              <XCircle :size="48" class="text-red-400" />
            </div>

            <h2 class="text-3xl font-bold mb-4">
              {{ checkInResult.success ? 'Check-In Successful!' : 'Check-In Failed' }}
            </h2>

            <p v-if="checkInResult.success" class="text-xl text-white/70">
              Welcome to the tournament!
            </p>
            <p v-else class="text-xl text-red-300">
              {{ checkInResult.message }}
            </p>
          </div>

          <!-- Player Details (Success) -->
          <div v-if="checkInResult.success" class="glass-card p-6 mb-8 max-w-md mx-auto">
            <div class="space-y-4 text-left">
              <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-white/60">Player</span>
                <span class="font-semibold">{{ checkInResult.user }}</span>
              </div>

              <div class="flex justify-between items-center py-3 border-b border-white/10">
                <span class="text-white/60">Table</span>
                <span class="text-2xl font-bold text-poker-400">{{ checkInResult.table }}</span>
              </div>

              <div class="flex justify-between items-center py-3">
                <span class="text-white/60">Seat</span>
                <span class="text-2xl font-bold text-accent-400">{{ checkInResult.seat }}</span>
              </div>
            </div>
          </div>

          <button
            @click="scanAnother"
            class="btn-primary w-full max-w-md mx-auto"
          >
            <RotateCcw :size="20" class="inline mr-2" />
            Scan Another
          </button>
        </div>
      </div>

      <!-- Manual Check-in Option -->
      <div class="glass-card p-6 mt-8">
        <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
          <Smartphone :size="24" class="text-poker-400" />
          Manual Check-In
        </h3>
        
        <form @submit.prevent="handleManualCheckIn" class="flex gap-4">
          <input
            v-model="manualId"
            type="text"
            placeholder="Enter Reservation ID"
            class="input-glass flex-1"
            :disabled="loading"
          />
          <button
            type="submit"
            class="btn-primary"
            :disabled="loading || !manualId"
          >
            <UserCheck :size="20" class="inline mr-2" />
            Check In
          </button>
        </form>
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
import { ref, onMounted, onUnmounted } from 'vue'
import { Html5Qrcode } from 'html5-qrcode'
import { useReservationStore } from '../stores/reservationStore'
import { useToastStore } from '../stores/toastStore'
import {
  Camera, CheckCircle, XCircle, RotateCcw, ArrowLeft, Info, X,
  Smartphone, UserCheck, ScanLine
} from 'lucide-vue-next'
import PageHeader from '../components/PageHeader.vue'

const reservationStore = useReservationStore()
const toastStore = useToastStore()

const isScanning = ref(false)
const scanned = ref(false)
const checkInResult = ref({})
const manualId = ref('')
const loading = ref(false)

let html5QrCode = null

onMounted(() => {
  html5QrCode = new Html5Qrcode("qr-reader")
})

onUnmounted(() => {
  if (isScanning.value && html5QrCode) {
    stopScanner()
  }
})

async function startScanner() {
  try {
    await html5QrCode.start(
      { facingMode: "environment" },
      {
        fps: 10,
        qrbox: { width: 250, height: 250 }
      },
      onScanSuccess,
      onScanError
    )
    isScanning.value = true
    toastStore.info('Scanner started')
  } catch (err) {
    console.error('Failed to start scanner:', err)
    toastStore.error('Failed to start camera. Please check permissions.')
  }
}

async function stopScanner() {
  try {
    await html5QrCode.stop()
    isScanning.value = false
  } catch (err) {
    console.error('Failed to stop scanner:', err)
  }
}

async function onScanSuccess(decodedText) {
  // Stop scanner
  await stopScanner()
  
  // Extract reservation ID from URL or use as-is
  let reservationId = decodedText
  
  try {
    const url = new URL(decodedText)
    const params = new URLSearchParams(url.search)
    reservationId = params.get('id') || reservationId
  } catch {
    // Not a URL, use as-is
  }
  
  // Process check-in
  await processCheckIn(reservationId)
}

function onScanError(error) {
  // Ignore scan errors (too frequent)
}

async function processCheckIn(reservationId) {
  loading.value = true
  
  try {
    const result = await reservationStore.checkIn(reservationId)
    checkInResult.value = result
    scanned.value = true
    
    if (result.success) {
      toastStore.success('Check-in successful!')
    } else {
      toastStore.error(result.message)
    }
  } catch (error) {
    checkInResult.value = {
      success: false,
      message: error.response?.data?.message || 'Check-in failed'
    }
    scanned.value = true
    toastStore.error('Check-in failed')
  } finally {
    loading.value = false
  }
}

async function handleManualCheckIn() {
  if (!manualId.value) return
  await processCheckIn(manualId.value)
  manualId.value = ''
}

function scanAnother() {
  scanned.value = false
  checkInResult.value = {}
  startScanner()
}
</script>

