import { defineStore } from 'pinia'
import { ref } from 'vue'

export type FlashType = 'success' | 'error' | 'warning' | 'info'

interface FlashMessage {
  id: number
  type: FlashType
  message: string
  duration?: number
}

export const useFlashStore = defineStore('flash', () => {
  const messages = ref<FlashMessage[]>([])
  let messageIdCounter = 0

  function add(message: string, type: FlashType = 'info', duration = 5000) {
    const id = messageIdCounter++
    const flash: FlashMessage = {
      id,
      type,
      message,
      duration,
    }

    messages.value.push(flash)

    if (duration > 0) {
      setTimeout(() => {
        remove(id)
      }, duration)
    }

    return id
  }

  function success(message: string, duration = 5000) {
    return add(message, 'success', duration)
  }

  function error(message: string, duration = 5000) {
    return add(message, 'error', duration)
  }

  function warning(message: string, duration = 5000) {
    return add(message, 'warning', duration)
  }

  function info(message: string, duration = 5000) {
    return add(message, 'info', duration)
  }

  function remove(id: number) {
    const index = messages.value.findIndex(m => m.id === id)
    if (index > -1) {
      messages.value.splice(index, 1)
    }
  }

  function clear() {
    messages.value = []
  }

  return {
    messages,
    add,
    success,
    error,
    warning,
    info,
    remove,
    clear,
  }
})
