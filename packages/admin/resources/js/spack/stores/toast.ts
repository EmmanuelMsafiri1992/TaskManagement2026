import { defineStore } from 'pinia'
import { ref } from 'vue'

export type ToastType = 'success' | 'error' | 'warning' | 'info'

interface Toast {
  id: number
  type: ToastType
  message: string
  duration?: number
}

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<Toast[]>([])
  let toastIdCounter = 0

  function add(message: string, type: ToastType = 'info', duration = 3000) {
    const id = toastIdCounter++
    const toast: Toast = {
      id,
      type,
      message,
      duration,
    }

    toasts.value.push(toast)

    if (duration > 0) {
      setTimeout(() => {
        remove(id)
      }, duration)
    }

    return id
  }

  function success(message: string, duration = 3000) {
    return add(message, 'success', duration)
  }

  function error(message: string, duration = 3000) {
    return add(message, 'error', duration)
  }

  function warning(message: string, duration = 3000) {
    return add(message, 'warning', duration)
  }

  function info(message: string, duration = 3000) {
    return add(message, 'info', duration)
  }

  function remove(id: number) {
    const index = toasts.value.findIndex(t => t.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  function clear() {
    toasts.value = []
  }

  return {
    toasts,
    add,
    success,
    error,
    warning,
    info,
    remove,
    clear,
  }
})
