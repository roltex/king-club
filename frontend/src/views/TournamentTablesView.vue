<template>
  <div class="min-h-screen bg-slate-950">
    <LoadingSpinner v-if="isLoading" class="py-20" />
    
    <div v-else-if="tournament" id="tournament-tables-view" class="bg-slate-950" style="padding: 0; margin: 0; min-height: 100vh; overflow: hidden; display: flex; flex-direction: column;">
      
      <!-- Header with Stats in One Line -->
      <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 0.75rem 1rem; color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.3); flex-shrink: 0; border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
          <!-- Left: Tournament Info -->
          <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-center; font-size: 1.5rem;">
              🏆
            </div>
            <div>
              <h1 style="margin: 0; font-size: 1rem; font-weight: 700; line-height: 1.2;">{{ tournament.name }}</h1>
              <div style="margin-top: 0.125rem; font-size: 0.7rem; opacity: 0.9; display: flex; gap: 0.75rem;">
                <span>📅 {{ formatDate(tournament.start_date) }}</span>
                <span>💰 ₾{{ tournament.buy_in?.toLocaleString() }}</span>
              </div>
            </div>
          </div>
          
          <!-- Center: Stats -->
          <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center; backdrop-filter: blur(10px);">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ stats.total_seats }}</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Total</div>
            </div>
            <div style="background: rgba(6, 182, 212, 0.8); border: 1px solid rgba(6, 182, 212, 0.4); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ stats.available_seats }}</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Available</div>
            </div>
            <div style="background: rgba(59, 130, 246, 0.8); border: 1px solid rgba(59, 130, 246, 0.4); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ stats.registered }}</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Registered</div>
            </div>
            <div style="background: rgba(16, 185, 129, 0.8); border: 1px solid rgba(16, 185, 129, 0.4); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ stats.checked_in }}</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Checked In</div>
            </div>
            <div style="background: rgba(245, 158, 11, 0.8); border: 1px solid rgba(245, 158, 11, 0.4); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ stats.waiting_list }}</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Waiting</div>
            </div>
            <div style="background: rgba(236, 72, 153, 0.8); border: 1px solid rgba(236, 72, 153, 0.4); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 75px; text-align: center;">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">{{ stats.fill_percentage }}%</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Fill Rate</div>
            </div>
            <div style="background: rgba(251, 191, 36, 0.8); border: 1px solid rgba(251, 191, 36, 0.4); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; min-width: 95px; text-align: center;">
              <div style="font-size: 1.25rem; font-weight: 700; line-height: 1;">₾{{ (tournament.guaranteed_prize || 0).toLocaleString() }}</div>
              <div style="font-size: 0.625rem; opacity: 0.9; margin-top: 0.0625rem;">Guaranteed</div>
            </div>
          </div>
          
          <!-- Right: Prize Pool, Fullscreen & Back Button -->
          <div style="display: flex; gap: 0.5rem; align-items: center;">
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); padding: 0.375rem 0.75rem; border-radius: 6px; text-align: center; min-width: 80px; backdrop-filter: blur(10px);">
              <div style="font-size: 0.625rem; opacity: 0.9; margin-bottom: 0.0625rem;">Prize Pool</div>
              <div style="font-size: 1rem; font-weight: 700; line-height: 1;">₾{{ (tournament.actual_prize_pool || 0).toLocaleString() }}</div>
            </div>
            <button 
              @click="toggleFullscreen"
              id="fullscreen-btn"
              style="background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(16, 185, 129, 0.2); color: white; padding: 0.375rem 0.75rem; border-radius: 6px; cursor: pointer; font-size: 1.25rem; backdrop-filter: blur(10px); transition: all 0.2s; height: 100%; display: flex; align-items: center; justify-content: center;"
              @mouseover="$event.target.style.background='rgba(15, 23, 42, 0.8)'; $event.target.style.transform='scale(1.05)'" 
              @mouseout="$event.target.style.background='rgba(15, 23, 42, 0.6)'; $event.target.style.transform='scale(1)'"
            >
              <span id="fullscreen-icon">⛶</span>
            </button>
            <router-link 
              :to="`/tournament/${tournamentId}`"
              class="btn-secondary"
              style="padding: 0.375rem 0.75rem; font-size: 0.875rem; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;"
            >
              <ArrowLeft :size="16" />
              Back
            </router-link>
          </div>
        </div>
      </div>

      <!-- Tables & Waiting List Container -->
      <div style="flex: 1; display: flex; gap: 1rem; padding: 1rem; min-height: 0; overflow-y: auto; background: #0f172a;">
        <!-- Tables Section -->
        <div style="flex: 1; display: flex; flex-direction: column; min-height: 0;">
          <div id="tables-grid" :style="tablesGridStyle">
            <div 
              v-for="table in tables" 
              :key="table.table_number"
              class="table-card card" 
              style="overflow: hidden;"
            >
              
              <!-- Table Header -->
              <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 0.875rem 1rem; color: white; border-bottom: 1px solid rgba(16, 185, 129, 0.2);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                  <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; line-height: 1;">
                      {{ table.table_number }}
                    </div>
                    <div>
                      <div style="font-size: 1rem; font-weight: 700; line-height: 1.2;">Table {{ table.table_number }}</div>
                      <div style="font-size: 0.75rem; opacity: 0.9; margin-top: 0.125rem;">
                        {{ getCheckedInCount(table) }}/{{ table.total_seats }} Active
                      </div>
                    </div>
                  </div>
                  <div :style="getTableStatusStyle(table)">
                    {{ table.occupied_count }}/{{ table.total_seats }}
                  </div>
                </div>
              </div>

              <!-- Poker Table -->
              <div style="padding: 1rem; background: #1e293b; flex: 1; display: flex; flex-direction: column; min-height: 0;">
                <div style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); border-radius: 16px; padding: 1rem; box-shadow: inset 0 2px 8px rgba(0,0,0,0.4), 0 4px 12px rgba(0,0,0,0.3); border: 1px solid rgba(16, 185, 129, 0.2); flex: 1; display: flex; flex-direction: column;">
                  
                  <!-- Seats Grid -->
                  <div class="seats-grid">
                    <div 
                      v-for="seat in table.seats" 
                      :key="seat.seat_number"
                      class="seat-item"
                    >
                      <template v-if="seat.occupied">
                        <!-- Occupied Seat -->
                        <div :style="getSeatStyle(seat)">
                          <div style="width: 48px; height: 48px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1f2937; font-size: 1.5rem; line-height: 1; margin: 0 auto 0.5rem auto;">
                            {{ seat.seat_number }}
                          </div>
                          <div style="color: white; font-size: 0.875rem; font-weight: 600; line-height: 1.2; margin-bottom: 0.375rem; width: 100%; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                            {{ seat.player_name || 'Unknown' }}
                          </div>
                          <div :style="getStatusBadgeStyle(seat)">
                            {{ getStatusText(seat) }}
                          </div>
                        </div>
                      </template>
                      <template v-else>
                        <!-- Empty Seat -->
                        <div style="background: rgba(0,0,0,0.08); border: 2px dashed rgba(255,255,255,0.3); border-radius: 12px; width: 100%; padding: 1rem; display: flex; flex-direction: column; align-items: center; justify-center; color: rgba(255,255,255,0.5);">
                          <div style="font-size: 2rem; line-height: 1;">+</div>
                          <div style="font-size: 0.75rem; margin-top: 0.25rem; font-weight: 600;">Seat {{ seat.seat_number }}</div>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Table Footer -->
              <div style="background: #1e293b; padding: 0.75rem 1rem; border-top: 1px solid #334155; display: flex; align-items: center; justify-content: space-between; font-size: 0.875rem;">
                <div style="display: flex; gap: 1rem;">
                  <div style="display: flex; align-items: center; gap: 0.375rem;">
                    <div style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 4px rgba(16, 185, 129, 0.5);"></div>
                    <span style="color: #cbd5e1;"><strong style="color: #f1f5f9;">{{ getCheckedInCount(table) }}</strong> Active</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 0.375rem;">
                    <div style="width: 10px; height: 10px; background: #3b82f6; border-radius: 50%; box-shadow: 0 0 4px rgba(59, 130, 246, 0.5);"></div>
                    <span style="color: #cbd5e1;"><strong style="color: #f1f5f9;">{{ getRegisteredCount(table) }}</strong> Waiting</span>
                  </div>
                  <div style="display: flex; align-items: center; gap: 0.375rem;">
                    <div style="width: 10px; height: 10px; background: #64748b; border-radius: 50%;"></div>
                    <span style="color: #cbd5e1;"><strong style="color: #f1f5f9;">{{ getEmptyCount(table) }}</strong> Empty</span>
                  </div>
                </div>
                <div style="color: #10b981; font-weight: 700; font-size: 1rem;">
                  {{ Math.round((table.occupied_count / table.total_seats) * 100) }}%
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Waiting List Column -->
        <div style="width: 280px; display: flex; flex-direction: column; min-height: 0;">
          <div class="card" style="overflow: hidden; display: flex; flex-direction: column; height: 100%;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); padding: 1rem; color: white; border-bottom: 1px solid rgba(245, 158, 11, 0.2);">
              <h3 style="font-size: 1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 1.25rem;">⏱</span>
                <span>Waiting List</span>
                <span style="background: rgba(255,255,255,0.3); padding: 0.125rem 0.5rem; border-radius: 12px; font-size: 0.75rem; margin-left: auto;">{{ waitingList.length }}</span>
              </h3>
            </div>
            
            <!-- Waiting List Content -->
            <div style="flex: 1; overflow-y: auto; padding: 0.75rem; background: #1e293b;">
              <template v-if="waitingList.length > 0">
                <div 
                  v-for="(player, index) in waitingList" 
                  :key="player.id || index"
                  style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; padding: 0.75rem; margin-bottom: 0.5rem; transition: all 0.2s;"
                  onmouseover="this.style.background='rgba(245, 158, 11, 0.15)'; this.style.borderColor='rgba(245, 158, 11, 0.4)'"
                  onmouseout="this.style.background='rgba(245, 158, 11, 0.1)'; this.style.borderColor='rgba(245, 158, 11, 0.3)'"
                >
                  <div style="display: flex; align-items: start; gap: 0.75rem;">
                    <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.875rem; flex-shrink: 0; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);">
                      {{ player.position || index + 1 }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                      <div style="font-weight: 600; font-size: 0.875rem; color: #f1f5f9; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ player.player_name || player.name || 'Unknown' }}
                      </div>
                    </div>
                  </div>
                </div>
              </template>
              <div v-else style="text-align: center; padding: 2rem; color: #64748b; font-size: 0.875rem;">
                  No players waiting
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import LoadingSpinner from '../components/LoadingSpinner.vue'
import { ArrowLeft } from 'lucide-vue-next'

const route = useRoute()
const tournamentId = route.params.id

const isLoading = ref(true)
const tournament = ref(null)
const tables = ref([])
const waitingList = ref([])
const stats = ref({
  total_seats: 0,
  available_seats: 0,
  registered: 0,
  checked_in: 0,
  waiting_list: 0,
  fill_percentage: 0
})

const tablesGridStyle = computed(() => {
  const tableCount = tables.value.length
  const maxCols = Math.min(tableCount, 3)
  return {
    display: 'grid',
    gap: '1rem',
    height: '100%',
    alignContent: 'start',
    gridTemplateColumns: `repeat(${maxCols}, 1fr)`
  }
})

const fetchTournamentData = async () => {
  try {
    isLoading.value = true
    
    // Fetch tournament details
    const tournamentRes = await axios.get(`/tournaments/${tournamentId}`)
    tournament.value = tournamentRes.data
    
    // Fetch tables layout
    const tablesRes = await axios.get(`/tournaments/${tournamentId}/tables`)
    tables.value = tablesRes.data.tables.map(table => ({
      ...table,
      total_seats: tournament.value.seats_per_table || table.seats?.length || 0
    }))
    
    // Fetch waiting list
    try {
      const waitingRes = await axios.get(`/tournaments/${tournamentId}/waiting-list`)
      waitingList.value = waitingRes.data.waiting_list || []
    } catch (e) {
      waitingList.value = []
    }
    
    // Calculate stats
    const totalSeats = tournament.value.total_seats
    const registered = tables.value.reduce((sum, t) => sum + t.occupied_count, 0)
    const checkedIn = tables.value.reduce((sum, t) => 
      sum + t.seats.filter(s => s.occupied && s.status === 'checked_in').length, 0
    )
    const available = Math.max(0, totalSeats - registered)
    const fillPercentage = totalSeats > 0 ? Math.round((registered / totalSeats) * 100) : 0
    
    stats.value = {
      total_seats: totalSeats,
      available_seats: available,
      registered: registered,
      checked_in: checkedIn,
      waiting_list: waitingList.value.length,
      fill_percentage: fillPercentage
    }
  } catch (error) {
    console.error('Error fetching tournament data:', error)
  } finally {
    isLoading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getCheckedInCount = (table) => {
  return table.seats.filter(s => s.occupied && s.status === 'checked_in').length
}

const getRegisteredCount = (table) => {
  return table.seats.filter(s => s.occupied && s.status === 'registered').length
}

const getEmptyCount = (table) => {
  return table.seats.filter(s => !s.occupied).length
}

const getTableStatusStyle = (table) => {
  const isFull = table.occupied_count >= table.total_seats
  const isAlmostFull = table.occupied_count >= table.total_seats * 0.8
  const bgColor = isFull ? '#ef4444' : (isAlmostFull ? '#f59e0b' : '#10b981')
  return {
    background: bgColor,
    padding: '0.5rem 0.875rem',
    borderRadius: '6px',
    fontWeight: '700',
    fontSize: '1rem',
    lineHeight: '1'
  }
}

const getSeatStyle = (seat) => {
  let gradient = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)'
  
  if (seat.status === 'checked_in') {
    gradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
  } else if (seat.status === 'cancelled') {
    gradient = 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'
  }
  
  return {
    background: gradient,
    padding: '0.5rem',
    borderRadius: '12px',
    textAlign: 'center',
    width: '100%',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    boxShadow: '0 2px 8px rgba(0,0,0,0.2)',
    position: 'relative'
  }
}

const getStatusBadgeStyle = (seat) => {
  let bgColor = '#1d4ed8'
  
  if (seat.status === 'checked_in') {
    bgColor = '#047857'
  } else if (seat.status === 'cancelled') {
    bgColor = '#b91c1c'
  }
  
  return {
    background: bgColor,
    color: 'white',
    fontSize: '0.75rem',
    padding: '0.25rem 0.5rem',
    borderRadius: '4px',
    fontWeight: '700'
  }
}

const getStatusText = (seat) => {
  if (seat.status === 'checked_in') {
    return '✓ Checked In'
  } else if (seat.status === 'cancelled') {
    return '✕ Cancelled'
  }
  return 'Registered'
}

const toggleFullscreen = () => {
  const elem = document.getElementById('tournament-tables-view')
  const icon = document.getElementById('fullscreen-icon')
  
  if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
    // Enter fullscreen
    if (elem.requestFullscreen) {
      elem.requestFullscreen()
    } else if (elem.webkitRequestFullscreen) {
      elem.webkitRequestFullscreen()
    } else if (elem.mozRequestFullScreen) {
      elem.mozRequestFullScreen()
    } else if (elem.msRequestFullscreen) {
      elem.msRequestFullscreen()
    }
  } else {
    // Exit fullscreen
    if (document.exitFullscreen) {
      document.exitFullscreen()
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen()
    } else if (document.mozCancelFullScreen) {
      document.mozCancelFullScreen()
    } else if (document.msExitFullscreen) {
      document.msExitFullscreen()
    }
  }
}

const updateFullscreenIcon = () => {
  const icon = document.getElementById('fullscreen-icon')
  if (icon) {
    if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
      icon.textContent = '⛶'
    } else {
      icon.textContent = '⛶'
    }
  }
}

onMounted(() => {
  fetchTournamentData()
  
  // Listen for fullscreen change events
  document.addEventListener('fullscreenchange', updateFullscreenIcon)
  document.addEventListener('webkitfullscreenchange', updateFullscreenIcon)
  document.addEventListener('mozfullscreenchange', updateFullscreenIcon)
  document.addEventListener('MSFullscreenChange', updateFullscreenIcon)
})

onUnmounted(() => {
  // Clean up event listeners
  document.removeEventListener('fullscreenchange', updateFullscreenIcon)
  document.removeEventListener('webkitfullscreenchange', updateFullscreenIcon)
  document.removeEventListener('mozfullscreenchange', updateFullscreenIcon)
  document.removeEventListener('MSFullscreenChange', updateFullscreenIcon)
})
</script>

<style scoped>
.seats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.75rem;
  height: 100%;
}

.seat-item {
  min-height: 0;
  display: flex;
}

.table-card {
  display: flex;
  flex-direction: column;
  min-height: 0;
}
</style>

