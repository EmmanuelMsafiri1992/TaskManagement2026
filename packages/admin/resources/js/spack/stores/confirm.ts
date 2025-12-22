import { defineStore } from 'pinia'
import { ref } from 'vue'

interface ConfirmOptions {
  title?: string
  message: string
  confirmText?: string
  cancelText?: string
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

  let confirmCallback: (() => void | Promise<void>) | null = null
  let cancelCallback: (() => void) | null = null

  function show(options: ConfirmOptions) {
    title.value = options.title || 'Confirm'
    message.value = options.message
    confirmText.value = options.confirmText || 'Confirm'
    cancelText.value = options.cancelText || 'Cancel'
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

  function cancel() {
    if (cancelCallback) {
      cancelCallback()
    }
    close()
  }

  function close() {
    isOpen.value = false
    confirmCallback = null
    cancelCallback = null
  }

  return {
    isOpen,
    title,
    message,
    confirmText,
    cancelText,
    processing,
    show,
    confirm,
    cancel,
    close,
  }
})
