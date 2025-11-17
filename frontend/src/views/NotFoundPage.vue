<template>
  <div class="min-h-screen bg-slate-950 flex items-center justify-center px-4">
    <div class="max-w-md w-full text-center">
      <div class="card p-12">
        <!-- 404 Icon -->
        <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-700 flex items-center justify-center mx-auto mb-6">
          <AlertCircle :size="48" class="text-slate-500" />
        </div>

        <!-- Error Code -->
        <h1 class="text-7xl font-black mb-4">
          <span class="gradient-text">404</span>
        </h1>

        <!-- Message -->
        <h2 class="text-3xl font-bold text-white mb-4">Page Not Found</h2>
        <p class="text-slate-400 mb-8">
          The page you're looking for doesn't exist or has been moved.
        </p>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link to="/" class="btn-primary px-6 py-3 flex items-center justify-center gap-2">
            <Home :size="20" />
            <span>Go Home</span>
          </router-link>
          <router-link to="/tournaments" class="btn-secondary px-6 py-3 flex items-center justify-center gap-2">
            <Trophy :size="20" />
            <span>Browse Tournaments</span>
          </router-link>
        </div>
      </div>

      <!-- Helpful Links -->
      <div class="mt-8 text-sm">
        <p class="text-slate-500 mb-3">Maybe you're looking for:</p>
        <div class="flex flex-wrap gap-3 justify-center">
          <router-link to="/" class="text-emerald-400 hover:text-emerald-300">
            Home
          </router-link>
          <span class="text-slate-700">•</span>
          <router-link to="/tournaments" class="text-emerald-400 hover:text-emerald-300">
            Tournaments
          </router-link>
          <span class="text-slate-700">•</span>
          <router-link v-if="authStore.isLoggedIn" to="/profile" class="text-emerald-400 hover:text-emerald-300">
            Profile
          </router-link>
          <router-link v-else to="/login" class="text-emerald-400 hover:text-emerald-300">
            Login
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import { AlertCircle, Home, Trophy } from 'lucide-vue-next'

const authStore = useAuthStore()
</script>
