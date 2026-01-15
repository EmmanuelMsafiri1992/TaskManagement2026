import { useIndexStore } from 'spack'
import { axios } from 'spack/axios'

export const useIndex = (endpoint: string, params: Record<string, any> = {}) => {
  // Extract the resource name from the endpoint (e.g., '/api/employees' -> 'employees')
  const resourceName = endpoint.split('/').pop() || 'resource'

  const indexStore = useIndexStore(resourceName)()

  // Set the URI (the store uses 'uri' not 'endpoint')
  indexStore.uri = endpoint

  // Set initial params
  if (params) {
    Object.assign(indexStore.params, params)
  }

  // Add convenience methods directly to the store instance
  // This preserves reactivity since we're returning the same store object
  ;(indexStore as any).get = async () => {
    return indexStore.fetch()
  }

  ;(indexStore as any).deleteIt = async (id: number | string) => {
    if (!confirm('Are you sure you want to delete this item?')) {
      return false
    }
    try {
      await axios.delete(`${endpoint}/${id}`)
      await indexStore.fetch()
      return true
    } catch (error) {
      console.error('Delete error:', error)
      return false
    }
  }

  // Pagination methods
  ;(indexStore as any).prev = async () => {
    if (indexStore.currentPage > 1) {
      indexStore.currentPage--
      return indexStore.fetch()
    }
  }

  ;(indexStore as any).next = async () => {
    indexStore.currentPage++
    return indexStore.fetch()
  }

  // Return the store directly to preserve reactivity
  return indexStore as typeof indexStore & {
    get: () => Promise<void>
    deleteIt: (id: number | string) => Promise<boolean>
    prev: () => Promise<void>
    next: () => Promise<void>
  }
}
