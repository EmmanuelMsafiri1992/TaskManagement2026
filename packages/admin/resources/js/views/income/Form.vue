<template>
  <FormBase :external-form="form" @submit="submit" @cancel="emit('close')">
    <template #title>
      {{ form.id ? __('Edit Income') : __('Add Income') }}
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

      <!-- Income Date -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Income Date') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.income_date"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.income_date }"
            required
          />
          <p v-if="form.errors.income_date" class="mt-2 text-sm text-red-600">
            {{ form.errors.income_date }}
          </p>
        </div>
      </div>

      <!-- Source -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Source') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.source"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.source }"
            required
          >
            <option value="">{{ __('Select source') }}</option>
            <option value="sales">{{ __('Sales') }}</option>
            <option value="services">{{ __('Services') }}</option>
            <option value="consulting">{{ __('Consulting') }}</option>
            <option value="adsense">{{ __('AdSense') }}</option>
            <option value="quotation">{{ __('Quotation') }}</option>
            <option value="other">{{ __('Other') }}</option>
          </select>
          <p v-if="form.errors.source" class="mt-2 text-sm text-red-600">
            {{ form.errors.source }}
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
          <textarea
            v-model="form.description"
            rows="3"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.description }"
            :placeholder="__('Description of the income')"
            required
          ></textarea>
          <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
            {{ form.errors.description }}
          </p>
        </div>
      </div>

      <!-- Invoice Number -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Invoice Number') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.invoice_number"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="__('Invoice or reference number (optional)')"
          />
        </div>
      </div>

      <!-- Client Name -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Client Name') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.client_name"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="__('Client or payer name (optional)')"
          />
        </div>
      </div>

      <!-- Related Quotation -->
      <div v-if="quotations.length > 0" class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Related Quotation') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.quotation_id"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option :value="null">{{ __('None') }}</option>
            <option v-for="q in quotations" :key="q.id" :value="q.id">
              {{ q.quotation_number || `#${q.id}` }} - {{ q.client?.name || 'N/A' }} ({{ q.currency }} {{ formatNumber(q.total) }})
            </option>
          </select>
        </div>
      </div>

      <!-- Status -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Status') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.status"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="pending">{{ __('Pending') }}</option>
            <option value="received">{{ __('Received') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
          </select>
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
            :placeholder="__('Additional notes (optional)')"
          ></textarea>
        </div>
      </div>
    </div>
  </FormBase>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
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

const showCustomCategory = ref(false)
const quotations = ref([])

const defaultCategories = [
  'Product Sales',
  'Service Revenue',
  'Consulting Fees',
  'Advertising Revenue',
  'Subscription Income',
  'Commission',
  'Rental Income',
  'Interest Income',
  'Royalties',
  'Miscellaneous'
]

const form = useForm('income', {
  amount: '',
  currency: 'MWK',
  income_date: new Date().toISOString().split('T')[0],
  source: '',
  category: '',
  description: '',
  invoice_number: '',
  client_name: '',
  quotation_id: null,
  status: 'pending',
  notes: ''
})

const toggleCustomCategory = () => {
  showCustomCategory.value = !showCustomCategory.value
  if (!showCustomCategory.value) {
    form.category = ''
  }
}

const formatNumber = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const loadQuotations = async () => {
  try {
    const response = await axios.get('quotations', { params: { per_page: 100 } })
    quotations.value = response.data.data || []
  } catch (error) {
    console.error('Failed to load quotations:', error)
  }
}

const submit = async () => {
  try {
    form.processing = true
    form.errors = {}

    const data = {
      amount: form.amount,
      currency: form.currency,
      income_date: form.income_date,
      source: form.source,
      category: form.category || null,
      description: form.description,
      invoice_number: form.invoice_number || null,
      client_name: form.client_name || null,
      quotation_id: form.quotation_id || null,
      status: form.status,
      notes: form.notes || null
    }

    if (form.id) {
      await axios.put(`income/${form.id}`, data)
    } else {
      await axios.post('income', data)
    }

    emit('close')
  } catch (error) {
    if (error.response?.status === 422) {
      form.errors = error.response.data.errors || {}
    } else {
      console.error('Failed to save income:', error)
      alert(error.response?.data?.message || 'Failed to save income')
    }
  } finally {
    form.processing = false
  }
}

watch(() => props.modelValue, (income) => {
  if (income) {
    form.id = income.id
    form.amount = income.amount
    form.currency = income.currency || 'MWK'
    form.income_date = income.income_date?.split('T')[0] || ''
    form.source = income.source || ''
    form.category = income.category || ''
    form.description = income.description || ''
    form.invoice_number = income.invoice_number || ''
    form.client_name = income.client_name || ''
    form.quotation_id = income.quotation_id || null
    form.status = income.status || 'pending'
    form.notes = income.notes || ''

    // Check if category is custom
    if (income.category && !defaultCategories.includes(income.category)) {
      showCustomCategory.value = true
    }
  } else {
    form.reset()
    form.currency = 'MWK'
    form.income_date = new Date().toISOString().split('T')[0]
    form.status = 'pending'
    showCustomCategory.value = false
  }
}, { immediate: true })

onMounted(() => {
  loadQuotations()
})
</script>
