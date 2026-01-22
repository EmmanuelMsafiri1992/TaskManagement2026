<template>
  <div>
    <div class="border-b border-gray-200 px-6 py-4">
      <h2 class="text-lg font-semibold text-gray-900">
        {{ modelValue ? __('Edit Quotation') : __('Create Quotation') }}
      </h2>
    </div>

    <div class="max-h-[70vh] overflow-y-auto px-6 py-4">
      <!-- Customer Information -->
      <div class="mb-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Customer Information') }}</h3>

        <!-- Client Selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">{{ __('Select Client') }}</label>
          <select
            v-model="selectedClientId"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="onClientSelect"
          >
            <option value="">{{ __('-- Select a registered client or enter manually --') }}</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
              {{ client.name }}{{ client.company_name ? ` (${client.company_name})` : '' }}
            </option>
          </select>
        </div>

        <!-- Company Selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">{{ __('Select Company') }}</label>
          <select
            v-model="selectedCompanyId"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="onCompanySelect"
          >
            <option value="">{{ __('-- Select a company to auto-fill business details --') }}</option>
            <option v-for="company in companies" :key="company.id" :value="company.id">
              {{ company.name }}{{ company.trading_name ? ` (${company.trading_name})` : '' }}
            </option>
          </select>
          <p class="mt-1 text-xs text-gray-500">{{ __('Selecting a company will auto-fill the business information below') }}</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Customer Name') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.customer_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.customer_name }"
            />
            <p v-if="errors.customer_name" class="mt-1 text-sm text-red-600">{{ errors.customer_name[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Email') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.customer_email"
              type="email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.customer_email }"
            />
            <p v-if="errors.customer_email" class="mt-1 text-sm text-red-600">{{ errors.customer_email[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
            <input
              v-model="form.customer_phone"
              type="tel"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
            <input
              v-model="form.customer_address"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Business Information -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Business Information') }}</h3>
        <p class="mb-4 text-sm text-gray-500">{{ __('This information will appear on the quotation as your company details') }}</p>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Business Name') }}</label>
            <input
              v-model="form.business_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Your company name')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Business Email') }}</label>
            <input
              v-model="form.business_email"
              type="email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('company@email.com')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Business Phone') }}</label>
            <input
              v-model="form.business_phone"
              type="tel"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Business phone number')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Business Address') }}</label>
            <input
              v-model="form.business_address"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Business address')"
            />
          </div>
        </div>
      </div>

      <!-- Quotation Details -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Quotation Details') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Quotation Date') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.quotation_date"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.quotation_date }"
            />
            <p v-if="errors.quotation_date" class="mt-1 text-sm text-red-600">{{ errors.quotation_date[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Valid Until') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.valid_until"
              type="date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.valid_until }"
            />
            <p v-if="errors.valid_until" class="mt-1 text-sm text-red-600">{{ errors.valid_until[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Currency') }}</label>
            <select
              v-model="form.currency"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="MWK">MWK - Malawian Kwacha</option>
              <option value="USD">USD - US Dollar</option>
              <option value="EUR">EUR - Euro</option>
              <option value="GBP">GBP - British Pound</option>
              <option value="ZAR">ZAR - South African Rand</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Template') }}</label>
            <select
              v-model="form.template"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="style_1">{{ __('Style 1 - Classic') }}</option>
              <option value="style_2">{{ __('Style 2 - Modern') }}</option>
              <option value="style_3">{{ __('Style 3 - Colorful Header') }}</option>
              <option value="style_4">{{ __('Style 4 - Bordered') }}</option>
              <option value="style_5">{{ __('Style 5 - Info Banner') }}</option>
              <option value="style_6">{{ __('Style 6 - Minimal') }}</option>
              <option value="style_7">{{ __('Style 7 - Split Header') }}</option>
              <option value="style_8">{{ __('Style 8 - Elegant') }}</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-md font-medium text-gray-900">{{ __('Items') }}</h3>
          <button
            type="button"
            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            @click="addItem"
          >
            <PlusIcon class="mr-1 h-4 w-4" />
            {{ __('Add Item') }}
          </button>
        </div>

        <div class="overflow-hidden rounded-lg border border-gray-200">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  {{ __('Description') }}
                </th>
                <th class="w-24 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  {{ __('Qty') }}
                </th>
                <th class="w-32 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  {{ __('Unit Price') }}
                </th>
                <th class="w-32 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  {{ __('Total') }}
                </th>
                <th class="w-16 px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(item, index) in form.items" :key="index">
                <td class="px-4 py-2">
                  <input
                    v-model="item.description"
                    type="text"
                    :placeholder="__('Item description *')"
                    class="block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    :class="errors[`items.${index}.description`] ? 'border-red-300 bg-red-50' : 'border-gray-300'"
                  />
                  <p v-if="errors[`items.${index}.description`]" class="mt-1 text-xs text-red-600">
                    {{ __('Description is required') }}
                  </p>
                </td>
                <td class="px-4 py-2">
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @input="calculateItemTotal(index)"
                  />
                </td>
                <td class="px-4 py-2">
                  <input
                    v-model.number="item.unit_price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    @input="calculateItemTotal(index)"
                  />
                </td>
                <td class="px-4 py-2 text-sm font-medium text-gray-900">
                  {{ formatNumber(item.quantity * item.unit_price) }}
                </td>
                <td class="px-4 py-2">
                  <button
                    v-if="form.items.length > 1"
                    type="button"
                    class="text-red-500 hover:text-red-700"
                    @click="removeItem(index)"
                  >
                    <TrashIcon class="h-5 w-5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <p v-if="errors.items" class="mt-2 text-sm text-red-600">{{ errors.items[0] }}</p>
      </div>

      <!-- Totals -->
      <div class="mb-6 flex justify-end">
        <div class="w-80 space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">{{ __('Subtotal') }}:</span>
            <span class="font-medium">{{ form.currency }} {{ formatNumber(subtotal) }}</span>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">{{ __('Tax Rate') }} (%):</span>
            <input
              v-model.number="form.tax_rate"
              type="number"
              min="0"
              max="100"
              step="0.5"
              class="w-20 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="flex justify-between text-sm">
            <span class="text-gray-600">{{ __('Tax Amount') }}:</span>
            <span class="font-medium">{{ form.currency }} {{ formatNumber(taxAmount) }}</span>
          </div>

          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-600">{{ __('Discount') }}:</span>
            <input
              v-model.number="form.discount_amount"
              type="number"
              min="0"
              step="0.01"
              class="w-24 rounded-md border-gray-300 text-right shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-semibold">
            <span>{{ __('Total') }}:</span>
            <span class="text-indigo-600">{{ form.currency }} {{ formatNumber(grandTotal) }}</span>
          </div>
        </div>
      </div>

      <!-- Notes & Terms -->
      <div class="border-t border-gray-200 pt-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Additional notes for the customer')"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Terms & Conditions') }}</label>
            <textarea
              v-model="form.terms"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Terms and conditions')"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Status (for edit mode) -->
      <div v-if="modelValue" class="mt-6 border-t border-gray-200 pt-6">
        <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
        <select
          v-model="form.status"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option value="draft">{{ __('Draft') }}</option>
          <option value="sent">{{ __('Sent') }}</option>
          <option value="accepted">{{ __('Accepted') }}</option>
          <option value="rejected">{{ __('Rejected') }}</option>
          <option value="expired">{{ __('Expired') }}</option>
        </select>
      </div>
    </div>

    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
      <TheButton variant="secondary" @click="$emit('close')">
        {{ __('Cancel') }}
      </TheButton>
      <TheButton :disabled="processing" @click="submit">
        {{ modelValue ? __('Update Quotation') : __('Create Quotation') }}
      </TheButton>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import TheButton from '@/thetheme/components/TheButton.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const processing = ref(false)
const errors = ref({})
const clients = ref([])
const companies = ref([])
const selectedClientId = ref('')
const selectedCompanyId = ref('')

// Fetch clients and companies on mount
onMounted(async () => {
  try {
    const [clientsResponse, companiesResponse] = await Promise.all([
      axios.get('clients/options'),
      axios.get('companies/options')
    ])
    clients.value = clientsResponse.data.data || []
    companies.value = companiesResponse.data.data || []
  } catch (error) {
    console.error('Failed to fetch data:', error)
  }
})

// Handle client selection
const onClientSelect = () => {
  if (selectedClientId.value) {
    const client = clients.value.find(c => c.id === parseInt(selectedClientId.value))
    if (client) {
      form.client_id = client.id
      form.customer_name = client.name || ''
      form.customer_email = client.email || ''
      form.customer_phone = client.phone || ''
      form.customer_address = client.address || ''
    }
  } else {
    form.client_id = null
  }
}

// Handle company selection - auto-populate business fields
const onCompanySelect = async () => {
  if (selectedCompanyId.value) {
    try {
      const response = await axios.get(`companies/${selectedCompanyId.value}/quotation-details`)
      const companyData = response.data.data
      if (companyData) {
        form.company_id = companyData.id
        form.business_name = companyData.business_name || ''
        form.business_email = companyData.business_email || ''
        form.business_phone = companyData.business_phone || ''
        form.business_address = companyData.business_address || ''
        if (companyData.logo) {
          form.logo = companyData.logo
        }
      }
    } catch (error) {
      console.error('Failed to fetch company details:', error)
    }
  } else {
    form.company_id = null
  }
}

const getDefaultItem = () => ({
  description: '',
  quantity: 1,
  unit_price: 0
})

const form = reactive({
  client_id: null,
  company_id: null,
  customer_name: '',
  customer_email: '',
  customer_phone: '',
  customer_address: '',
  business_name: '',
  business_email: '',
  business_phone: '',
  business_address: '',
  logo: '',
  quotation_date: new Date().toISOString().split('T')[0],
  valid_until: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  currency: 'MWK',
  tax_rate: 16.5,
  discount_amount: 0,
  template: 'style_1',
  notes: '',
  terms: 'Payment is due within 14 days of acceptance.\nPrices are valid for 30 days from quotation date.',
  status: 'draft',
  items: [getDefaultItem()]
})

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + (item.quantity * item.unit_price)
  }, 0)
})

const taxAmount = computed(() => {
  return subtotal.value * (form.tax_rate / 100)
})

const grandTotal = computed(() => {
  return subtotal.value + taxAmount.value - form.discount_amount
})

const addItem = () => {
  form.items.push(getDefaultItem())
}

const removeItem = (index) => {
  if (form.items.length > 1) {
    form.items.splice(index, 1)
  }
}

const calculateItemTotal = (index) => {
  const item = form.items[index]
  item.total = item.quantity * item.unit_price
}

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const submit = async () => {
  processing.value = true
  errors.value = {}

  const url = props.modelValue
    ? `quotations/${props.modelValue.id}`
    : 'quotations'

  try {
    if (props.modelValue) {
      await axios.put(url, form)
    } else {
      await axios.post(url, form)
    }
    emit('saved')
    emit('close')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
      alert('Validation errors:\n' + Object.values(error.response.data.errors).flat().join('\n'))
    } else if (error.response?.data?.error) {
      alert('Server error:\n' + error.response.data.error + '\nFile: ' + error.response.data.file + '\nLine: ' + error.response.data.line)
    } else {
      alert('Error: ' + (error.message || 'Unknown error'))
    }
    console.error('Failed to save quotation:', error)
  } finally {
    processing.value = false
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    Object.assign(form, {
      client_id: newVal.client_id || null,
      company_id: newVal.company_id || null,
      customer_name: newVal.customer_name || '',
      customer_email: newVal.customer_email || '',
      customer_phone: newVal.customer_phone || '',
      customer_address: newVal.customer_address || '',
      business_name: newVal.business_name || '',
      business_email: newVal.business_email || '',
      business_phone: newVal.business_phone || '',
      business_address: newVal.business_address || '',
      logo: newVal.logo || '',
      quotation_date: newVal.quotation_date ? newVal.quotation_date.split('T')[0] : new Date().toISOString().split('T')[0],
      valid_until: newVal.valid_until ? newVal.valid_until.split('T')[0] : new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
      currency: newVal.currency || 'MWK',
      tax_rate: parseFloat(newVal.tax_rate) || 16.5,
      discount_amount: parseFloat(newVal.discount_amount) || 0,
      template: newVal.template || 'style_1',
      notes: newVal.notes || '',
      terms: newVal.terms || '',
      status: newVal.status || 'draft',
      items: newVal.items?.length ? newVal.items.map(item => ({
        description: item.description,
        quantity: item.quantity,
        unit_price: parseFloat(item.unit_price)
      })) : [getDefaultItem()]
    })
    // Set selected client if exists
    if (newVal.client_id) {
      selectedClientId.value = newVal.client_id.toString()
    }
    // Set selected company if exists
    if (newVal.company_id) {
      selectedCompanyId.value = newVal.company_id.toString()
    }
  }
}, { immediate: true })
</script>
