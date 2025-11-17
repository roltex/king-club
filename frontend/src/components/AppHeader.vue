<template>
  <header class="sticky top-0 z-50 bg-slate-900/95 backdrop-blur-custom border-b border-slate-800 shadow-xl">
    <nav class="page-container py-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <router-link 
          to="/" 
          class="flex items-center gap-3 hover:opacity-80 transition-opacity"
        >
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-lg">
            <Spade :size="24" class="text-white" />
          </div>
          <div>
            <h1 class="text-xl font-black text-white">Kings Club</h1>
            <p class="text-xs text-slate-400">Play & Win</p>
          </div>
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-6">
          <!-- Nav Links -->
          <router-link
            v-for="link in navLinks"
            :key="link.path"
            :to="link.path"
            class="nav-link"
            :class="{ 'nav-link-active': isActive(link.path) }"
          >
            <component :is="link.icon" :size="18" />
            <span>{{ link.label }}</span>
          </router-link>

          <!-- Auth State -->
          <div v-if="!authStore.isLoggedIn" class="flex items-center gap-3 ml-4 pl-4 border-l border-slate-700">
            <router-link to="/login" class="text-slate-300 hover:text-white font-medium transition-colors">
              Login
            </router-link>
            <router-link to="/register" class="btn-primary px-4 py-2 text-sm">
              Sign Up
            </router-link>
          </div>

          <!-- User Dropdown -->
          <div v-else class="relative ml-4 pl-4 border-l border-slate-700">
            <button
              @click="userMenuOpen = !userMenuOpen"
              class="flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 transition-colors"
            >
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center">
                <User :size="18" class="text-white" />
              </div>
              <span class="text-white font-medium">{{ authStore.fullName || 'Player' }}</span>
              <ChevronDown :size="16" class="text-slate-400" :class="{ 'rotate-180': userMenuOpen }" />
            </button>

            <!-- Dropdown Menu -->
            <transition name="dropdown">
              <div
                v-if="userMenuOpen"
                @click="userMenuOpen = false"
                class="absolute right-0 mt-2 w-56 card border-slate-700 overflow-hidden shadow-2xl"
              >
                <router-link
                  to="/profile"
                  class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 transition-colors text-slate-300 hover:text-white"
                >
                  <User :size="18" />
                  <span>My Profile</span>
                </router-link>
                <router-link
                  to="/my-tournaments"
                  class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 transition-colors text-slate-300 hover:text-white"
                >
                  <History :size="18" />
                  <span>My Tournaments</span>
                </router-link>
                <router-link
                  to="/profile/edit"
                  class="flex items-center gap-3 px-4 py-3 hover:bg-slate-800 transition-colors text-slate-300 hover:text-white"
                >
                  <Settings :size="18" />
                  <span>Settings</span>
                </router-link>
                <div class="border-t border-slate-800"></div>
                <button
                  @click="handleLogout"
                  class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-900/20 transition-colors text-red-400 hover:text-red-300"
                >
                  <LogOut :size="18" />
                  <span>Logout</span>
                </button>
              </div>
            </transition>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden p-2 rounded-lg bg-slate-800 hover:bg-slate-700 transition-colors"
        >
          <Menu v-if="!mobileMenuOpen" :size="24" class="text-white" />
          <X v-else :size="24" class="text-white" />
        </button>
      </div>

      <!-- Mobile Navigation -->
      <transition name="slide-down">
        <div v-if="mobileMenuOpen" class="md:hidden mt-4 space-y-2 pb-4">
          <!-- Nav Links -->
          <router-link
            v-for="link in navLinks"
            :key="link.path"
            :to="link.path"
            @click="mobileMenuOpen = false"
            class="nav-link-mobile"
            :class="{ 'nav-link-mobile-active': isActive(link.path) }"
          >
            <component :is="link.icon" :size="20" />
            <span>{{ link.label }}</span>
          </router-link>

          <!-- Auth Links (Not Logged In) -->
          <template v-if="!authStore.isLoggedIn">
            <div class="border-t border-slate-800 my-2"></div>
            <router-link
              to="/login"
              @click="mobileMenuOpen = false"
              class="nav-link-mobile"
            >
              <LogIn :size="20" />
              <span>Login</span>
            </router-link>
            <router-link
              to="/register"
              @click="mobileMenuOpen = false"
              class="nav-link-mobile bg-gradient-to-r from-emerald-600 to-emerald-700 text-white border-emerald-600"
            >
              <UserPlus :size="20" />
              <span>Sign Up</span>
            </router-link>
          </template>

          <!-- User Links (Logged In) -->
          <template v-else>
            <div class="border-t border-slate-800 my-2"></div>
            <div class="px-4 py-3 text-slate-400 text-sm">
              Logged in as <span class="text-white font-semibold">{{ authStore.fullName }}</span>
            </div>
            <router-link
              to="/profile"
              @click="mobileMenuOpen = false"
              class="nav-link-mobile"
            >
              <User :size="20" />
              <span>My Profile</span>
            </router-link>
            <router-link
              to="/my-tournaments"
              @click="mobileMenuOpen = false"
              class="nav-link-mobile"
            >
              <History :size="20" />
              <span>My Tournaments</span>
            </router-link>
            <router-link
              to="/profile/edit"
              @click="mobileMenuOpen = false"
              class="nav-link-mobile"
            >
              <Settings :size="20" />
              <span>Settings</span>
            </router-link>
            <button
              @click="handleLogout(); mobileMenuOpen = false"
              class="nav-link-mobile text-red-400 hover:bg-red-900/20 w-full"
            >
              <LogOut :size="20" />
              <span>Logout</span>
            </button>
          </template>
        </div>
      </transition>
    </nav>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { 
  Home, Trophy, User, LogIn, UserPlus, LogOut, Settings, History, 
  Menu, X, Spade, ChevronDown 
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const mobileMenuOpen = ref(false)
const userMenuOpen = ref(false)

const navLinks = computed(() => [
  { path: '/', label: 'Home', icon: Home },
  { path: '/tournaments', label: 'Tournaments', icon: Trophy },
])

const isActive = (path) => {
  if (path === '/') {
    return route.path === '/'
  }
  return route.path.startsWith(path)
}

const handleLogout = async () => {
  await authStore.logout()
  userMenuOpen.value = false
  router.push('/')
}
</script>

<style scoped>
.nav-link {
  @apply flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200;
  @apply text-slate-300 hover:text-white hover:bg-slate-800;
}

.nav-link-active {
  @apply text-white bg-emerald-600 shadow-lg shadow-emerald-900/50;
}

.nav-link-mobile {
  @apply flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200;
  @apply text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 border border-slate-700;
}

.nav-link-mobile-active {
  @apply text-white bg-emerald-600 border-emerald-600;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}
</style>
