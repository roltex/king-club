<template>
  <div class="flex items-center justify-center" :class="containerClass">
    <Loader2 :size="size" class="animate-spin text-poker-400" />
    <span v-if="text" class="ml-3 text-white/70">{{ text }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Loader2 } from 'lucide-vue-next'

const props = defineProps({
  size: {
    type: Number,
    default: 40
  },
  text: {
    type: String,
    default: ''
  },
  class: {
    type: String,
    default: ''
  }
})

const containerClass = computed(() => props.class)
</script>
