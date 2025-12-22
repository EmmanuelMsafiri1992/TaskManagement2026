import { defineStore } from 'pinia'
import { reactive, ref } from 'vue'
import { axios } from '../axios'
import type { AxiosResponse } from 'axios'

interface IndexFilters {
  [key: string]: any
}

interface IndexConfig {
  uri?: string
  [key: string]: any
}

export const useIndexStore = (name: string) => {
  return defineStore(`index-${name}`, () => {
    const data = ref<any[]>([])
    const filters = reactive<IndexFilters>({})
    const params = reactive<IndexFilters>({})
    const sortBy = ref<string>('')
    const sortOrder = ref<'asc' | 'desc'>('asc')
    const perPage = ref<number>(15)
    const currentPage = ref<number>(1)
    const uri = ref<string>('')
    const fetching = ref<boolean>(false)
    const config = reactive<IndexConfig>({})

    function setFilter(key: string, value: any) {
      filters[key] = value
    }

    function clearFilter(key: string) {
      delete filters[key]
    }

    function clearAllFilters() {
      Object.keys(filters).forEach(key => delete filters[key])
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

        data.value = response.data
      } catch (error: any) {
        console.error('IndexStore fetch error:', error)
      } finally {
        fetching.value = false
      }
    }

    return {
      data,
      filters,
      params,
      sortBy,
      sortOrder,
      perPage,
      currentPage,
      uri,
      fetching,
      config,
      setFilter,
      clearFilter,
      clearAllFilters,
      setSort,
      toggleSort,
      reset,
      setConfig,
      onSearch,
      fetch,
    }
  })
}
