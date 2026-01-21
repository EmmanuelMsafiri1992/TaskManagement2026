import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

interface ConfirmOptions {
  title?: string
  message: string
  confirmText?: string
  cancelText?: string
  danger?: boolean
  onConfirm?: () => void | Promise<void>
  onCancel?: () => void
}

export const useConfirmStore = defineStore('confirm', () => {
  const isOpen = ref(false)
  const title = ref('Confirm')
  const message = ref('')
  const confirmText = ref('Confirm')
  const cancelText = ref('Cancel')
  const processing = ref(false)
  const danger = ref(false)

  let confirmCallback: (() => void | Promise<void>) | null = null
  let cancelCallback: (() => void) | null = null

  // Alias for message (used in templates)
  const description = computed(() => message.value)
  // Alias for confirmText (used in templates)
  const button = computed(() => confirmText.value)

  function show(options: ConfirmOptions) {
    title.value = options.title || 'Confirm'
    message.value = options.message
    confirmText.value = options.confirmText || 'Confirm'
    cancelText.value = options.cancelText || 'Cancel'
    danger.value = options.danger || false
    confirmCallback = options.onConfirm || null
    cancelCallback = options.onCancel || null
    isOpen.value = true
  }

  async function confirm() {
    if (confirmCallback) {
      processing.value = true
      try {
        await confirmCallback()
      } finally {
        processing.value = false
      }
    }
    close()
  }

  // Alias for confirm (used in templates)
  async function proceed() {
    return confirm()
  }

  function cancel() {
    if (cancelCallback) {
      cancelCallback()
    }
    close()
  }

  function close() {
    isOpen.value = false
    danger.value = false
    confirmCallback = null
    cancelCallback = null
  }

  // Alias for close (used in templates)
  function hide() {
    close()
  }

  return {
    isOpen,
    title,
    message,
    description,
    confirmText,
    button,
    cancelText,
    processing,
    danger,
    show,
    confirm,
    proceed,
    cancel,
    close,
    hide,
  }
})
