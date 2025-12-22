import { useIndexStore } from 'spack'

export const useIndex = (endpoint: string, params: Record<string, any> = {}) => {
  // Extract the resource name from the endpoint (e.g., '/api/employees' -> 'employees')
  const resourceName = endpoint.split('/').pop() || 'resource'

  const indexStore = useIndexStore(resourceName)()
  indexStore.endpoint = endpoint

  // Set initial params
  if (params) {
    Object.assign(indexStore.params, params)
  }

  return indexStore
}
