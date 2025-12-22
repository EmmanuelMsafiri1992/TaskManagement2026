import { defineStore } from 'pinia'
import { reactive, ref } from 'vue'
import { axios } from '../axios'
import type { AxiosResponse } from 'axios'

interface FormErrors {
  [key: string]: string[]
}

interface FormData {
  [key: string]: any
}

export const useFormStore = (name: string) => {
  return defineStore(`form-${name}`, () => {
    const data = reactive<FormData>({})
    const errors = reactive<FormErrors>({})
    const options = reactive<FormData>({})
    const submitting = ref(false)
    const fetching = ref(false)
    const uri = ref('')
    const method = ref<'post' | 'put' | 'patch'>('post')
    const id = ref<string | number | null>(null)
    const successCallbacks = ref<Array<(response: AxiosResponse) => void>>([])

    async function init(endpoint: string, itemId?: string | number) {
      uri.value = endpoint
      id.value = itemId || null

      if (itemId) {
        method.value = 'put'
        await fetch(itemId)
      } else {
        // Fetch create form options
        await fetchCreateOptions()
      }
    }

    async function fetchCreateOptions() {
      fetching.value = true

      try {
        const response: AxiosResponse = await axios.get(`${uri.value}/create`)

        // Check if response is an array of field objects (from Field::make())
        if (Array.isArray(response.data)) {
          response.data.forEach((field: any) => {
            if (field.name && field.value !== undefined) {
              data[field.name] = field.value
            }
            if (field.name && field.options && field.options.length > 0) {
              options[field.name] = field.options
            }
          })
        } else {
          // Legacy format support
          if (response.data.options) {
            Object.assign(options, response.data.options)
          }
          if (response.data.data) {
            Object.assign(data, response.data.data)
          }
        }
      } catch (error: any) {
        console.error('Form create options fetch error:', error)
      } finally {
        fetching.value = false
      }
    }

    async function fetch(itemId: string | number) {
      fetching.value = true
      clearErrors()

      try {
        const response: AxiosResponse = await axios.get(`${uri.value}/${itemId}/edit`)

        // Check if response is an array of field objects (from Field::make())
        if (Array.isArray(response.data)) {
          response.data.forEach((field: any) => {
            if (field.name && field.value !== undefined) {
              data[field.name] = field.value
            }
            if (field.name && field.options && field.options.length > 0) {
              options[field.name] = field.options
            }
          })
        } else {
          // Legacy format support
          Object.assign(data, response.data.data || response.data)
          if (response.data.options) {
            Object.assign(options, response.data.options)
          }
        }
      } catch (error: any) {
        console.error('Form fetch error:', error)
      } finally {
        fetching.value = false
      }
    }

    async function submit() {
      submitting.value = true
      clearErrors()

      try {
        const endpoint = id.value ? `${uri.value}/${id.value}` : uri.value
        const response: AxiosResponse = await axios[method.value](endpoint, data)

        submitting.value = false

        // Call all success callbacks
        successCallbacks.value.forEach(callback => callback(response))

        return response
      } catch (error: any) {
        submitting.value = false

        if (error.response?.data?.errors) {
          Object.assign(errors, error.response.data.errors)
        }

        throw error
      }
    }

    function onSuccess(callback: (response: AxiosResponse) => void) {
      successCallbacks.value.push(callback)
    }

    function clearErrors() {
      Object.keys(errors).forEach(key => delete errors[key])
    }

    function setData(key: string, value: any) {
      data[key] = value
    }

    function reset() {
      Object.keys(data).forEach(key => delete data[key])
      clearErrors()
      submitting.value = false
      fetching.value = false
    }

    return {
      data,
      errors,
      options,
      submitting,
      fetching,
      init,
      fetch,
      submit,
      onSuccess,
      clearErrors,
      setData,
      reset,
    }
  })
}
