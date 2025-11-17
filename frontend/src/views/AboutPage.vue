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
              About Kings Club
            </span>
          </div>
          
          <h1 class="text-5xl md:text-7xl font-black text-white mb-6 leading-tight">
            Welcome to
            <span class="gradient-text block">Kings Club</span>
          </h1>
          
          <p class="text-xl text-slate-300 mb-10 max-w-2xl mx-auto">
            Georgia's premier poker destination for competitive tournaments and exciting cash games
          </p>
        </div>
      </div>
    </section>

    <!-- Our Story Section -->
    <section class="page-container py-16">
      <div class="card p-8 md:p-12">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center shadow-lg shadow-emerald-900/50">
            <BookOpen :size="32" class="text-white" />
          </div>
          <h2 class="text-3xl md:text-4xl font-black text-white">Our Story</h2>
        </div>
        
        <div class="prose prose-invert max-w-none">
          <p class="text-lg text-slate-300 leading-relaxed mb-6">
            Kings Club was founded with a vision to create Georgia's most prestigious poker community. 
            We bring together passionate players from all skill levels to compete in professionally organized 
            tournaments and cash games.
          </p>
          <p class="text-lg text-slate-300 leading-relaxed mb-6">
            Our state-of-the-art facilities and commitment to fair play have made us the go-to destination 
            for serious poker enthusiasts. Whether you're a seasoned pro or just starting your poker journey, 
            Kings Club offers an unparalleled experience.
          </p>
          <p class="text-lg text-slate-300 leading-relaxed">
            We pride ourselves on creating a welcoming atmosphere where skill, strategy, and sportsmanship 
            come together. Join us and become part of the Kings Club legacy.
          </p>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="page-container py-16">
      <div class="text-center mb-12">
        <h2 class="section-title mb-2">Why Choose Kings Club</h2>
        <p class="text-slate-400 text-lg">Experience the best in poker entertainment</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="card p-8 text-center group hover:border-emerald-700 transition-all">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50 group-hover:scale-110 transition-transform">
            <Trophy :size="32" class="text-white" />
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Premium Tournaments</h3>
          <p class="text-slate-400">Competitive tournaments with guaranteed prize pools and professional organization</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700 transition-all">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50 group-hover:scale-110 transition-transform">
            <DollarSign :size="32" class="text-white" />
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Live Cash Games</h3>
          <p class="text-slate-400">Multiple stakes and game types available 24/7 for all skill levels</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700 transition-all">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50 group-hover:scale-110 transition-transform">
            <Shield :size="32" class="text-white" />
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Fair & Secure</h3>
          <p class="text-slate-400">Transparent rules, secure transactions, and professional oversight</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700 transition-all">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50 group-hover:scale-110 transition-transform">
            <Users :size="32" class="text-white" />
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Vibrant Community</h3>
          <p class="text-slate-400">Connect with fellow players and build lasting friendships</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700 transition-all">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50 group-hover:scale-110 transition-transform">
            <Award :size="32" class="text-white" />
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Professional Staff</h3>
          <p class="text-slate-400">Experienced dealers and tournament directors ensure smooth gameplay</p>
        </div>

        <div class="card p-8 text-center group hover:border-emerald-700 transition-all">
          <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-600 to-emerald-700 flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/50 group-hover:scale-110 transition-transform">
            <Clock :size="32" class="text-white" />
          </div>
          <h3 class="text-xl font-bold text-white mb-3">Convenient Scheduling</h3>
          <p class="text-slate-400">Easy online registration and flexible tournament schedules</p>
        </div>
      </div>
    </section>

    <!-- Values Section -->
    <section class="page-container py-16">
      <div class="card bg-gradient-to-br from-emerald-900/30 to-emerald-800/20 border-emerald-700/50 p-8 md:p-12">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-black text-white mb-4">Our Values</h2>
          <p class="text-slate-300 text-lg">The principles that guide everything we do</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="flex gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
              <Heart :size="24" class="text-emerald-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-2">Passion for Poker</h3>
              <p class="text-slate-300">We live and breathe poker, and our enthusiasm shows in every event we organize</p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
              <Target :size="24" class="text-emerald-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-2">Excellence</h3>
              <p class="text-slate-300">We strive for perfection in every detail, from tournament structure to player experience</p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
              <Shield :size="24" class="text-emerald-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-2">Integrity</h3>
              <p class="text-slate-300">Honest, transparent, and fair - these values are non-negotiable in everything we do</p>
            </div>
          </div>

          <div class="flex gap-4">
            <div class="w-12 h-12 rounded-lg bg-emerald-600/20 flex items-center justify-center flex-shrink-0">
              <Sparkles :size="24" class="text-emerald-400" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-2">Innovation</h3>
              <p class="text-slate-300">We continuously improve our platform and services to enhance your poker experience</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="page-container py-16">
      <div class="card bg-gradient-to-br from-emerald-900/50 to-emerald-800/30 border-emerald-700 p-12 text-center">
        <h2 class="text-4xl md:text-5xl font-black text-white mb-4">
          Ready to Join the Action?
        </h2>
        <p class="text-xl text-emerald-100 mb-8 max-w-2xl mx-auto">
          Create your account today and start competing in exciting tournaments and cash games
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link v-if="!authStore.isLoggedIn" to="/register" class="btn-accent text-lg px-8 py-4 inline-flex items-center gap-2">
            <UserPlus :size="24" />
            <span>Create Free Account</span>
          </router-link>
          <router-link to="/tournaments" class="btn-secondary text-lg px-8 py-4 inline-flex items-center gap-2">
            <Trophy :size="24" />
            <span>Browse Tournaments</span>
          </router-link>
          <router-link to="/cash-games" class="btn-secondary text-lg px-8 py-4 inline-flex items-center gap-2">
            <DollarSign :size="24" />
            <span>View Cash Games</span>
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'
import {
  BookOpen, Trophy, DollarSign, Shield, Users, Award, Clock,
  Heart, Target, Sparkles, UserPlus
} from 'lucide-vue-next'

const authStore = useAuthStore()
</script>

