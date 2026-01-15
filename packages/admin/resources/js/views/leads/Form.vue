<template>
  <div>
    <div class="border-b border-gray-200 px-6 py-4">
      <h2 class="text-lg font-semibold text-gray-900">
        {{ modelValue ? __('Edit Lead') : __('Add Lead') }}
      </h2>
    </div>

    <div class="max-h-[70vh] space-y-6 overflow-y-auto px-6 py-4">
      <!-- Contact Information -->
      <div>
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Contact Information') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('First Name') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.first_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.first_name }"
              required
            />
            <p v-if="errors.first_name" class="mt-1 text-sm text-red-600">{{ errors.first_name[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Last Name') }}</label>
            <input
              v-model="form.last_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Email') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.email"
              type="email"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.email }"
              required
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</p>
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
            <label class="block text-sm font-medium text-gray-700">{{ __('Company') }}</label>
            <input
              v-model="form.company_name"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Job Title') }}</label>
            <input
              v-model="form.job_title"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Website') }}</label>
            <input
              v-model="form.website"
              type="url"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="https://"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Country') }}</label>
            <select
              v-model="form.country"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Select Country') }}</option>
              <option value="Malawi">Malawi</option>
              <option value="Zambia">Zambia</option>
              <option value="Tanzania">Tanzania</option>
              <option value="Mozambique">Mozambique</option>
              <option value="Zimbabwe">Zimbabwe</option>
              <option value="South Africa">South Africa</option>
              <option value="Kenya">Kenya</option>
              <option value="United States">United States</option>
              <option value="United Kingdom">United Kingdom</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Project Details -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Project Details') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ __('Service Interest') }} <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.service_interest"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.service_interest }"
              required
            >
              <option v-for="(label, key) in options?.service_interests" :key="key" :value="key">{{ label }}</option>
            </select>
            <p v-if="errors.service_interest" class="mt-1 text-sm text-red-600">{{ errors.service_interest[0] }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Budget Range') }}</label>
            <select
              v-model="form.budget_range"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Not Specified') }}</option>
              <option v-for="(label, key) in options?.budget_ranges" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Timeline') }}</label>
            <select
              v-model="form.timeline"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Not Specified') }}</option>
              <option v-for="(label, key) in options?.timelines" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Source') }}</label>
            <select
              v-model="form.source"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option v-for="(label, key) in options?.sources" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('Project Description') }}</label>
            <textarea
              v-model="form.project_description"
              rows="4"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Describe the project requirements...')"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Lead Qualification (only for edit) -->
      <div v-if="modelValue" class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-md font-medium text-gray-900">{{ __('Lead Qualification') }}</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
            <select
              v-model="form.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option v-for="(label, key) in options?.statuses" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Priority') }}</label>
            <select
              v-model="form.priority"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option v-for="(label, key) in options?.priorities" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Assigned To') }}</label>
            <select
              v-model="form.assigned_to"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">{{ __('Unassigned') }}</option>
              <option v-for="user in options?.assignable_users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Next Follow-up') }}</label>
            <input
              v-model="form.next_follow_up_at"
              type="datetime-local"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div v-if="form.status === 'lost'" class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">{{ __('Loss Reason') }}</label>
            <input
              v-model="form.loss_reason"
              type="text"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :placeholder="__('Why was this lead lost?')"
            />
          </div>
        </div>
      </div>

      <!-- Internal Notes -->
      <div class="border-t border-gray-200 pt-6">
        <label class="block text-sm font-medium text-gray-700">{{ __('Internal Notes') }}</label>
        <textarea
          v-model="form.internal_notes"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          :placeholder="__('Private notes about this lead...')"
        ></textarea>
      </div>
    </div>

    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
      <TheButton variant="secondary" @click="$emit('close')">
        {{ __('Cancel') }}
      </TheButton>
      <TheButton :disabled="processing" @click="submit">
        {{ modelValue ? __('Update Lead') : __('Create Lead') }}
      </TheButton>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { axios } from 'spack/axios'
import TheButton from '@/thetheme/components/TheButton.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  },
  options: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'saved'])

const processing = ref(false)
const errors = ref({})

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  company_name: '',
  job_title: '',
  website: '',
  country: 'Malawi',
  city: '',
  service_interest: 'web_development',
  project_description: '',
  budget_range: '',
  timeline: '',
  source: 'website',
  status: 'new',
  priority: 'warm',
  assigned_to: '',
  next_follow_up_at: '',
  internal_notes: '',
  loss_reason: ''
})

const submit = async () => {
  processing.value = true
  errors.value = {}

  const url = props.modelValue
    ? `leads/${props.modelValue.id}`
    : 'leads'

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
    }
    console.error('Failed to save lead:', error)
  } finally {
    processing.value = false
  }
}

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    Object.assign(form, {
      first_name: newVal.first_name || '',
      last_name: newVal.last_name || '',
      email: newVal.email || '',
      phone: newVal.phone || '',
      company_name: newVal.company_name || '',
      job_title: newVal.job_title || '',
      website: newVal.website || '',
      country: newVal.country || 'Malawi',
      city: newVal.city || '',
      service_interest: newVal.service_interest || 'web_development',
      project_description: newVal.project_description || '',
      budget_range: newVal.budget_range || '',
      timeline: newVal.timeline || '',
      source: newVal.source || 'website',
      status: newVal.status || 'new',
      priority: newVal.priority || 'warm',
      assigned_to: newVal.assigned_to || '',
      next_follow_up_at: newVal.next_follow_up_at ? newVal.next_follow_up_at.slice(0, 16) : '',
      internal_notes: newVal.internal_notes || '',
      loss_reason: newVal.loss_reason || ''
    })
  }
}, { immediate: true })
</script>
