<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <router-link to="/profile" class="inline-flex items-center gap-2 text-emerald-400 hover:text-emerald-300 mb-4 transition-colors">
          <ArrowLeft :size="20" />
          <span>Back to Profile</span>
        </router-link>
        <h1 class="section-title mb-2">Change Password</h1>
        <p class="text-slate-400 text-lg">Update your account password</p>
      </div>
    </div>

    <div class="page-container py-8">
      <div class="max-w-2xl mx-auto">
        <div class="card p-8">
          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Current Password -->
            <div>
              <label for="current_password" class="block text-sm font-semibold text-white mb-2">
                Current Password
              </label>
              <div class="relative">
                <Lock :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                <input
                  id="current_password"
                  v-model="form.current_password"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  required
                  placeholder="Enter current password"
                  class="input pl-10 pr-10 w-full"
                  :disabled="isSubmitting"
                />
                <button
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300"
                >
                  <Eye v-if="!showCurrentPassword" :size="20" />
                  <EyeOff v-else :size="20" />
                </button>
              </div>
            </div>

            <div class="divider"></div>

            <!-- New Password -->
            <div>
              <label for="new_password" class="block text-sm font-semibold text-white mb-2">
                New Password
              </label>
              <div class="relative">
                <Lock :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                <input
                  id="new_password"
                  v-model="form.new_password"
                  :type="showNewPassword ? 'text' : 'password'"
                  required
                  placeholder="Enter new password"
                  class="input pl-10 pr-10 w-full"
                  :disabled="isSubmitting"
                  minlength="6"
                />
                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300"
                >
                  <Eye v-if="!showNewPassword" :size="20" />
                  <EyeOff v-else :size="20" />
                </button>
              </div>
              <p class="text-slate-500 text-xs mt-1">Minimum 6 characters</p>
            </div>

            <!-- Confirm New Password -->
            <div>
              <label for="new_password_confirmation" class="block text-sm font-semibold text-white mb-2">
                Confirm New Password
              </label>
              <div class="relative">
                <Lock :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
                <input
                  id="new_password_confirmation"
                  v-model="form.new_password_confirmation"
                  :type="showNewPassword ? 'text' : 'password'"
                  required
                  placeholder="Re-enter new password"
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
                <Lock v-else :size="20" />
                <span>{{ isSubmitting ? 'Updating...' : 'Update Password' }}</span>
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  ArrowLeft, Lock, Eye, EyeOff, CheckCircle, AlertCircle, Loader
} from 'lucide-vue-next'
import axios from 'axios'

const router = useRouter()

const form = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const handleSubmit = async () => {
  if (isSubmitting.value) return

  // Validate passwords match
  if (form.value.new_password !== form.value.new_password_confirmation) {
    errorMessage.value = 'New passwords do not match'
    return
  }

  isSubmitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  try {
    await axios.post('/player/change-password', {
      current_password: form.value.current_password,
      new_password: form.value.new_password,
      new_password_confirmation: form.value.new_password_confirmation
    })

    successMessage.value = 'Password updated successfully! Redirecting...'

    // Reset form
    form.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    }

    setTimeout(() => {
      router.push('/profile')
    }, 2000)
  } catch (error) {
    console.error('Failed to change password:', error)
    errorMessage.value = error.response?.data?.message || 'Failed to change password'
  } finally {
    isSubmitting.value = false
  }
}
</script>
