<template>
  <footer class="bg-slate-900 border-t border-slate-800 mt-20">
    <div class="page-container py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        <!-- Brand -->
        <div class="md:col-span-2">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center">
              <Spade :size="24" class="text-white" />
            </div>
            <div>
              <h3 class="text-xl font-black text-white">Kings Club</h3>
              <p class="text-xs text-slate-400">Play & Win Big</p>
            </div>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed max-w-md">
            Your premier destination for competitive Kings Club. Join players from around the world, compete for amazing prizes, and showcase your skills at the table.
          </p>
        </div>

        <!-- Quick Links -->
        <div>
          <h4 class="text-white font-bold mb-4">Quick Links</h4>
          <ul class="space-y-2">
            <li>
              <router-link to="/about" class="text-slate-400 hover:text-emerald-400 text-sm transition-colors">
                About
              </router-link>
            </li>
            <li>
              <router-link to="/tournaments" class="text-slate-400 hover:text-emerald-400 text-sm transition-colors">
                Tournaments
              </router-link>
            </li>
            <li>
              <router-link to="/cash-games" class="text-slate-400 hover:text-emerald-400 text-sm transition-colors">
                Cash Games
              </router-link>
            </li>
            <li>
              <router-link to="/contact" class="text-slate-400 hover:text-emerald-400 text-sm transition-colors">
                Contact
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div>
          <h4 class="text-white font-bold mb-4">Contact</h4>
          <ul class="space-y-3">
            <li class="flex items-center gap-3 text-slate-400 text-sm">
              <Mail :size="16" class="text-emerald-400 flex-shrink-0" />
              <a href="mailto:info@pokertournaments.ge" class="hover:text-emerald-400 transition-colors">
                info@pokertournaments.ge
              </a>
            </li>
            <li class="flex items-center gap-3 text-slate-400 text-sm">
              <Phone :size="16" class="text-emerald-400 flex-shrink-0" />
              <a href="tel:+995555123456" class="hover:text-emerald-400 transition-colors">
                +995 555 123 456
              </a>
            </li>
            <li class="flex items-center gap-3 text-slate-400 text-sm">
              <MapPin :size="16" class="text-emerald-400 flex-shrink-0" />
              <span>Tbilisi, Georgia</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom Bar -->
      <div class="pt-8 border-t border-slate-800">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <p class="text-slate-500 text-sm text-center md:text-left">
            © {{ currentYear }} Kings Club. All rights reserved.
          </p>
          
          <div class="flex items-center gap-6">
            <a 
              href="https://facebook.com" 
              target="_blank" 
              class="text-slate-500 hover:text-emerald-400 transition-colors"
              aria-label="Facebook"
            >
              <Facebook :size="20" />
            </a>
            <a 
              href="https://twitter.com" 
              target="_blank" 
              class="text-slate-500 hover:text-emerald-400 transition-colors"
              aria-label="Twitter"
            >
              <Twitter :size="20" />
            </a>
            <a 
              href="https://instagram.com" 
              target="_blank" 
              class="text-slate-500 hover:text-emerald-400 transition-colors"
              aria-label="Instagram"
            >
              <Instagram :size="20" />
            </a>
            <a 
              href="https://youtube.com" 
              target="_blank" 
              class="text-slate-500 hover:text-emerald-400 transition-colors"
              aria-label="YouTube"
            >
              <Youtube :size="20" />
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import { 
  Spade, Mail, Phone, MapPin, Facebook, Twitter, Instagram, Youtube 
} from 'lucide-vue-next'

const authStore = useAuthStore()
const currentYear = computed(() => new Date().getFullYear())
</script>
