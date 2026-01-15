import { reactive, ref } from 'vue'
import { axios } from 'spack/axios'

export const useForm = (endpoint: string, initialData: Record<string, any> = {}) => {
  const formData = reactive<Record<string, any>>({ ...initialData })
  const errors = reactive<Record<string, string>>({})
  const processing = ref(false)
  let currentId: number | string | null = null

  const submit = async (onSuccess?: () => void) => {
    processing.value = true
    // Clear errors
    Object.keys(errors).forEach(key => delete errors[key])

    try {
      const method = currentId ? 'put' : 'post'
      const url = currentId ? `${endpoint}/${currentId}` : endpoint
      const response = await axios[method](url, formData)

      if (onSuccess) {
        onSuccess()
      }

      return response
    } catch (error: any) {
      if (error.response?.data?.errors) {
        const responseErrors = error.response.data.errors
        Object.keys(responseErrors).forEach(key => {
          errors[key] = Array.isArray(responseErrors[key])
            ? responseErrors[key][0]
            : responseErrors[key]
        })
      }
      throw error
    } finally {
      processing.value = false
    }
  }

  const setData = (data: Record<string, any>) => {
    if (data.id) {
      currentId = data.id
    }
    Object.keys(data).forEach(key => {
      if (key in formData || key === 'id') {
        formData[key] = data[key]
      }
    })
  }

  const reset = () => {
    currentId = null
    Object.keys(initialData).forEach(key => {
      formData[key] = initialData[key]
    })
    Object.keys(errors).forEach(key => delete errors[key])
  }

  // Return a proxy that allows direct access to form fields
  return new Proxy(formData, {
    get(target, prop) {
      if (prop === 'submit') return submit
      if (prop === 'setData') return setData
      if (prop === 'reset') return reset
      if (prop === 'errors') return errors
      if (prop === 'processing') return processing
      if (prop === 'id') return currentId
      return target[prop as string]
    },
    set(target, prop, value) {
      target[prop as string] = value
      return true
    }
  }) as Record<string, any> & {
    submit: (onSuccess?: () => void) => Promise<any>
    setData: (data: Record<string, any>) => void
    reset: () => void
    errors: Record<string, string>
    processing: { value: boolean }
    id: number | string | null
  }
}
