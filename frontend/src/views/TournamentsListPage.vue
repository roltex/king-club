<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Page Header -->
    <div class="bg-gradient-to-br from-slate-900 to-emerald-950 border-b border-slate-800">
      <div class="page-container py-12">
        <h1 class="section-title mb-2">Browse Tournaments</h1>
        <p class="text-slate-400 text-lg">Find your perfect poker tournament and register to compete</p>
      </div>
    </div>

    <div class="page-container py-8">
      <!-- Filters Card -->
      <div class="card p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div class="md:col-span-2">
            <div class="relative">
              <Search :size="20" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search tournaments..."
                class="input pl-10 w-full"
              />
            </div>
          </div>

          <!-- Type Filter -->
          <div>
            <select v-model="filters.type" class="input w-full">
              <option value="">All Types</option>
              <option value="freezeout">Freezeout</option>
              <option value="rebuy">Rebuy</option>
              <option value="bounty">Bounty</option>
              <option value="turbo">Turbo</option>
              <option value="deep_stack">Deep Stack</option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <select v-model="filters.status" class="input w-full">
              <option value="">All Status</option>
              <option value="open">Open</option>
              <option value="closing_soon">Closing Soon</option>
              <option value="full">Full</option>
            </select>
          </div>
        </div>

        <!-- Active Filters -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-slate-800">
          <span class="text-sm text-slate-400">Active filters:</span>
          <button
            v-if="filters.type"
            @click="filters.type = ''"
            class="badge bg-emerald-900/50 text-emerald-400 border-emerald-800 hover:bg-emerald-900 cursor-pointer"
          >
            {{ filters.type }}
            <X :size="14" />
          </button>
          <button
            v-if="filters.status"
            @click="filters.status = ''"
            class="badge bg-emerald-900/50 text-emerald-400 border-emerald-800 hover:bg-emerald-900 cursor-pointer"
          >
            {{ filters.status }}
            <X :size="14" />
          </button>
          <button
            @click="clearAllFilters"
            class="badge badge-error hover:bg-red-900 cursor-pointer"
          >
            Clear All
          </button>
        </div>
      </div>

      <!-- Results Header -->
      <div class="flex items-center justify-between mb-6">
        <p class="text-slate-400">
          Found <span class="text-white font-bold">{{ filteredTournaments.length }}</span> tournaments
        </p>
        <div class="flex items-center gap-2">
          <button
            @click="viewMode = 'grid'"
            class="p-2.5 rounded-lg transition-colors"
            :class="viewMode === 'grid' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
          >
            <LayoutGrid :size="20" />
          </button>
          <button
            @click="viewMode = 'list'"
            class="p-2.5 rounded-lg transition-colors"
            :class="viewMode === 'list' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-white'"
          >
            <List :size="20" />
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <SkeletonCard v-for="i in 6" :key="i" />
      </div>

      <!-- Tournaments Grid -->
      <div
        v-else-if="filteredTournaments.length > 0"
        class="grid gap-6"
        :class="{
          'grid-cols-1 md:grid-cols-2 lg:grid-cols-3': viewMode === 'grid',
          'grid-cols-1': viewMode === 'list'
        }"
      >
        <TournamentCard
          v-for="tournament in filteredTournaments"
          :key="tournament.id"
          :tournament="tournament"
        />
      </div>

      <!-- Empty State -->
      <div v-else class="card p-12 text-center">
        <Search :size="64" class="text-slate-700 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-white mb-2">No Tournaments Found</h3>
        <p class="text-slate-400 mb-6">
          Try adjusting your filters or check back later for new tournaments
        </p>
        <button @click="clearAllFilters" class="btn-primary px-6 py-3 inline-flex items-center gap-2">
          <RotateCcw :size="18" />
          Clear Filters
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useTournamentsStore } from '../stores/tournaments'
import TournamentCard from '../components/TournamentCard.vue'
import SkeletonCard from '../components/SkeletonCard.vue'
import {
  Search, LayoutGrid, List, X, RotateCcw
} from 'lucide-vue-next'

const tournamentsStore = useTournamentsStore()

const isLoading = ref(true)
const searchQuery = ref('')
const viewMode = ref('grid')

const filters = ref({
  type: '',
  status: ''
})

const hasActiveFilters = computed(() => {
  return filters.value.type || filters.value.status || searchQuery.value
})

const filteredTournaments = computed(() => {
  let results = tournamentsStore.tournaments

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    results = results.filter(t =>
      t.name.toLowerCase().includes(query) ||
      t.venue_name?.toLowerCase().includes(query) ||
      t.location?.toLowerCase().includes(query)
    )
  }

  if (filters.value.type) {
    results = results.filter(t => t.tournament_type === filters.value.type)
  }

  if (filters.value.status) {
    results = results.filter(t => t.registration_status === filters.value.status)
  }

  return results
})

const clearAllFilters = () => {
  filters.value = {
    type: '',
    status: ''
  }
  searchQuery.value = ''
}

onMounted(async () => {
  try {
    await tournamentsStore.fetchTournaments()
  } catch (error) {
    console.error('Failed to load tournaments:', error)
  } finally {
    isLoading.value = false
  }
})

watch([filters, searchQuery], () => {
  // Optional: Add analytics
}, { deep: true })
</script>
