<template>
  <div>
    <div class="border-b border-gray-200 px-6 py-4">
      <h2 class="text-lg font-semibold text-gray-900">
        {{ modelValue ? __('Edit Company') : __('Create Company') }}
      </h2>
    </div>

    <div class="max-h-[70vh] overflow-y-auto px-6 py-4">
      <!-- Basic Information -->
      <div class="mb-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Basic Information') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Company Name') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.name }"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Trading Name') }}</label>
            <input
              v-model="form.trading_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Registration Number') }}</label>
            <input
              v-model="form.registration_number"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Tax Number') }}</label>
            <input
              v-model="form.tax_number"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Industry') }}</label>
            <input
              v-model="form.industry"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('e.g., Technology, Healthcare, etc.')"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
            <select
              v-model="form.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="active">{{ __('Active') }}</option>
              <option value="inactive">{{ __('Inactive') }}</option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
          <textarea
            v-model="form.description"
            rows="2"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>
      </div>

      <!-- Contact Information -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Contact Information') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
            <input
              v-model="form.phone"
              type="tel"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Secondary Phone') }}</label>
            <input
              v-model="form.secondary_phone"
              type="tel"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Fax') }}</label>
            <input
              v-model="form.fax"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('Website') }}</label>
            <input
              v-model="form.website"
              type="url"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="https://"
            />
          </div>
        </div>
      </div>

      <!-- Address -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Address') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('Street Address') }}</label>
            <textarea
              v-model="form.address"
              rows="2"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('City') }}</label>
            <input
              v-model="form.city"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('State/Province') }}</label>
            <input
              v-model="form.state"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Postal Code') }}</label>
            <input
              v-model="form.postal_code"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Country') }}</label>
            <input
              v-model="form.country"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Banking Information -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Banking Information') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Bank Name') }}</label>
            <input
              v-model="form.bank_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Account Name') }}</label>
            <input
              v-model="form.bank_account_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Account Number') }}</label>
            <input
              v-model="form.bank_account_number"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
            <input
              v-model="form.bank_branch"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('SWIFT Code') }}</label>
            <input
              v-model="form.bank_swift_code"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="border-t border-gray-200 pt-6">
        <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
        <textarea
          v-model="form.notes"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :placeholder="__('Internal notes about this company')"
        ></textarea>
      </div>
    </div>

    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
      <TheButton variant="secondary" @click="$emit('close')">
        {{ __('Cancel') }}
      </TheButton>
      <TheButton :disabled="processing" @click="submit">
        {{ modelValue ? __('Update Company') : __('Create Company') }}
      </TheButton>
    </div>
  </div>
</template>

<script setup>
import { inject, reactive, ref, watch } from 'vue'
import { axios } from 'spack/axios'
import TheButton from '@/thetheme/components/TheButton.vue'

const __ = inject('__')

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const processing = ref(false)
const errors = ref({})

const getDefaultForm = () => ({
  name: '',
  trading_name: '',
  registration_number: '',
  tax_number: '',
  email: '',
  phone: '',
  secondary_phone: '',
  fax: '',
  website: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
  industry: '',
  description: '',
  bank_name: '',
  bank_account_name: '',
  bank_account_number: '',
  bank_branch: '',
  bank_swift_code: '',
  status: 'active',
  notes: '',
})

const form = reactive(getDefaultForm())

const submit = async () => {
  processing.value = true
  errors.value = {}

  const url = props.modelValue
    ? `companies/${props.modelValue.id}`
    : 'companies'

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
    } else {
      alert('Error: ' + (error.response?.data?.message || error.message || 'Unknown error'))
    }
    console.error('Failed to save company:', error)
  } finally {
    processing.value = false
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    Object.assign(form, {
      name: newVal.name || '',
      trading_name: newVal.trading_name || '',
      registration_number: newVal.registration_number || '',
      tax_number: newVal.tax_number || '',
      email: newVal.email || '',
      phone: newVal.phone || '',
      secondary_phone: newVal.secondary_phone || '',
      fax: newVal.fax || '',
      website: newVal.website || '',
      address: newVal.address || '',
      city: newVal.city || '',
      state: newVal.state || '',
      postal_code: newVal.postal_code || '',
      country: newVal.country || '',
      industry: newVal.industry || '',
      description: newVal.description || '',
      bank_name: newVal.bank_name || '',
      bank_account_name: newVal.bank_account_name || '',
      bank_account_number: newVal.bank_account_number || '',
      bank_branch: newVal.bank_branch || '',
      bank_swift_code: newVal.bank_swift_code || '',
      status: newVal.status || 'active',
      notes: newVal.notes || '',
    })
  } else {
    Object.assign(form, getDefaultForm())
  }
}, { immediate: true })
</script>
