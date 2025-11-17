<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toastStore.toasts"
        :key="toast.id"
        :class="toastClass(toast.type)"
        class="glass-card px-6 py-4 rounded-xl shadow-glass-lg flex items-center gap-3 min-w-[300px] max-w-md"
      >
        <component :is="getIcon(toast.type)" :size="20" />
        <span class="flex-1">{{ toast.message }}</span>
        <button
          @click="toastStore.removeToast(toast.id)"
          class="hover:bg-white/10 rounded-lg p-1 transition-colors"
        >
          <X :size="16" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup>
import { CheckCircle, XCircle, AlertCircle, Info, X } from 'lucide-vue-next'
import { useToastStore } from '../stores/toastStore'

const toastStore = useToastStore()

const toastClass = (type) => {
  const classes = {
    success: 'border-green-500/50 bg-green-500/10',
    error: 'border-red-500/50 bg-red-500/10',
    warning: 'border-yellow-500/50 bg-yellow-500/10',
    info: 'border-blue-500/50 bg-blue-500/10'
  }
  return classes[type] || classes.info
}

const getIcon = (type) => {
  const icons = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertCircle,
    info: Info
  }
  return icons[type] || Info
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100px) scale(0.8);
}
</style>

