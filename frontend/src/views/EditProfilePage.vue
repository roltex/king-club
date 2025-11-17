<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <router-link to="/profile" class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 mb-4 transition-colors">
          <ArrowLeft :size="20" />
          <span>Back to Profile</span>
        </router-link>
        <h1 class="section-title mb-2">Edit Profile</h1>
        <p class="text-slate-400 text-lg">Update your personal information</p>
      </div>
    </div>

    <div class="page-container py-8">
      <div class="max-w-2xl mx-auto">
        <div class="card p-8">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="first_name" class="block text-sm font-semibold text-white mb-2">
                  First Name
                </label>
                <input
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  required
                  class="input w-full"
                  :disabled="isSubmitting"
                />
              </div>
              <div>
                <label for="last_name" class="block text-sm font-semibold text-white mb-2">
                  Last Name
                </label>
                <input
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  required
                  class="input w-full"
                  :disabled="isSubmitting"
                />
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-semibold text-white mb-2">
                Email Address
              </label>
              <div class="relative">
                <Mail :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="input pl-10 w-full"
                  :disabled="isSubmitting"
                />
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="block text-sm font-semibold text-white mb-2">
                Phone Number
              </label>
              <div class="relative">
                <Phone :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="input pl-10 w-full"
                  :disabled="isSubmitting"
                />
              </div>
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="p-4 bg-emerald-900/20 border border-emerald-800 rounded-lg">
              <div class="flex items-center gap-2 text-emerald-400">
                <CheckCircle :size="20" />
                <span>{{ successMessage }}</span>
              </div>
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="p-4 bg-red-900/20 border border-red-800 rounded-lg">
              <div class="flex items-center gap-2 text-red-400">
                <AlertCircle :size="20" />
                <span>{{ errorMessage }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-4 pt-4">
              <button
                type="submit"
                :disabled="isSubmitting"
                class="flex-1 btn-primary py-3 flex items-center justify-center gap-2"
                :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
              >
                <Loader v-if="isSubmitting" :size="20" class="animate-spin" />
                <Save v-else :size="20" />
                <span>{{ isSubmitting ? 'Saving...' : 'Save Changes' }}</span>
              </button>
              <router-link to="/profile" class="btn-secondary px-8 py-3 flex items-center justify-center">
                Cancel
              </router-link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import {
  ArrowLeft, Mail, Phone, CheckCircle, AlertCircle, Loader, Save
} from 'lucide-vue-next'
import axios from 'axios'

const authStore = useAuthStore()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: ''
})

const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const handleSubmit = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    const response = await axios.put('/player/profile', form.value)
    
    // Update local auth store
    authStore.user = response.data.player

    successMessage.value = 'Profile updated successfully!'

    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Failed to update profile:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to update profile'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  // Pre-fill form with current user data
  if (authStore.user) {
    form.value = {
      first_name: authStore.user.first_name || '',
      last_name: authStore.user.last_name || '',
      email: authStore.user.email || '',
      phone: authStore.user.phone || ''
    }
  }
})
</script>
