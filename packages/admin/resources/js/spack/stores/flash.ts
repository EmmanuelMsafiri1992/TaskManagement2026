import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export type FlashType = 'success' | 'error' | 'warning' | 'info' | 'danger'

interface FlashMessage {
  id: number
  type: FlashType
  message: string
  duration?: number
}

export const useFlashStore = defineStore('flash', () => {
  const messages = ref<FlashMessage[]>([])
  let messageIdCounter = 0

  // Computed for the first/current message (used in templates)
  const message = computed(() => messages.value.length > 0 ? messages.value[0].message : '')
  const type = computed(() => messages.value.length > 0 ? messages.value[0].type : 'info')

  function add(msg: string, msgType: FlashType = 'info', duration = 5000) {
    const id = messageIdCounter++
    const flash: FlashMessage = {
      id,
      type: msgType,
      message: msg,
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

  function success(msg: string, duration = 5000) {
    return add(msg, 'success', duration)
  }

  function error(msg: string, duration = 5000) {
    return add(msg, 'error', duration)
  }

  function warning(msg: string, duration = 5000) {
    return add(msg, 'warning', duration)
  }

  function info(msg: string, duration = 5000) {
    return add(msg, 'info', duration)
  }

  function danger(msg: string, duration = 5000) {
    return add(msg, 'danger', duration)
  }

  function remove(id: number) {
    const index = messages.value.findIndex(m => m.id === id)
    if (index > -1) {
      messages.value.splice(index, 1)
    }
  }

  // Alias for removing the first message (used in templates)
  function hide() {
    if (messages.value.length > 0) {
      remove(messages.value[0].id)
    }
  }

  function clear() {
    messages.value = []
  }

  return {
    messages,
    message,
    type,
    add,
    success,
    error,
    warning,
    info,
    danger,
    remove,
    hide,
    clear,
  }
})
