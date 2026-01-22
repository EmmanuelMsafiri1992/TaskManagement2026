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

      <!-- Company Logo -->
      <div class="mb-6 border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Company Logo') }}</h3>

        <!-- Existing Logo Preview -->
        <div v-if="existingLogo && !logoFile" class="mb-4">
          <div class="flex items-center gap-4">
            <img
              :src="`/storage/${existingLogo}`"
              alt="Company Logo"
              class="h-20 w-20 rounded-lg object-cover border border-gray-200"
            />
            <div>
              <p class="text-sm text-gray-600">{{ __('Current logo') }}</p>
              <button
                type="button"
                class="mt-1 text-sm text-red-600 hover:text-red-800"
                @click="removeExistingLogo"
              >
                {{ __('Remove logo') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Logo Upload -->
        <div
          class="flex justify-center rounded-md border-2 border-dashed border-gray-300 px-6 pb-6 pt-5"
          :class="{ 'border-indigo-500 bg-indigo-50': isDragging }"
          @dragenter.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @dragover.prevent
          @drop.prevent="handleDrop"
        >
          <div class="space-y-1 text-center">
            <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
            <div class="flex text-sm text-gray-600">
              <label
                for="logo-upload"
                class="relative cursor-pointer rounded-md bg-white font-medium text-indigo-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-indigo-500 focus-within:ring-offset-2 hover:text-indigo-500"
              >
                <span>{{ __('Upload a logo') }}</span>
                <input
                  id="logo-upload"
                  ref="fileInput"
                  type="file"
                  class="sr-only"
                  accept=".jpg,.jpeg,.png,.gif,.svg"
                  @change="handleFileChange"
                />
              </label>
              <p class="pl-1">{{ __('or drag and drop') }}</p>
            </div>
            <p class="text-xs text-gray-500">
              {{ __('PNG, JPG, GIF, SVG up to 2MB') }}
            </p>
          </div>
        </div>

        <!-- Selected File Preview -->
        <div v-if="logoFile" class="mt-4">
          <div class="flex items-center gap-4">
            <img
              v-if="logoPreview"
              :src="logoPreview"
              alt="Logo Preview"
              class="h-20 w-20 rounded-lg object-cover border border-gray-200"
            />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">{{ logoFile.name }}</p>
              <p class="text-xs text-gray-500">{{ formatFileSize(logoFile.size) }}</p>
            </div>
            <button
              type="button"
              class="text-sm text-red-600 hover:text-red-800"
              @click="removeLogo"
            >
              {{ __('Remove') }}
            </button>
          </div>
        </div>

        <p v-if="errors.logo" class="mt-2 text-sm text-red-600">{{ errors.logo[0] }}</p>
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
import { PhotoIcon } from '@heroicons/vue/24/outline'
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
const fileInput = ref(null)
const isDragging = ref(false)
const logoFile = ref(null)
const logoPreview = ref(null)
const existingLogo = ref(null)
const removeLogoFlag = ref(false)

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
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml']
  const maxSize = 2 * 1024 * 1024 // 2MB

  if (!allowedTypes.includes(file.type)) {
    alert('Please upload a JPG, PNG, GIF, or SVG file.')
    return
  }

  if (file.size > maxSize) {
    alert('File size must be less than 2MB.')
    return
  }

  logoFile.value = file
  existingLogo.value = null
  removeLogoFlag.value = false

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    logoPreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeLogo = () => {
  logoFile.value = null
  logoPreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeExistingLogo = () => {
  existingLogo.value = null
  removeLogoFlag.value = true
}

const formatFileSize = (bytes) => {
  if (bytes < 1024) return bytes + ' B'
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB'
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB'
}

const submit = async () => {
  processing.value = true
  errors.value = {}

  const formData = new FormData()

  // Add all form fields
  Object.keys(form).forEach(key => {
    if (form[key] !== null && form[key] !== undefined) {
      formData.append(key, form[key])
    }
  })

  // Add logo file if selected
  if (logoFile.value) {
    formData.append('logo_file', logoFile.value)
  }

  // Flag to remove existing logo
  if (removeLogoFlag.value) {
    formData.append('remove_logo', '1')
  }

  try {
    if (props.modelValue) {
      formData.append('_method', 'PUT')
      await axios.post(`companies/${props.modelValue.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      await axios.post('companies', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
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
    existingLogo.value = newVal.logo || null
    logoFile.value = null
    logoPreview.value = null
    removeLogoFlag.value = false
  } else {
    Object.assign(form, getDefaultForm())
    existingLogo.value = null
    logoFile.value = null
    logoPreview.value = null
    removeLogoFlag.value = false
  }
}, { immediate: true })
</script>
