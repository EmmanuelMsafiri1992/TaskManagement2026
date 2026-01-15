<template>
  <FormBase :external-form="form" @submit="submit" @cancel="emit('close')">
    <template #title>
      {{ form.id ? __('Edit Expense') : __('Add Expense') }}
    </template>

    <div class="space-y-6">
      <!-- Amount and Currency -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Amount') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div class="flex gap-2">
            <select
              v-model="form.currency"
              class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="MWK">MWK</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
              <option value="ZAR">ZAR</option>
            </select>
            <input
              v-model="form.amount"
              type="number"
              step="0.01"
              min="0"
              class="block flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.amount }"
              :placeholder="__('Enter amount')"
              required
            />
          </div>
          <p v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
            {{ form.errors.amount }}
          </p>
        </div>
      </div>

      <!-- Expense Date -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Expense Date') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.expense_date"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.expense_date }"
            required
          />
          <p v-if="form.errors.expense_date" class="mt-2 text-sm text-red-600">
            {{ form.errors.expense_date }}
          </p>
        </div>
      </div>

      <!-- Category -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Category') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div class="flex gap-2">
            <select
              v-if="!showCustomCategory"
              v-model="form.category"
              class="block flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Select category') }}</option>
              <option v-for="cat in defaultCategories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <input
              v-else
              v-model="form.category"
              type="text"
              class="block flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Enter custom category')"
            />
            <button
              type="button"
              class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
              @click="toggleCustomCategory"
            >
              {{ showCustomCategory ? __('Select') : __('Custom') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Description') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.description"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.description }"
            :placeholder="__('Brief description of the expense')"
            required
          />
          <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
            {{ form.errors.description }}
          </p>
        </div>
      </div>

      <!-- Notes -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Notes') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <textarea
            v-model="form.notes"
            rows="3"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="__('Additional notes or details (optional)')"
          ></textarea>
        </div>
      </div>

      <!-- Receipt Upload -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Receipt') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div v-if="existingReceipt && !form.receipt" class="mb-2 flex items-center justify-between rounded-md border border-gray-200 bg-gray-50 p-2">
            <div class="flex items-center">
              <DocumentIcon class="mr-2 h-5 w-5 text-gray-400" />
              <span class="text-sm text-gray-700">{{ existingReceipt }}</span>
            </div>
            <button
              type="button"
              class="text-sm text-red-600 hover:text-red-800"
              @click="removeExistingReceipt"
            >
              {{ __('Remove') }}
            </button>
          </div>

          <div
            class="flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5"
            :class="{ 'border-indigo-500 bg-indigo-50': isDragging }"
            @dragenter.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @dragover.prevent
            @drop.prevent="handleDrop"
          >
            <div class="space-y-1 text-center">
              <ArrowUpTrayIcon class="mx-auto h-12 w-12 text-gray-400" />
              <div class="flex text-sm text-gray-600">
                <label
                  for="receipt-upload"
                  class="relative cursor-pointer rounded-md bg-white font-medium text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-500"
                >
                  <span>{{ __('Upload a file') }}</span>
                  <input
                    id="receipt-upload"
                    ref="fileInput"
                    type="file"
                    class="sr-only"
                    accept=".pdf,.jpg,.jpeg,.png"
                    @change="handleFileChange"
                  />
                </label>
                <p class="pl-1">{{ __('or drag and drop') }}</p>
              </div>
              <p class="text-xs text-gray-500">
                {{ __('PDF, JPG, JPEG, PNG up to 5MB') }}
              </p>
            </div>
          </div>

          <div v-if="form.receipt" class="mt-2 flex items-center justify-between rounded-md border border-green-200 bg-green-50 p-2">
            <div class="flex items-center">
              <DocumentIcon class="mr-2 h-5 w-5 text-green-600" />
              <span class="text-sm text-green-700">{{ form.receipt.name }}</span>
              <span class="ml-2 text-xs text-green-600">({{ formatFileSize(form.receipt.size) }})</span>
            </div>
            <button
              type="button"
              class="text-sm text-red-600 hover:text-red-800"
              @click="removeFile"
            >
              {{ __('Remove') }}
            </button>
          </div>

          <p v-if="form.errors.receipt" class="mt-2 text-sm text-red-600">
            {{ form.errors.receipt }}
          </p>
        </div>
      </div>
    </div>
  </FormBase>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { ArrowUpTrayIcon, DocumentIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import FormBase from '@/thetheme/components/FormBase.vue'
import { useForm } from '@/composables/useForm'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const fileInput = ref(null)
const isDragging = ref(false)
const showCustomCategory = ref(false)
const existingReceipt = ref(null)

const defaultCategories = [
  'Travel',
  'Meals & Entertainment',
  'Office Supplies',
  'Equipment',
  'Software & Subscriptions',
  'Professional Services',
  'Utilities',
  'Marketing',
  'Training & Education',
  'Transportation',
  'Accommodation',
  'Communication',
  'Miscellaneous'
]

const form = useForm('expenses', {
  amount: '',
  currency: 'MWK',
  expense_date: new Date().toISOString().split('T')[0],
  category: '',
  description: '',
  notes: '',
  receipt: null
})

const toggleCustomCategory = () => {
  showCustomCategory.value = !showCustomCategory.value
  if (!showCustomCategory.value) {
    form.category = ''
  }
}

const handleFileChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

const handleDrop = (e) => {
  isDragging.value = false
  const file = e.dataTransfer.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

const validateAndSetFile = (file) => {
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']
  const maxSize = 5 * 1024 * 1024 // 5MB

  if (!allowedTypes.includes(file.type)) {
    alert('Please upload a PDF, JPG, JPEG, or PNG file.')
    return
  }

  if (file.size > maxSize) {
    alert('File size must be less than 5MB.')
    return
  }

  form.receipt = file
  existingReceipt.value = null
}

const removeFile = () => {
  form.receipt = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeExistingReceipt = () => {
  existingReceipt.value = null
}

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

const submit = async () => {
  const formData = new FormData()
  formData.append('amount', form.amount)
  formData.append('currency', form.currency)
  formData.append('expense_date', form.expense_date)
  formData.append('category', form.category || '')
  formData.append('description', form.description)
  formData.append('notes', form.notes || '')

  if (form.receipt) {
    formData.append('receipt', form.receipt)
  }

  try {
    form.processing = true
    form.errors = {}

    let response
    if (form.id) {
      formData.append('_method', 'PUT')
      response = await axios.post(`expenses/${form.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      response = await axios.post('expenses', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }

    emit('close')
  } catch (error) {
    if (error.response?.status === 422) {
      form.errors = error.response.data.errors || {}
    } else {
      console.error('Failed to save expense:', error)
      alert(error.response?.data?.message || 'Failed to save expense')
    }
  } finally {
    form.processing = false
  }
}

watch(() => props.modelValue, (expense) => {
  if (expense) {
    form.id = expense.id
    form.amount = expense.amount
    form.currency = expense.currency || 'MWK'
    form.expense_date = expense.expense_date?.split('T')[0] || ''
    form.category = expense.category || ''
    form.description = expense.description || ''
    form.notes = expense.notes || ''
    form.receipt = null
    existingReceipt.value = expense.receipt_name || null

    // Check if category is custom
    if (expense.category && !defaultCategories.includes(expense.category)) {
      showCustomCategory.value = true
    }
  } else {
    form.reset()
    form.currency = 'MWK'
    form.expense_date = new Date().toISOString().split('T')[0]
    existingReceipt.value = null
    showCustomCategory.value = false
  }
}, { immediate: true })
</script>
