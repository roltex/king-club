<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="card p-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50">
            <UserPlus :size="32" class="text-white" />
          </div>
          <h1 class="text-3xl font-black text-white mb-2">Create Account</h1>
          <p class="text-slate-400">Join and start playing tournaments</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <!-- Name -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="first_name" class="block text-sm font-semibold text-white mb-2">
                First Name
              </label>
              <input
                id="first_name"
                v-model="form.first_name"
                type="text"
                required
                placeholder="John"
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
                placeholder="Doe"
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
                placeholder="your@email.com"
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
                required
                placeholder="+995 555 123 456"
                class="input pl-10 w-full"
                :disabled="isSubmitting"
              />
            </div>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-white mb-2">
              Password
            </label>
            <div class="relative">
              <Lock :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Create a strong password"
                class="input pl-10 pr-10 w-full"
                :disabled="isSubmitting"
                minlength="6"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300"
              >
                <Eye v-if="!showPassword" :size="20" />
                <EyeOff v-else :size="20" />
              </button>
            </div>
            <p class="text-slate-500 text-xs mt-1">Minimum 6 characters</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-white mb-2">
              Confirm Password
            </label>
            <div class="relative">
              <Lock :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="Re-enter your password"
                class="input pl-10 w-full"
                :disabled="isSubmitting"
              />
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="p-4 bg-red-900/20 border border-red-800 rounded-lg">
            <div class="flex items-center gap-2 text-red-400 text-sm">
              <AlertCircle :size="18" />
              <span>{{ errorMessage }}</span>
            </div>
          </div>

          <!-- Terms -->
          <label class="flex items-start gap-3 cursor-pointer">
            <input
              v-model="form.agreeToTerms"
              type="checkbox"
              required
              class="mt-1 w-5 h-5 rounded bg-slate-800 border-slate-700 text-emerald-600 focus:ring-emerald-600 focus:ring-offset-slate-900"
            />
            <span class="text-slate-300 text-sm">
              I agree to the Terms of Service and Privacy Policy
            </span>
          </label>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isSubmitting || !form.agreeToTerms"
            class="w-full btn-primary py-3 text-lg font-semibold flex items-center justify-center gap-2"
            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || !form.agreeToTerms }"
          >
            <Loader v-if="isSubmitting" :size="20" class="animate-spin" />
            <UserPlus v-else :size="20" />
            <span>{{ isSubmitting ? 'Creating Account...' : 'Create Account' }}</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Login Link -->
        <div class="text-center">
          <p class="text-slate-400 text-sm">
            Already have an account?
            <router-link to="/login" class="text-emerald-400 hover:text-emerald-300 font-semibold ml-1">
              Login here
            </router-link>
          </p>
        </div>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-6">
        <router-link to="/" class="inline-flex items-center gap-2 text-slate-400 hover:text-slate-300 text-sm transition-colors">
          <ArrowLeft :size="16" />
          <span>Back to Home</span>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { 
  UserPlus, Mail, Phone, Lock, Eye, EyeOff, AlertCircle, Loader, ArrowLeft 
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  agreeToTerms: false
})

const showPassword = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')

const handleSubmit = async () => {
  if (isSubmitting.value) return

  // Validate passwords match
  if (form.value.password !== form.value.password_confirmation) {
    errorMessage.value = 'Passwords do not match'
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await authStore.register({
      first_name: form.value.first_name,
      last_name: form.value.last_name,
      email: form.value.email,
      phone: form.value.phone,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation
    })
    
    router.push('/')
  } catch (error) {
    console.error('Registration failed:', error)
    
    // Show detailed validation errors
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorList = Object.values(errors).flat()
      errorMessage.value = errorList.join(', ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Registration failed. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
