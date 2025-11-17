<template>
  <div class="text-center py-12" :class="containerClass">
    <div class="glass-card p-12 max-w-md mx-auto">
      <component :is="icon" :size="iconSize" class="text-white/20 mx-auto mb-4" />
      <h3 class="text-2xl font-bold text-white mb-2">{{ title }}</h3>
      <p class="text-white/60 mb-6">{{ description }}</p>
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  icon: {
    type: Object,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  iconSize: {
    type: Number,
    default: 64
  },
  class: {
    type: String,
    default: ''
  }
})

const containerClass = computed(() => props.class)
</script>

