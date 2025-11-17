<template>
  <div class="text-center mb-12 animate-fade-in">
    <!-- Icon (if provided) -->
    <div v-if="icon" class="mb-6 flex justify-center">
      <div class="glass-card p-6 inline-block">
        <component :is="icon" :size="48" class="text-poker-400" />
      </div>
    </div>

    <!-- Title with animated gradient -->
    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-4">
      <span class="bg-gradient-to-r from-white via-poker-200 to-accent-200 bg-clip-text text-transparent animate-gradient">
        <slot name="title">{{ title }}</slot>
      </span>
    </h1>

    <!-- Subtitle -->
    <p v-if="subtitle" class="text-lg md:text-xl text-white/70 max-w-2xl mx-auto leading-relaxed">
      <slot name="subtitle">{{ subtitle }}</slot>
    </p>

    <!-- Decorative line -->
    <div class="mt-6 flex items-center justify-center gap-3">
      <div class="h-px w-12 bg-gradient-to-r from-transparent to-poker-500"></div>
      <div class="w-2 h-2 rounded-full bg-poker-500 animate-pulse"></div>
      <div class="h-px w-12 bg-gradient-to-l from-transparent to-poker-500"></div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: ''
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: Object,
    default: null
  }
})
</script>

<style scoped>
@keyframes gradient {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.animate-gradient {
  background-size: 200% auto;
  animation: gradient 3s ease infinite;
}

@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out;
}
</style>

