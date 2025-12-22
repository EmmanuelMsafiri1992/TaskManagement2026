import { onBeforeUnmount } from 'vue'
import { useStore } from 'Store/main'

export function useBlankPageRuntime(): void {
  const store = useStore()
  store.isBlankPage = true

  onBeforeUnmount(() => (store.isBlankPage = false))
}
