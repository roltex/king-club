<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
          <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-2xl">
            <User :size="48" class="text-white" />
          </div>
          <div class="flex-1">
            <h1 class="text-4xl font-black text-white mb-2">{{ authStore.fullName }}</h1>
            <p class="text-slate-400 mb-4">{{ authStore.user?.email }}</p>
            <div class="flex flex-wrap gap-3">
              <router-link to="/profile/edit" class="btn-primary px-5 py-2 text-sm flex items-center gap-2">
                <Edit :size="16" />
                Edit Profile
              </router-link>
              <router-link to="/profile/change-password" class="btn-secondary px-5 py-2 text-sm flex items-center gap-2">
                <Lock :size="16" />
                Change Password
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="page-container py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Profile Info -->
          <div class="card p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
              <Info :size="28" class="text-emerald-400" />
              Profile Information
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <div class="text-slate-400 text-sm mb-1">First Name</div>
                <div class="text-white font-semibold text-lg">{{ authStore.user?.first_name }}</div>
              </div>
              <div>
                <div class="text-slate-400 text-sm mb-1">Last Name</div>
                <div class="text-white font-semibold text-lg">{{ authStore.user?.last_name }}</div>
              </div>
              <div>
                <div class="text-slate-400 text-sm mb-1">Email</div>
                <div class="text-white font-semibold">{{ authStore.user?.email }}</div>
              </div>
              <div>
                <div class="text-slate-400 text-sm mb-1">Phone</div>
                <div class="text-white font-semibold">{{ authStore.user?.phone || 'Not provided' }}</div>
              </div>
              <div>
                <div class="text-slate-400 text-sm mb-1">Member Since</div>
                <div class="text-white font-semibold">{{ formatDate(authStore.user?.created_at) }}</div>
              </div>
              <div>
                <div class="text-slate-400 text-sm mb-1">Status</div>
                <span class="badge badge-success">Active</span>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="card p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
              <History :size="28" class="text-emerald-400" />
              Recent Activity
            </h2>

            <div v-if="recentActivity.length > 0" class="space-y-4">
              <div
                v-for="activity in recentActivity"
                :key="activity.id"
                class="flex items-start gap-4 p-4 bg-slate-800 rounded-lg hover:bg-slate-700 transition-colors"
              >
                <div class="w-10 h-10 rounded-lg bg-emerald-900/50 flex items-center justify-center flex-shrink-0">
                  <Trophy :size="20" class="text-emerald-400" />
                </div>
                <div class="flex-1">
                  <p class="text-white font-semibold">{{ activity.title }}</p>
                  <p class="text-slate-400 text-sm">{{ activity.description }}</p>
                  <p class="text-slate-500 text-xs mt-1">{{ activity.date }}</p>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8">
              <History :size="48" class="text-slate-700 mx-auto mb-3" />
              <p class="text-slate-400">No recent activity</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Stats -->
          <div class="card p-6">
            <h3 class="text-lg font-bold text-white mb-4">Tournament Stats</h3>
            <div class="space-y-4">
              <div class="stat-card p-4">
                <div class="text-3xl font-black text-emerald-400 mb-1">{{ stats.registered }}</div>
                <div class="text-slate-400 text-sm">Registered</div>
              </div>
              <div class="stat-card p-4">
                <div class="text-3xl font-black text-blue-400 mb-1">{{ stats.attended }}</div>
                <div class="text-slate-400 text-sm">Attended</div>
              </div>
              <div class="stat-card p-4">
                <div class="text-3xl font-black text-amber-400 mb-1">{{ stats.wins }}</div>
                <div class="text-slate-400 text-sm">Top Finishes</div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="card p-6">
            <h3 class="text-lg font-bold text-white mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <router-link to="/tournaments" class="btn-secondary w-full py-3 flex items-center justify-center gap-2">
                <Trophy :size="18" />
                Browse Tournaments
              </router-link>
              <router-link to="/my-tournaments" class="btn-secondary w-full py-3 flex items-center justify-center gap-2">
                <History :size="18" />
                My Tournaments
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import {
  User, Edit, Lock, Info, History, Trophy
} from 'lucide-vue-next'

const authStore = useAuthStore()

const stats = ref({
  registered: 0,
  attended: 0,
  wins: 0
})

const recentActivity = ref([])

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'long',
    year: 'numeric'
  })
}

onMounted(async () => {
  // TODO: Fetch actual stats and activity from API
  stats.value = {
    registered: 0,
    attended: 0,
    wins: 0
  }
  recentActivity.value = []
})
</script>
