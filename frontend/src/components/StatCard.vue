<template>
  <div class="glass-card-hover p-6">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-white/60 text-sm font-medium mb-2">{{ label }}</p>
        <p class="text-3xl font-bold text-white">{{ value }}</p>
        <p v-if="subtitle" class="text-white/40 text-xs mt-2">{{ subtitle }}</p>
      </div>
      <div class="glass-card p-3 rounded-xl" :class="iconColor">
        <component :is="icon" :size="24" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  icon: {
    type: Object,
    required: true
  },
  iconColor: {
    type: String,
    default: 'text-poker-400'
  }
})
</script>

