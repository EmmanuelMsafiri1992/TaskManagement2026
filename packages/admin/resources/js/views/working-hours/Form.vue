<template>
  <FormBase :external-form="form" @submit="submit" @cancel="emit('close')">
    <template #title>
      {{ form.id ? __('Edit Working Hours') : __('Assign Working Hours') }}
    </template>

    <div class="space-y-6">
      <!-- User Selection -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Employee') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.user_id"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.user_id }"
            :disabled="!!form.id"
            required
          >
            <option value="">{{ __('Select an employee') }}</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <p v-if="form.errors.user_id" class="mt-2 text-sm text-red-600">
            {{ form.errors.user_id }}
          </p>
        </div>
      </div>

      <!-- Working Hours Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Working Hours') }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ __('Default hours are 7:30 AM - 4:30 PM') }}
        </p>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Start Time') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.start_time"
            type="time"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.start_time }"
            required
          />
          <p v-if="form.errors.start_time" class="mt-2 text-sm text-red-600">
            {{ form.errors.start_time }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('End Time') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.end_time"
            type="time"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.end_time }"
            required
          />
          <p v-if="form.errors.end_time" class="mt-2 text-sm text-red-600">
            {{ form.errors.end_time }}
          </p>
        </div>
      </div>

      <!-- Duration Display -->
      <div v-if="form.start_time && form.end_time" class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Duration') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <p class="text-sm text-gray-900 pt-2">
            {{ calculateDuration }} {{ __('hours') }}
          </p>
        </div>
      </div>

      <!-- Effective Period Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Effective Period') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Schedule Type') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div class="flex gap-4">
            <label class="inline-flex items-center">
              <input
                v-model="scheduleType"
                type="radio"
                value="permanent"
                class="form-radio text-indigo-600"
              />
              <span class="ml-2 text-sm text-gray-700">{{ __('Permanent') }}</span>
            </label>
            <label class="inline-flex items-center">
              <input
                v-model="scheduleType"
                type="radio"
                value="temporary"
                class="form-radio text-indigo-600"
              />
              <span class="ml-2 text-sm text-gray-700">{{ __('Temporary (Date Range)') }}</span>
            </label>
          </div>
        </div>
      </div>

      <template v-if="scheduleType === 'temporary'">
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Start Date') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.effective_from"
              type="date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.effective_from }"
            />
            <p v-if="form.errors.effective_from" class="mt-2 text-sm text-red-600">
              {{ form.errors.effective_from }}
            </p>
          </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('End Date') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.effective_until"
              type="date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.effective_until }"
            />
            <p class="mt-1 text-sm text-gray-500">
              {{ __('Leave empty for ongoing schedule') }}
            </p>
            <p v-if="form.errors.effective_until" class="mt-2 text-sm text-red-600">
              {{ form.errors.effective_until }}
            </p>
          </div>
        </div>
      </template>

      <!-- Status -->
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Status') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <label class="inline-flex items-center">
            <input
              v-model="form.is_active"
              type="checkbox"
              class="form-checkbox h-4 w-4 text-indigo-600 rounded"
            />
            <span class="ml-2 text-sm text-gray-700">{{ __('Active') }}</span>
          </label>
        </div>
      </div>

      <!-- Reason -->
      <div class="border-t border-gray-200 pt-6">
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Reason') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <textarea
              v-model="form.reason"
              rows="3"
              :placeholder="__('Why is this employee being assigned custom working hours?')"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.reason }"
            ></textarea>
            <p class="mt-1 text-sm text-gray-500">
              {{ __('This will be included in the notification email to the employee.') }}
            </p>
            <p v-if="form.errors.reason" class="mt-2 text-sm text-red-600">
              {{ form.errors.reason }}
            </p>
          </div>
        </div>
      </div>

      <!-- Notification Info -->
      <div class="rounded-md bg-blue-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-blue-700">
              {{ __('The employee will be notified via email and in-app notification about this schedule change.') }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </FormBase>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import FormBase from '@/thetheme/components/FormBase.vue'
import { useForm } from '@/composables/useForm'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  },
  users: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close'])

const scheduleType = ref('permanent')

const form = useForm('working-hours', {
  user_id: '',
  start_time: '07:30',
  end_time: '16:30',
  effective_from: '',
  effective_until: '',
  is_active: true,
  reason: ''
})

const calculateDuration = computed(() => {
  if (!form.start_time || !form.end_time) return 0
  const [startH, startM] = form.start_time.split(':').map(Number)
  const [endH, endM] = form.end_time.split(':').map(Number)
  const startMinutes = startH * 60 + startM
  const endMinutes = endH * 60 + endM
  return ((endMinutes - startMinutes) / 60).toFixed(1)
})

const submit = () => {
  // Clear dates if permanent
  if (scheduleType.value === 'permanent') {
    form.effective_from = null
    form.effective_until = null
  }

  form.submit(() => {
    emit('close')
  })
}

watch(() => props.modelValue, (record) => {
  if (record) {
    form.setData({
      ...record,
      start_time: record.start_time?.substring(0, 5) || '07:30',
      end_time: record.end_time?.substring(0, 5) || '16:30',
      effective_from: record.effective_from ? record.effective_from.split('T')[0] : '',
      effective_until: record.effective_until ? record.effective_until.split('T')[0] : ''
    })
    scheduleType.value = (!record.effective_from && !record.effective_until) ? 'permanent' : 'temporary'
  } else {
    form.reset()
    scheduleType.value = 'permanent'
  }
}, { immediate: true })
</script>
