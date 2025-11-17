<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
      <!-- Card -->
      <div class="card p-8">
        <!-- Header -->
        <div class="text-center mb-8">
          <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50">
            <LogIn :size="32" class="text-white" />
          </div>
          <h1 class="text-3xl font-black text-white mb-2">Welcome Back</h1>
          <p class="text-slate-400">Login to your account to continue</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleSubmit" class="space-y-5">
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
                placeholder="Enter your password"
                class="input pl-10 pr-10 w-full"
                :disabled="isSubmitting"
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
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="p-4 bg-red-900/20 border border-red-800 rounded-lg">
            <div class="flex items-center gap-2 text-red-400 text-sm">
              <AlertCircle :size="18" />
              <span>{{ errorMessage }}</span>
            </div>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full btn-primary py-3 text-lg font-semibold flex items-center justify-center gap-2"
            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
          >
            <Loader v-if="isSubmitting" :size="20" class="animate-spin" />
            <LogIn v-else :size="20" />
            <span>{{ isSubmitting ? 'Logging in...' : 'Login' }}</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Sign Up Link -->
        <div class="text-center">
          <p class="text-slate-400 text-sm">
            Don't have an account?
            <router-link to="/register" class="text-emerald-400 hover:text-emerald-300 font-semibold ml-1">
              Sign up now
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
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { 
  LogIn, Mail, Lock, Eye, EyeOff, AlertCircle, Loader, ArrowLeft 
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: ''
})

const showPassword = ref(false)
const isSubmitting = ref(false)
const errorMessage = ref('')

const handleSubmit = async () => {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errorMessage.value = ''

  try {
    await authStore.login(form.value.email, form.value.password)
    
    const redirect = route.query.redirect || '/'
    router.push(redirect)
  } catch (error) {
    console.error('Login failed:', error)
    
    // Show detailed errors
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      const errorList = Object.values(errors).flat()
      errorMessage.value = errorList.join(', ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Invalid email or password'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>
