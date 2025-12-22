<template>
  <FormBase :form="form" @submit="submit">
    <template #title>
      {{ form.id ? __('Edit Employee Record') : __('Create Employee Record') }}
    </template>

    <div class="space-y-6">
      <!-- User Selection (only for create) -->
      <div v-if="!form.id" class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('User') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.user_id"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.user_id }"
            required
          >
            <option value="">{{ __('Select a user') }}</option>
            <option v-for="user in availableUsers" :key="user.id" :value="user.id">
              {{ user.name }} ({{ user.email }})
            </option>
          </select>
          <p v-if="form.errors.user_id" class="mt-2 text-sm text-red-600">
            {{ form.errors.user_id }}
          </p>
        </div>
      </div>

      <!-- Personal Information Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Personal Information') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('National ID') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.national_id"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Phone Number') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.phone_number"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Physical Address') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <textarea
            v-model="form.physical_address"
            rows="2"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Date of Birth') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.date_of_birth"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Gender') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.gender"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">{{ __('Select gender') }}</option>
            <option value="Male">{{ __('Male') }}</option>
            <option value="Female">{{ __('Female') }}</option>
            <option value="Other">{{ __('Other') }}</option>
          </select>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Marital Status') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.marital_status"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">{{ __('Select status') }}</option>
            <option value="Single">{{ __('Single') }}</option>
            <option value="Married">{{ __('Married') }}</option>
            <option value="Divorced">{{ __('Divorced') }}</option>
            <option value="Widowed">{{ __('Widowed') }}</option>
          </select>
        </div>
      </div>

      <!-- Next of Kin Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Next of Kin') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Name') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.next_of_kin_name"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Relationship') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.next_of_kin_relationship"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Phone') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.next_of_kin_phone"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Address') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <textarea
            v-model="form.next_of_kin_address"
            rows="2"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>
      </div>

      <!-- Employment Information Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Employment Information') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Position') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.position"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Department') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.department"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Reports To') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.reports_to"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="">{{ __('Select supervisor') }}</option>
            <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">
              {{ supervisor.name }}
            </option>
          </select>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Employment Date') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.employment_date"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Employment Type') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.employment_type"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          >
            <option value="Permanent">{{ __('Permanent') }}</option>
            <option value="Contract">{{ __('Contract') }}</option>
            <option value="Probation">{{ __('Probation') }}</option>
            <option value="Casual">{{ __('Casual') }}</option>
            <option value="Internship">{{ __('Internship') }}</option>
          </select>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Employment Status') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <select
            v-model="form.employment_status"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          >
            <option value="Active">{{ __('Active') }}</option>
            <option value="Resigned">{{ __('Resigned') }}</option>
            <option value="Terminated">{{ __('Terminated') }}</option>
            <option value="Retired">{{ __('Retired') }}</option>
            <option value="Deceased">{{ __('Deceased') }}</option>
            <option value="On Leave">{{ __('On Leave') }}</option>
            <option value="Suspended">{{ __('Suspended') }}</option>
          </select>
        </div>
      </div>

      <!-- Contract/Probation Details (conditional) -->
      <div v-if="form.employment_type === 'Contract'" class="space-y-6 border-t border-gray-200 pt-6">
        <h4 class="text-base font-medium text-gray-900">{{ __('Contract Details') }}</h4>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Contract Start Date') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.contract_start_date"
              type="date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Contract End Date') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.contract_end_date"
              type="date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <div v-if="form.employment_type === 'Probation'" class="space-y-6 border-t border-gray-200 pt-6">
        <h4 class="text-base font-medium text-gray-900">{{ __('Probation Details') }}</h4>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Probation Period (months)') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.probation_period_months"
              type="number"
              min="1"
              max="12"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Probation End Date') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.probation_end_date"
              type="date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>

        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Confirmation Date') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <input
              v-model="form.confirmation_date"
              type="date"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
        </div>
      </div>

      <!-- Salary Information -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Salary Information') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Current Salary') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div class="flex gap-2">
            <input
              v-model="form.current_salary"
              type="number"
              step="0.01"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <select
              v-model="form.salary_currency"
              class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="MWK">MWK</option>
              <option value="USD">USD</option>
              <option value="EUR">EUR</option>
              <option value="GBP">GBP</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Leave Entitlement -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Leave Entitlement') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Annual Leave Days') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.annual_leave_days"
            type="number"
            min="0"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Sick Leave Days') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.sick_leave_days"
            type="number"
            min="0"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Maternity Leave Days') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.maternity_leave_days"
            type="number"
            min="0"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <!-- Tax & Pension -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Tax & Pension') }}
        </h3>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Tax Identification Number') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.tax_identification_number"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Pension Number') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.pension_number"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>
      </div>

      <!-- Notes -->
      <div class="border-t border-gray-200 pt-6">
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Notes') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <textarea
              v-model="form.notes"
              rows="3"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>
        </div>
      </div>
    </div>
  </FormBase>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
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

const availableUsers = ref([])
const supervisors = ref([])

const form = useForm('/api/employees', {
  user_id: '',
  national_id: '',
  phone_number: '',
  physical_address: '',
  date_of_birth: '',
  gender: '',
  marital_status: '',
  next_of_kin_name: '',
  next_of_kin_relationship: '',
  next_of_kin_phone: '',
  next_of_kin_address: '',
  employment_date: '',
  employment_type: 'Probation',
  contract_start_date: '',
  contract_end_date: '',
  probation_period_months: 3,
  probation_end_date: '',
  confirmation_date: '',
  position: '',
  department: '',
  reports_to: '',
  current_salary: '',
  salary_currency: 'MWK',
  employment_status: 'Active',
  annual_leave_days: 18,
  sick_leave_days: 30,
  maternity_leave_days: 56,
  tax_identification_number: '',
  pension_number: '',
  notes: ''
})

const submit = () => {
  form.submit(() => {
    emit('close')
  })
}

const loadAvailableUsers = async () => {
  try {
    const response = await axios.get('/api/employees/available-users')
    availableUsers.value = response.data.data
  } catch (error) {
    console.error('Failed to load available users:', error)
  }
}

const loadSupervisors = async () => {
  try {
    const response = await axios.get('/api/employees/supervisors')
    supervisors.value = response.data.data
  } catch (error) {
    console.error('Failed to load supervisors:', error)
  }
}

watch(() => props.modelValue, (employee) => {
  if (employee) {
    form.setData(employee)
  } else {
    form.reset()
  }
}, { immediate: true })

onMounted(() => {
  loadAvailableUsers()
  loadSupervisors()
})
</script>
