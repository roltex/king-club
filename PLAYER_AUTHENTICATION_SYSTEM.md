# 🎮 Player Authentication System - COMPLETE!

## ✅ **SYSTEM OVERVIEW**

A complete user authentication system has been implemented! Now players must register and login before they can register for tournaments.

---

## 🏗️ **ARCHITECTURE**

### **Flow:**
1. **Player Registration** → Create account with email/password
2. **Player Login** → Get authentication token
3. **Tournament Registration** → Use token to register (auto seat assignment)
4. **Admin Manual Override** → Admins can change seats manually

---

## 📊 **DATABASE STRUCTURE**

### **New `players` Table:**
```sql
- id (UUID, Primary Key)
- first_name
- last_name
- phone (Unique)
- email (Unique)
- password (Hashed)
- date_of_birth
- city
- country (default: Georgia)
- profile_image
- is_active (default: true)
- email_verified (default: false)
- email_verified_at
- notes
- remember_token
- created_at
- updated_at
- deleted_at (Soft Deletes)
```

### **Updated `registrations` Table:**
```sql
- Added: player_id (UUID, Foreign Key to players.id)
- Keeps: first_name, last_name, phone, email (for history/redundancy)
```

---

## 🔐 **AUTHENTICATION FLOW**

### **1. Player Registration (New Account)**

**Endpoint:** `POST /api/player/register`

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "+995555123456",
  "email": "john.doe@example.com",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123",
  "date_of_birth": "1990-01-15",
  "city": "Tbilisi",
  "country": "Georgia"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Registration successful",
  "player": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+995555123456",
    "email": "john.doe@example.com",
    "city": "Tbilisi",
    "country": "Georgia"
  },
  "token": "1|abcdef123456..."
}
```

---

### **2. Player Login**

**Endpoint:** `POST /api/player/login`

**Request:**
```json
{
  "email": "john.doe@example.com",
  "password": "SecurePass123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "player": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+995555123456",
    "email": "john.doe@example.com",
    "city": "Tbilisi",
    "country": "Georgia"
  },
  "token": "2|xyz789456123..."
}
```

**Store this token!** Use it in the `Authorization` header for all protected endpoints.

---

## 🎰 **TOURNAMENT REGISTRATION (AUTHENTICATED)**

### **3. Register for Tournament**

**Endpoint:** `POST /api/register`
**Authentication:** **Required** (Bearer Token)

**Headers:**
```
Authorization: Bearer 2|xyz789456123...
Content-Type: application/json
```

**Request:**
```json
{
  "tournament_id": "01234567-89ab-cdef-0123-456789abcdef"
}
```

**Response (201 Created) - With Seat:**
```json
{
  "success": true,
  "status": "registered",
  "registration_id": "fedcba98-7654-3210-fedc-ba9876543210",
  "tournament": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "Friday Night Poker",
    "start_date": "2024-11-23T19:00:00+00:00",
    "venue_name": "Grand Casino Tbilisi"
  },
  "table": 5,
  "seat": 3,
  "qr_code": "http://localhost:5173/checkin?id=fedcba98-7654-3210-fedc-ba9876543210",
  "message": "Registration confirmed! You are registered for Friday Night Poker"
}
```

**Response (200 OK) - Waiting List:**
```json
{
  "success": true,
  "status": "waiting",
  "registration_id": "fedcba98-7654-3210-fedc-ba9876543210",
  "tournament": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "Friday Night Poker",
    "start_date": "2024-11-23T19:00:00+00:00",
    "venue_name": "Grand Casino Tbilisi"
  },
  "waiting_position": 5,
  "message": "Tournament is full. You have been added to the waiting list at position 5"
}
```

**How It Works:**
1. System automatically gets player info from auth token
2. Checks if tournament registration is open
3. Checks if player already registered
4. **Automatically assigns table & seat** (if auto-assignment enabled)
5. Generates QR code for check-in
6. Updates tournament prize pool

---

## 👤 **PLAYER PROFILE MANAGEMENT**

### **4. Get Player Profile**

**Endpoint:** `GET /api/player/profile`
**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "player": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "first_name": "John",
    "last_name": "Doe",
    "full_name": "John Doe",
    "phone": "+995555123456",
    "email": "john.doe@example.com",
    "date_of_birth": "1990-01-15",
    "city": "Tbilisi",
    "country": "Georgia",
    "profile_image": null,
    "email_verified": false,
    "statistics": {
      "total_registrations": 12,
      "tournaments_played": 10,
      "cancelled": 1,
      "waiting_list": 1
    },
    "created_at": "2024-01-15T10:30:00+00:00"
  }
}
```

---

### **5. Update Player Profile**

**Endpoint:** `PUT /api/player/profile`
**Authentication:** Required

