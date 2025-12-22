<template>
  <FormBase :form="form" @submit="submit">
    <template #title>
      {{ form.id ? __('Edit Leave Request') : __('Request Leave') }}
    </template>

    <div class="space-y-6">
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Leave Type') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.leave_type"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.leave_type }"
            required
          >
            <option value="">{{ __('Select leave type') }}</option>
            <option value="Annual Leave">{{ __('Annual Leave') }}</option>
            <option value="Sick Leave">{{ __('Sick Leave') }}</option>
            <option value="Maternity Leave">{{ __('Maternity Leave') }}</option>
            <option value="Paternity Leave">{{ __('Paternity Leave') }}</option>
            <option value="Unpaid Leave">{{ __('Unpaid Leave') }}</option>
            <option value="Compassionate Leave">{{ __('Compassionate Leave') }}</option>
            <option value="Study Leave">{{ __('Study Leave') }}</option>
          </select>
          <p v-if="form.errors.leave_type" class="mt-2 text-sm text-red-600">
            {{ form.errors.leave_type }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Start Date') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.start_date"
            type="date"
            :min="minDate"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.start_date }"
            required
            @change="calculateDays"
          />
          <p v-if="form.errors.start_date" class="mt-2 text-sm text-red-600">
            {{ form.errors.start_date }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('End Date') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.end_date"
            type="date"
            :min="form.start_date || minDate"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.end_date }"
            required
            @change="calculateDays"
          />
          <p v-if="form.errors.end_date" class="mt-2 text-sm text-red-600">
            {{ form.errors.end_date }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Number of Days') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.days"
            type="number"
            step="0.5"
            min="0.5"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.days }"
            required
            readonly
          />
          <p v-if="form.errors.days" class="mt-2 text-sm text-red-600">
            {{ form.errors.days }}
          </p>
          <p class="mt-1 text-sm text-gray-500">
            {{ __('Automatically calculated based on start and end dates') }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Half Day') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div class="flex items-center">
            <input
              v-model="form.is_half_day"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              @change="handleHalfDayChange"
            />
            <label class="ml-2 block text-sm text-gray-900">
              {{ __('This is a half day leave') }}
            </label>
          </div>
        </div>
      </div>

      <div v-if="form.is_half_day" class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Half Day Period') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.half_day_period"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          >
            <option value="">{{ __('Select period') }}</option>
            <option value="morning">{{ __('Morning') }}</option>
            <option value="afternoon">{{ __('Afternoon') }}</option>
          </select>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Reason') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <textarea
            v-model="form.reason"
            rows="4"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.reason }"
            :placeholder="__('Please provide a detailed reason for your leave request')"
            required
          ></textarea>
          <p v-if="form.errors.reason" class="mt-2 text-sm text-red-600">
            {{ form.errors.reason }}
          </p>
        </div>
      </div>

      <!-- Leave Balance Info (if available) -->
      <div v-if="leaveBalance" class="rounded-md bg-blue-50 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <InformationCircleIcon class="h-5 w-5 text-blue-400" />
          </div>
          <div class="ml-3 flex-1">
            <h3 class="text-sm font-medium text-blue-800">
              {{ __('Your Leave Balance') }}
            </h3>
            <div class="mt-2 text-sm text-blue-700">
              <p v-if="form.leave_type === 'Annual Leave'">
                {{ __('Annual Leave Balance:') }} {{ leaveBalance.annual }} {{ __('days') }}
              </p>
              <p v-else-if="form.leave_type === 'Sick Leave'">
                {{ __('Sick Leave Balance:') }} {{ leaveBalance.sick }} {{ __('days') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FormBase>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { InformationCircleIcon } from '@heroicons/vue/24/outline'
import axios from 'axios'
import FormBase from '@/thetheme/components/FormBase.vue'
import { useForm } from '@/composables/useForm'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const leaveBalance = ref(null)
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const form = useForm('/api/leaves', {
  leave_type: '',
  start_date: '',
  end_date: '',
  days: 1,
  is_half_day: false,
  half_day_period: '',
  reason: ''
})

const calculateDays = () => {
  if (form.start_date && form.end_date) {
    const start = new Date(form.start_date)
    const end = new Date(form.end_date)
    const diffTime = Math.abs(end - start)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1 // +1 to include both start and end dates

    if (form.is_half_day) {
      form.days = 0.5
    } else {
      form.days = diffDays
    }
  }
}

const handleHalfDayChange = () => {
  if (form.is_half_day) {
    form.end_date = form.start_date
    form.days = 0.5
  } else {
    form.half_day_period = ''
    calculateDays()
  }
}

const submit = () => {
  form.submit(() => {
    emit('close')
  })
}

const loadLeaveBalance = async () => {
  try {
    const response = await axios.get('/api/profile/create')
    if (response.data.employee_record) {
      leaveBalance.value = {
        annual: response.data.employee_record.leave_balance_annual,
        sick: response.data.employee_record.leave_balance_sick
      }
    }
  } catch (error) {
    console.error('Failed to load leave balance:', error)
  }
}

watch(() => props.modelValue, (leave) => {
  if (leave) {
    form.setData({
      ...leave,
      start_date: leave.start_date.split('T')[0],
      end_date: leave.end_date.split('T')[0]
    })
  } else {
    form.reset()
  }
}, { immediate: true })

onMounted(() => {
  loadLeaveBalance()
})
</script>
