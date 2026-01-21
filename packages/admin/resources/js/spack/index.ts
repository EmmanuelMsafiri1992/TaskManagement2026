// Axios instance
export { axios } from './axios'

// Pinia stores - re-export with types
export { useFormStore } from './stores/form'
export type { } from './stores/form'
export { useModalsStore } from './stores/modals'
export { useIndexStore } from './stores/index'
export { useConfirmStore } from './stores/confirm'
export { useFlashStore } from './stores/flash'
export { useToastStore } from './stores/toast'
export { useTabStore } from './stores/tab'

// Vue plugin
export { default as SpackPlugin } from './plugin'

// Re-export form data type for TSX components
export interface FormData {
  [key: string]: any
}

export interface FormErrors {
  [key: string]: string[]
}