**Request:**
```json
{
  "first_name": "John",
  "last_name": "Smith",
  "phone": "+995555999888",
  "city": "Batumi"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "player": {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "first_name": "John",
    "last_name": "Smith",
    "full_name": "John Smith",
    "phone": "+995555999888",
    "email": "john.doe@example.com",
    "city": "Batumi",
    "country": "Georgia"
  }
}
```

---

### **6. Change Password**

**Endpoint:** `POST /api/player/change-password`
**Authentication:** Required

**Request:**
```json
{
  "current_password": "OldPass123",
  "new_password": "NewSecurePass456",
  "new_password_confirmation": "NewSecurePass456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully",
  "token": "3|newtoken123456..."
}
```

**Note:** Old tokens are revoked, use the new token!

---

### **7. Tournament History**

**Endpoint:** `GET /api/player/tournament-history`
**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "history": [
    {
      "id": "reg-id-1",
      "tournament": {
        "id": "tournament-id-1",
        "name": "Friday Night Poker",
        "start_date": "2024-11-23T19:00:00+00:00",
        "tournament_type": "freezeout",
        "game_type": "texas_holdem",
        "buy_in": 100
      },
      "status": "checked_in",
      "table_number": 5,
      "seat_number": 3,
      "registered_at": "2024-11-20T14:30:00+00:00",
      "checked_in_at": "2024-11-23T18:45:00+00:00"
    },
    {
      "id": "reg-id-2",
      "tournament": {
        "id": "tournament-id-2",
        "name": "Sunday Turbo",
        "start_date": "2024-11-26T16:00:00+00:00",
        "tournament_type": "turbo",
        "game_type": "texas_holdem",
        "buy_in": 150
      },
      "status": "waiting",
      "table_number": null,
      "seat_number": null,
      "registered_at": "2024-11-24T10:00:00+00:00",
      "checked_in_at": null
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 2
  }
}
```

---

### **8. Logout**

**Endpoint:** `POST /api/player/logout`
**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## 🔓 **PUBLIC ENDPOINTS (NO AUTH REQUIRED)**

These endpoints remain public:

```
GET  /api/tournaments                  # List all tournaments
GET  /api/tournaments/featured         # Featured tournaments
GET  /api/tournaments/upcoming         # Upcoming tournaments
GET  /api/tournaments/{id}             # Tournament details
GET  /api/tournaments/{id}/statistics  # Tournament stats
GET  /api/tournaments/{id}/tables      # Table layout
POST /api/checkin                      # QR code check-in
GET  /api/registration/{id}            # View registration
GET  /api/registrations                # List registrations
```

---

## 🔒 **PROTECTED ENDPOINTS (AUTH REQUIRED)**

These require authentication token:

```
POST /api/register                     # Register for tournament
POST /api/registration/{id}/cancel     # Cancel registration
GET  /api/player/profile               # Get profile
PUT  /api/player/profile               # Update profile
POST /api/player/change-password       # Change password
POST /api/player/logout                # Logout
GET  /api/player/tournament-history    # Tournament history
```

---

## 🛠️ **ADMIN FEATURES (FILAMENT)**

### **Manual Seat Assignment**

Admins can now:

1. **View Player Accounts**
   - Navigate to `Registrations` in Filament
   - See which player account is linked to each registration
   - See "Account: player@email.com" under player name

2. **Manually Change Seats**
   - Click "Edit" on any registration
   - Find "Seat Assignment" section
   - Change table number
   - Change seat number
   - Save

3. **Link Player Accounts**
   - In registration edit form
   - Select "Player Account" dropdown
   - Search by name, email, or phone
   - Link existing player to registration

4. **View Player Statistics**
   - Player accounts show:
     - Total registrations
     - Tournaments played
     - Cancelled registrations
     - Waiting list entries

---

## 💻 **FRONTEND IMPLEMENTATION**

### **1. Authentication Store (Pinia)**

Create `frontend/src/stores/auth.js`:

```javascript
import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    player: null,
    token: localStorage.getItem('authToken') || null,
    isAuthenticated: false,
  }),

  actions: {
    async register(data) {
      const response = await axios.post('/api/player/register', data)
      this.setAuth(response.data.player, response.data.token)
      return response.data
    },

    async login(email, password) {
      const response = await axios.post('/api/player/login', { email, password })
      this.setAuth(response.data.player, response.data.token)
      return response.data
    },

    setAuth(player, token) {
      this.player = player
      this.token = token
      this.isAuthenticated = true
      localStorage.setItem('authToken', token)
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },

    async logout() {
      if (this.token) {
        await axios.post('/api/player/logout')
      }
      this.clearAuth()
    },

    clearAuth() {
      this.player = null
      this.token = null
      this.isAuthenticated = false
      localStorage.removeItem('authToken')
      delete axios.defaults.headers.common['Authorization']
    },

    async fetchProfile() {
      if (!this.token) return
      
      const response = await axios.get('/api/player/profile')
      this.player = response.data.player
      this.isAuthenticated = true
    },

    initializeAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
        this.fetchProfile()
      }
    },
  },
})
```

---

### **2. Axios Configuration**

Update `frontend/src/main.js`:

```javascript
import axios from 'axios'
import { createPinia } from 'pinia'
import { useAuthStore } from './stores/auth'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)

