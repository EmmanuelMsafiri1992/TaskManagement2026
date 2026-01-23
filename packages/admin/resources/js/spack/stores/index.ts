import { defineStore } from 'pinia'
import { computed, reactive, ref } from 'vue'
import { axios } from '../axios'
import type { AxiosResponse } from 'axios'

interface IndexFilters {
  [key: string]: any
}

interface IndexConfig {
  uri?: string
  [key: string]: any
}

interface ResponseMeta {
  current_page?: number
  last_page?: number
  per_page?: number
  total?: number
  [key: string]: any
}

export const useIndexStore = (name: string) => {
  return defineStore(`index-${name}`, () => {
    const data = ref<any>([])
    const meta = ref<ResponseMeta>({})
    const responseFilters = ref<any>({})
    const filters = reactive<IndexFilters>({})
    const params = reactive<IndexFilters>({})
    const sortBy = ref<string>('')
    const sortOrder = ref<'asc' | 'desc'>('asc')
    const perPage = ref<number>(15)
    const currentPage = ref<number>(1)
    const uri = ref<string>('')
    const fetching = ref<boolean>(false)
    const config = reactive<IndexConfig>({})

    // Computed for currently applied filters (used in templates)
    const appliedFilters = computed(() => {
      return Object.entries(filters).filter(([_, value]) => value !== '' && value !== null && value !== undefined)
    })

    // Computed to extract items array from data (handles both array and object responses)
    const items = computed(() => {
      if (Array.isArray(data.value)) {
        return data.value
      }
      if (data.value && Array.isArray(data.value.data)) {
        return data.value.data
      }
      return []
    })

    function setFilter(key: string, value: any) {
      filters[key] = value
    }

    function clearFilter(key: string) {
      delete filters[key]
    }

    function clearAllFilters() {
      Object.keys(filters).forEach(key => delete filters[key])
    }

    // Alias for clearAllFilters (used in templates)
    function resetFilters() {
      clearAllFilters()
      fetch()
    }

    // Returns current filters object (used in templates)
    function getFilters() {
      return { ...filters }
    }

    function setSort(column: string, order: 'asc' | 'desc' = 'asc') {
      sortBy.value = column
      sortOrder.value = order
    }

    function toggleSort(column: string) {
      if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortBy.value = column
        sortOrder.value = 'asc'
      }
    }

    // Alias for toggleSort (used in templates)
    function sort(column: string) {
      toggleSort(column)
      fetch()
    }

    function reset() {
      clearAllFilters()
      sortBy.value = ''
      sortOrder.value = 'asc'
      currentPage.value = 1
    }

    function setConfig(newConfig: IndexConfig) {
      Object.assign(config, newConfig)
      if (newConfig.uri) {
        uri.value = newConfig.uri
      }
    }

    function onSearch() {
      // Debounce search and fetch
      currentPage.value = 1
      fetch()
    }

    async function fetch() {
      if (!uri.value) {
        console.error('IndexStore: No URI configured for fetch')
        return
      }

      fetching.value = true

      try {
        // Build params object, excluding empty values
        const requestParams: any = {
          ...filters,
          ...params,
          per_page: perPage.value,
          page: currentPage.value,
        }

        // Only add sort params if they have values
        if (sortBy.value) {
          requestParams.sort_by = sortBy.value
          requestParams.sort_order = sortOrder.value
        }

        const response: AxiosResponse = await axios.get(uri.value, {
          params: requestParams,
        })

        // Handle different response formats:
        // 1. Paginated: { data: [...], current_page, last_page, ... }
        // 2. Custom: { data: [...], meta: {...}, filters: {...} }
        // 3. Plain array: [...]
        if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
          // Response is an object
          if (Array.isArray(response.data.data)) {
            // Has a data property that is an array
            data.value = response.data
            // Store meta if present
            if (response.data.meta) {
              meta.value = response.data.meta
            } else {
              // For Laravel paginate responses, extract pagination info
              meta.value = {
                current_page: response.data.current_page,
                last_page: response.data.last_page,
                per_page: response.data.per_page,
                total: response.data.total,
              }
            }
            // Store filters if present
            if (response.data.filters) {
              responseFilters.value = response.data.filters
            }
          } else {
            // Object without data array property
            data.value = response.data
          }
        } else if (Array.isArray(response.data)) {
          // Plain array response
          data.value = response.data
        } else {
          data.value = response.data
        }
      } catch (error: any) {
        console.error('IndexStore fetch error:', error)
      } finally {
        fetching.value = false
      }
    }

    // Update or create an item in the data array
    function updateOrCreate(item: any) {
      if (!item || !item.id) return

      const dataArray = Array.isArray(data.value) ? data.value : (data.value?.data || [])
      const index = dataArray.findIndex((i: any) => i.id === item.id)

      if (index !== -1) {
        // Update existing
        dataArray[index] = item
      } else {
        // Create new - add to beginning
        dataArray.unshift(item)
      }

      // Trigger reactivity
      if (Array.isArray(data.value)) {
        data.value = [...dataArray]
      } else if (data.value?.data) {
        data.value = { ...data.value, data: [...dataArray] }
      }
    }

    // Delete an item from the data array
    function deleteIt(id: number | string) {
      const dataArray = Array.isArray(data.value) ? data.value : (data.value?.data || [])
      const filtered = dataArray.filter((i: any) => i.id !== id)

      if (Array.isArray(data.value)) {
        data.value = filtered
      } else if (data.value?.data) {
        data.value = { ...data.value, data: filtered }
      }
    }

    // Alias for fetch (used in templates)
    function get() {
      return fetch()
    }

    return {
      data,
      items,
      meta,
      responseFilters,
      filters,
      params,
      sortBy,
      sortOrder,
      perPage,
      currentPage,
      uri,
      fetching,
      config,
      appliedFilters,
      setFilter,
      clearFilter,
      clearAllFilters,
      resetFilters,
      getFilters,
      setSort,
      toggleSort,
      sort,
      reset,
      setConfig,
      onSearch,
      fetch,
      get,
      updateOrCreate,
      deleteIt,
    }
  })
}
