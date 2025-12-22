import { reactive, ref } from 'vue'
import axios from 'axios'

export const useForm = (initialData: Record<string, any> = {}) => {
  const data = reactive({ ...initialData })
  const errors = ref<Record<string, string[]>>({})
  const processing = ref(false)

  const submit = async (method: 'post' | 'put' | 'patch' | 'delete', url: string, config: any = {}) => {
    processing.value = true
    errors.value = {}

    try {
      const response = await axios[method](url, data, config)

      if (config.onSuccess) {
        config.onSuccess(response.data)
      }

      return response
    } catch (error: any) {
      if (error.response?.data?.errors) {
        errors.value = error.response.data.errors
      }

      if (config.onError) {
        config.onError(error)
      }

      throw error
    } finally {
      processing.value = false
    }
  }

  const reset = () => {
    Object.assign(data, initialData)
    errors.value = {}
  }

  return {
    data,
    errors,
    processing,
    submit,
    reset,
    post: (url: string, config?: any) => submit('post', url, config),
    put: (url: string, config?: any) => submit('put', url, config),
    patch: (url: string, config?: any) => submit('patch', url, config),
    delete: (url: string, config?: any) => submit('delete', url, config),
  }
}
