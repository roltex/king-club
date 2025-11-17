<template>
  <div class="min-h-screen bg-slate-950">
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950">
      <div class="absolute inset-0 bg-[url('/grid.svg')] opacity-5"></div>
      
      <div class="page-container py-24 relative">
        <div class="max-w-4xl mx-auto text-center">
          <div class="inline-block mb-6">
            <span class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-900/50 border border-emerald-700 rounded-full text-emerald-400 text-sm font-semibold">
              <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
              Get in Touch
            </span>
          </div>
          
          <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight">
            Contact
            <span class="gradient-text block">Kings Club</span>
          </h1>
          
          <p class="text-xl text-slate-300 mb-10 max-w-2xl mx-auto">
            Have questions? We're here to help. Reach out to our team anytime
          </p>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section class="page-container py-16">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Contact Information -->
        <div class="lg:col-span-1 space-y-6">
          <div class="card p-6">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
              <Mail :size="24" class="text-emerald-400" />
              Contact Information
            </h2>
            
            <div class="space-y-6">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                  <Mail :size="20" class="text-emerald-400" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">Email</h3>
                  <a href="mailto:info@kingsclub.ge" class="text-white font-semibold hover:text-emerald-400 transition-colors">
                    info@kingsclub.ge
                  </a>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                  <Phone :size="20" class="text-emerald-400" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">Phone</h3>
                  <a href="tel:+995555123456" class="text-white font-semibold hover:text-emerald-400 transition-colors">
                    +995 555 123 456
                  </a>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                  <MapPin :size="20" class="text-emerald-400" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">Location</h3>
                  <p class="text-white font-semibold">
                    12 Rustaveli Avenue<br>
                    Tbilisi, Georgia 0108
                  </p>
                </div>
              </div>

              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
                  <Clock :size="20" class="text-emerald-400" />
                </div>
                <div>
                  <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">Hours</h3>
                  <p class="text-white font-semibold">
                    Daily: 12:00 PM - 4:00 AM<br>
                    <span class="text-slate-400 text-sm">Tournaments & Cash Games</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Links -->
          <div class="card p-6">
            <h2 class="text-xl font-bold text-white mb-4">Quick Links</h2>
            <div class="space-y-3">
              <router-link to="/tournaments" class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 hover:bg-slate-900 border border-slate-800 hover:border-emerald-700 transition-all group">
                <Trophy :size="18" class="text-emerald-400 group-hover:scale-110 transition-transform" />
                <span class="text-slate-300 group-hover:text-white">Browse Tournaments</span>
              </router-link>
              <router-link to="/cash-games" class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 hover:bg-slate-900 border border-slate-800 hover:border-emerald-700 transition-all group">
                <DollarSign :size="18" class="text-emerald-400 group-hover:scale-110 transition-transform" />
                <span class="text-slate-300 group-hover:text-white">View Cash Games</span>
              </router-link>
              <router-link to="/about" class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 hover:bg-slate-900 border border-slate-800 hover:border-emerald-700 transition-all group">
                <Info :size="18" class="text-emerald-400 group-hover:scale-110 transition-transform" />
                <span class="text-slate-300 group-hover:text-white">About Us</span>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="lg:col-span-2">
          <div class="card p-8">
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
              <MessageSquare :size="24" class="text-emerald-400" />
              Send us a Message
            </h2>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-slate-400 mb-2">
                    Your Name <span class="text-red-400">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="input w-full"
                    placeholder="John Doe"
                  />
                </div>

                <div>
                  <label for="email" class="block text-sm font-medium text-slate-400 mb-2">
                    Email Address <span class="text-red-400">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="input w-full"
                    placeholder="john@example.com"
                  />
                </div>
              </div>

              <div>
                <label for="phone" class="block text-sm font-medium text-slate-400 mb-2">
                  Phone Number
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="input w-full"
                  placeholder="+995 555 123 456"
                />
              </div>

              <div>
                <label for="subject" class="block text-sm font-medium text-slate-400 mb-2">
                  Subject <span class="text-red-400">*</span>
                </label>
                <select
                  id="subject"
                  v-model="form.subject"
                  required
                  class="input w-full"
                >
                  <option value="">Select a subject</option>
                  <option value="tournament">Tournament Inquiry</option>
                  <option value="cashgame">Cash Game Inquiry</option>
                  <option value="registration">Registration Help</option>
                  <option value="general">General Question</option>
                  <option value="feedback">Feedback</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div>
                <label for="message" class="block text-sm font-medium text-slate-400 mb-2">
                  Message <span class="text-red-400">*</span>
                </label>
                <textarea
                  id="message"
                  v-model="form.message"
                  required
                  rows="6"
                  class="input w-full resize-none"
                  placeholder="Tell us how we can help you..."
                ></textarea>
              </div>

              <!-- Success Message -->
              <div v-if="successMessage" class="p-4 bg-emerald-900/20 border border-emerald-800 rounded-lg">
                <div class="flex items-center gap-2 text-emerald-400">
                  <CheckCircle :size="20" />
                  <span>{{ successMessage }}</span>
                </div>
              </div>

              <!-- Error Message -->
              <div v-if="errorMessage" class="p-4 bg-red-900/20 border border-red-800 rounded-lg">
                <div class="flex items-center gap-2 text-red-400">
                  <AlertCircle :size="20" />
                  <span>{{ errorMessage }}</span>
                </div>
              </div>

              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full btn-primary py-4 text-lg font-bold flex items-center justify-center gap-2"
                :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
              >
                <Loader v-if="isSubmitting" :size="24" class="animate-spin" />
                <Send v-else :size="24" />
                <span>{{ isSubmitting ? 'Sending...' : 'Send Message' }}</span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <!-- Map Section (Optional) -->
    <section class="page-container py-16">
      <div class="card p-0 overflow-hidden">
        <div class="bg-slate-900 p-8 border-b border-slate-800">
          <h2 class="text-2xl font-bold text-white mb-2 flex items-center gap-3">
            <MapPin :size="24" class="text-emerald-400" />
            Find Us
          </h2>
          <p class="text-slate-400">Visit our location in the heart of Tbilisi</p>
        </div>
        <div class="bg-slate-800 h-96 flex items-center justify-center">
          <div class="text-center">
            <MapPin :size="64" class="text-slate-700 mx-auto mb-4" />
            <p class="text-slate-500 font-semibold">12 Rustaveli Avenue, Tbilisi, Georgia</p>
            <a 
              href="https://www.google.com/maps/search/?api=1&query=12+Rustaveli+Avenue+Tbilisi+Georgia" 
              target="_blank"
              class="inline-flex items-center gap-2 mt-4 text-emerald-400 hover:text-emerald-300 transition-colors"
            >
              <span>Open in Google Maps</span>
              <ArrowRight :size="18" />
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  Mail, Phone, MapPin, Clock, Trophy, DollarSign, Info,
  MessageSquare, Send, CheckCircle, AlertCircle, Loader, ArrowRight
} from 'lucide-vue-next'

const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const handleSubmit = async () => {
  successMessage.value = ''
  errorMessage.value = ''
  isSubmitting.value = true

  try {
    // Simulate API call (you can replace this with actual API endpoint)
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // For now, just show success message
    // In production, you would send this to your backend API
    successMessage.value = 'Thank you for your message! We\'ll get back to you soon.'
    
    // Reset form
    form.value = {
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: ''
    }
  } catch (error) {
    errorMessage.value = 'Failed to send message. Please try again or contact us directly.'
  } finally {
    isSubmitting.value = false
  }
}
</script>

