import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  // Public Routes
  {
    path: '/',
    name: 'Home',
    component: () => import('../views/HomePage.vue'),
    meta: { title: 'Kings Club' }
  },
  
  // Authentication Routes
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginPage.vue'),
    meta: { title: 'Login', guest: true }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('../views/RegisterPage.vue'),
    meta: { title: 'Create Account', guest: true }
  },

  // Tournament Routes
  {
    path: '/tournaments',
    name: 'Tournaments',
    component: () => import('../views/TournamentsListPage.vue'),
    meta: { title: 'Browse Tournaments' }
  },
  {
    path: '/tournament/:id',
    name: 'TournamentDetail',
    component: () => import('../views/TournamentDetailPage.vue'),
    props: true,
    meta: { title: 'Tournament Details' }
  },
  {
    path: '/tournament/:id/tables',
    name: 'TournamentTables',
    component: () => import('../views/TournamentTablesView.vue'),
    props: true,
    meta: { title: 'Tournament Tables' }
  },

  // Cash Game Routes
  {
    path: '/cash-games',
    name: 'CashGames',
    component: () => import('../views/CashGamesListPage.vue'),
    meta: { title: 'Browse Cash Games' }
  },
  {
    path: '/cash-game/:id',
    name: 'CashGameDetail',
    component: () => import('../views/CashGameDetailPage.vue'),
    props: true,
    meta: { title: 'Cash Game Details' }
  },
  {
    path: '/cash-game/:id/tables',
    name: 'CashGameTables',
    component: () => import('../views/CashGameTablesView.vue'),
    props: true,
    meta: { title: 'Cash Game Tables' }
  },

  // Player Profile Routes (Protected)
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('../views/PlayerProfilePage.vue'),
    meta: { title: 'My Profile', requiresAuth: true }
  },
  {
    path: '/profile/edit',
    name: 'EditProfile',
    component: () => import('../views/EditProfilePage.vue'),
    meta: { title: 'Edit Profile', requiresAuth: true }
  },
  {
    path: '/profile/change-password',
    name: 'ChangePassword',
    component: () => import('../views/ChangePasswordPage.vue'),
    meta: { title: 'Change Password', requiresAuth: true }
  },
  {
    path: '/my-tournaments',
    name: 'MyTournaments',
    component: () => import('../views/MyTournamentsPage.vue'),
    meta: { title: 'My Tournaments', requiresAuth: true }
  },
  {
    path: '/my-cash-games',
    name: 'MyCashGames',
    component: () => import('../views/MyCashGamesPage.vue'),
    meta: { title: 'My Cash Games', requiresAuth: true }
  },

  // Registration/Confirmation Routes
  {
    path: '/register-tournament/:tournamentId',
    name: 'RegisterTournament',
    component: () => import('../views/RegisterTournamentPage.vue'),
    props: true,
    meta: { title: 'Register for Tournament', requiresAuth: true }
  },
  {
    path: '/register-cash-game/:cashGameId',
    name: 'RegisterCashGame',
    component: () => import('../views/RegisterCashGamePage.vue'),
    props: true,
    meta: { title: 'Join Cash Game', requiresAuth: true }
  },
  {
    path: '/confirmation/:id',
    name: 'Confirmation',
    component: () => import('../views/ConfirmationPage.vue'),
    props: true,
    meta: { title: 'Registration Confirmed' }
  },

  // QR Code Check-in
  {
    path: '/checkin',
    name: 'CheckIn',
    component: () => import('../views/CheckInPage.vue'),
    meta: { title: 'Check In' }
  },
  {
    path: '/scanner',
    name: 'Scanner',
    component: () => import('../views/ScannerPage.vue'),
    meta: { title: 'QR Scanner' }
  },

  // Table View
  {
    path: '/tables/:tournamentId',
    name: 'Tables',
    component: () => import('../views/TablesPage.vue'),
    props: true,
    meta: { title: 'Table Layout' }
  },

  // About & Contact Routes
  {
    path: '/about',
    name: 'About',
    component: () => import('../views/AboutPage.vue'),
    meta: { title: 'About Us' }
  },
  {
    path: '/contact',
    name: 'Contact',
    component: () => import('../views/ContactPage.vue'),
    meta: { title: 'Contact Us' }
  },

  // Legacy Routes (for backward compatibility)
  {
    path: '/my-reservation',
    redirect: { name: 'MyTournaments' }
  },
  {
    path: '/reserve',
    redirect: { name: 'Tournaments' }
  },

  // 404 Not Found
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFoundPage.vue'),
    meta: { title: '404 - Not Found' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0, behavior: 'smooth' }
    }
  }
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  // Set page title
  document.title = to.meta.title ? `${to.meta.title} | Kings Club` : 'Kings Club'
  
  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isLoggedIn) {
    // Save intended destination
    next({
      name: 'Login',
      query: { redirect: to.fullPath }
    })
    return
  }
  
  // Check if route is for guests only (login/register)
  if (to.meta.guest && authStore.isLoggedIn) {
    next({ name: 'Tournaments' })
    return
  }
  
  next()
})

export default router