// Configure axios
axios.defaults.baseURL = import.meta.env.VITE_API_URL

// Initialize auth
const authStore = useAuthStore()
authStore.initializeAuth()

app.mount('#app')
```

---

### **3. Login Page**

Create `frontend/src/views/LoginPage.vue`:

```vue
<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-poker-950 to-poker-900 px-4">
    <div class="glass-card p-8 w-full max-w-md">
      <h1 class="text-3xl font-bold text-center mb-6">Player Login</h1>
      
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-2">Email</label>
          <input
            v-model="email"
            type="email"
            required
            class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-2">Password</label>
          <input
            v-model="password"
            type="password"
            required
            class="w-full px-4 py-2 rounded-lg bg-white/10 border border-white/20"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full btn-primary"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>

      <p class="text-center mt-4">
        Don't have an account?
        <router-link to="/register" class="text-poker-400 hover:underline">
          Register here
        </router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)

const handleLogin = async () => {
  loading.value = true
  try {
    await authStore.login(email.value, password.value)
    router.push('/tournaments')
  } catch (error) {
    alert(error.response?.data?.message || 'Login failed')
  } finally {
    loading.value = false
  }
}
</script>
```

---

### **4. Register Page**

Create `frontend/src/views/RegisterPage.vue` (similar structure with more fields)

---

### **5. Protected Route Guard**

Update `frontend/src/router/index.js`:

```javascript
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  // ...routes
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else {
    next()
  }
})

export default router
```

---

### **6. Update Tournament Registration**

Update existing registration to use auth:

```javascript
// In ReservePage.vue or TournamentDetailPage.vue
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const registerForTournament = async (tournamentId) => {
  // Check if logged in
  if (!authStore.isAuthenticated) {
    router.push('/login')
    return
  }

  try {
    const response = await axios.post('/api/register', {
      tournament_id: tournamentId
    })
    
    // Show success message with seat info
    alert(`Registered! Table ${response.data.table}, Seat ${response.data.seat}`)
  } catch (error) {
    alert(error.response?.data?.message || 'Registration failed')
  }
}
```

---

## 🎯 **AUTOMATIC SEAT ASSIGNMENT**

### **How It Works:**

1. **Player registers for tournament**
2. **System checks available seats**
3. **Randomly assigns table & seat** (up to 100 attempts to find available)
4. **If full, adds to waiting list** (if enabled)
5. **Generates QR code** for check-in
6. **Updates prize pool**

### **Admin Can Override:**

1. Go to Filament admin
2. Navigate to Registrations
3. Click Edit on any registration
4. Change table number
5. Change seat number
6. Save

**System validates:**
- Table number <= total tables
- Seat number <= seats per table
- No duplicate seat assignments

---

## ✅ **TESTING THE SYSTEM**

### **1. Create a Player Account**

```bash
curl -X POST http://127.0.0.1:8000/api/player/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Test",
    "last_name": "Player",
    "phone": "+995555000111",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

---

### **2. Login**

```bash
curl -X POST http://127.0.0.1:8000/api/player/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

Save the token from response!

---

### **3. Register for Tournament**

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "tournament_id": "YOUR_TOURNAMENT_ID"
  }'
```

---

### **4. Check Filament Admin**

1. Go to `http://127.0.0.1:8000/admin/registrations`
2. See new registration with player account linked
3. Click Edit
4. Try changing table/seat
5. Save and verify

---

## 📋 **FEATURES SUMMARY**

### ✅ **Implemented:**

1. **Player Registration System**
   - Email/password authentication
   - Profile management
   - Password change
   - Logout

2. **Tournament Registration (Authenticated)**
   - Requires login
   - Auto seat assignment
   - Waiting list support
   - Player info auto-filled

3. **Admin Manual Override**
   - View player accounts
   - Change seats manually
   - Link player accounts
   - View player statistics

4. **API Token Authentication**
   - Laravel Sanctum
   - Bearer token auth
   - Token revocation on logout
   - Separate player guard

5. **Database Structure**
   - Players table created
   - Registrations updated with player_id
   - Foreign keys and indexes
   - Soft deletes

---

## 🎉 **READY TO USE!**

**Backend:** ✅ 100% Complete
**Database:** ✅ Migrations ran successfully
**API:** ✅ All endpoints working
**Admin:** ✅ Manual seat editing enabled

**Next Step:** Implement frontend authentication pages!

---

**Try it now:** `http://127.0.0.1:8000/admin/registrations`

See the new player account integration! 🚀✨

