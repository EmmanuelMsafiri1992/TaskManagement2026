<template>
  <div>
    <div class="space-y-6 px-4 py-5 sm:p-6">
      <!-- Employee Selection -->
      <div v-if="!modelValue">
        <label class="block text-sm font-medium text-gray-700">{{ __('Employee') }}</label>
        <select
          v-model="form.user_id"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          required
        >
          <option value="">{{ __('Select Employee') }}</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
        <FormError :error="errors.user_id" />
      </div>

      <div v-else class="rounded-md bg-gray-50 p-4">
        <div class="flex items-center">
          <div class="h-10 w-10 flex-shrink-0">
            <UserAvatar :user="modelValue.user" size="10" />
          </div>
          <div class="ml-4">
            <div class="text-sm font-medium text-gray-900">{{ modelValue.user.name }}</div>
            <div class="text-sm text-gray-500">{{ modelValue.user.email }}</div>
          </div>
        </div>
      </div>

      <!-- Payroll Period -->
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Payroll Period') }}</label>
        <input
          v-model="form.payroll_period"
          type="month"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          required
        />
        <FormError :error="errors.payroll_period" />
      </div>

      <!-- Currency -->
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Currency') }}</label>
        <select
          v-model="form.currency"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        >
          <option value="USD">USD</option>
          <option value="EUR">EUR</option>
          <option value="GBP">GBP</option>
          <option value="INR">INR</option>
        </select>
        <FormError :error="errors.currency" />
      </div>

      <!-- Basic Information -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Basic Salary') }}</label>
          <input
            v-model.number="form.basic_salary"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
            @input="calculateTotals"
          />
          <FormError :error="errors.basic_salary" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Working Days') }}</label>
          <input
            v-model.number="form.working_days"
            type="number"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <FormError :error="errors.working_days" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Present Days') }}</label>
          <input
            v-model.number="form.present_days"
            type="number"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <FormError :error="errors.present_days" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Absent Days') }}</label>
          <input
            v-model.number="form.absent_days"
            type="number"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <FormError :error="errors.absent_days" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Leave Days') }}</label>
          <input
            v-model.number="form.leave_days"
            type="number"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <FormError :error="errors.leave_days" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Overtime Hours') }}</label>
          <input
            v-model.number="form.overtime_hours"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <FormError :error="errors.overtime_hours" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Overtime Rate') }}</label>
          <input
            v-model.number="form.overtime_rate"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <FormError :error="errors.overtime_rate" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Overtime Pay') }}</label>
          <input
            v-model.number="form.overtime_pay"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            readonly
          />
          <FormError :error="errors.overtime_pay" />
        </div>
      </div>

      <!-- Allowances Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Allowances') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('House Rent Allowance') }}</label>
            <input
              v-model.number="form.house_rent_allowance"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.house_rent_allowance" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Medical Allowance') }}</label>
            <input
              v-model.number="form.medical_allowance"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.medical_allowance" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Transport Allowance') }}</label>
            <input
              v-model.number="form.transport_allowance"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.transport_allowance" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Other Allowances') }}</label>
            <input
              v-model.number="form.other_allowances"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.other_allowances" />
          </div>
        </div>
      </div>

      <!-- Deductions Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Deductions') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Tax Deduction') }}</label>
            <input
              v-model.number="form.tax_deduction"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.tax_deduction" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Provident Fund') }}</label>
            <input
              v-model.number="form.provident_fund"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.provident_fund" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Loan Deduction') }}</label>
            <input
              v-model.number="form.loan_deduction"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.loan_deduction" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Other Deductions') }}</label>
            <input
              v-model.number="form.other_deductions"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.other_deductions" />
          </div>
        </div>
      </div>

      <!-- Bonuses Section -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="mb-4 text-lg font-medium text-gray-900">{{ __('Bonuses & Adjustments') }}</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Performance Bonus') }}</label>
            <input
              v-model.number="form.performance_bonus"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.performance_bonus" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Festival Bonus') }}</label>
            <input
              v-model.number="form.festival_bonus"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.festival_bonus" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Advance Payment') }}</label>
            <input
              v-model.number="form.advance_payment"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.advance_payment" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Adjustment') }}</label>
            <input
              v-model.number="form.adjustment"
              type="number"
              step="0.01"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @input="calculateTotals"
            />
            <FormError :error="errors.adjustment" />
          </div>
        </div>
      </div>

      <!-- Totals Summary -->
      <div class="rounded-lg border-2 border-indigo-200 bg-indigo-50 p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <div class="text-sm font-medium text-gray-500">{{ __('Gross Salary') }}</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
              {{ new Intl.NumberFormat().format(form.gross_salary) }}
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">{{ __('Total Deductions') }}</div>
            <div class="mt-1 text-2xl font-bold text-red-600">
              {{ new Intl.NumberFormat().format(form.total_deductions) }}
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">{{ __('Net Salary') }}</div>
            <div class="mt-1 text-2xl font-bold text-green-600">
              {{ new Intl.NumberFormat().format(form.net_salary) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
        <textarea
          v-model="form.notes"
          rows="3"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        ></textarea>
        <FormError :error="errors.notes" />
      </div>
    </div>

    <div class="flex justify-end gap-3 bg-gray-50 px-4 py-3 sm:px-6">
      <TheButton variant="secondary" @click="$emit('close')">
        {{ __('Cancel') }}
      </TheButton>
      <TheButton :disabled="processing" @click="submit">
        {{ modelValue ? __('Update Payroll') : __('Create Payroll') }}
      </TheButton>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import axios from 'axios'
import TheButton from '@/thetheme/components/TheButton.vue'
import FormError from '@/thetheme/components/FormError.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])

const users = ref([])
const processing = ref(false)
const errors = ref({})

const form = reactive({
  user_id: props.modelValue?.user_id || '',
  payroll_period: props.modelValue?.payroll_period || new Date().toISOString().slice(0, 7),
  currency: props.modelValue?.currency || 'USD',
  basic_salary: props.modelValue?.basic_salary || 0,
  working_days: props.modelValue?.working_days || 0,
  present_days: props.modelValue?.present_days || 0,
  absent_days: props.modelValue?.absent_days || 0,
  leave_days: props.modelValue?.leave_days || 0,
  overtime_hours: props.modelValue?.overtime_hours || 0,
  overtime_rate: props.modelValue?.overtime_rate || 0,
  overtime_pay: props.modelValue?.overtime_pay || 0,
  house_rent_allowance: props.modelValue?.house_rent_allowance || 0,
  medical_allowance: props.modelValue?.medical_allowance || 0,
  transport_allowance: props.modelValue?.transport_allowance || 0,
  other_allowances: props.modelValue?.other_allowances || 0,
  tax_deduction: props.modelValue?.tax_deduction || 0,
  provident_fund: props.modelValue?.provident_fund || 0,
  loan_deduction: props.modelValue?.loan_deduction || 0,
  other_deductions: props.modelValue?.other_deductions || 0,
  performance_bonus: props.modelValue?.performance_bonus || 0,
  festival_bonus: props.modelValue?.festival_bonus || 0,
  advance_payment: props.modelValue?.advance_payment || 0,
  adjustment: props.modelValue?.adjustment || 0,
  gross_salary: props.modelValue?.gross_salary || 0,
  total_deductions: props.modelValue?.total_deductions || 0,
  net_salary: props.modelValue?.net_salary || 0,
  notes: props.modelValue?.notes || ''
})

const calculateTotals = () => {
  // Calculate overtime pay
  form.overtime_pay = (form.overtime_hours || 0) * (form.overtime_rate || 0)

  // Calculate gross salary
  form.gross_salary = (
    (form.basic_salary || 0) +
    (form.overtime_pay || 0) +
    (form.house_rent_allowance || 0) +
    (form.medical_allowance || 0) +
    (form.transport_allowance || 0) +
    (form.other_allowances || 0) +
    (form.performance_bonus || 0) +
    (form.festival_bonus || 0)
  )

  // Calculate total deductions
  form.total_deductions = (
    (form.tax_deduction || 0) +
    (form.provident_fund || 0) +
    (form.loan_deduction || 0) +
    (form.other_deductions || 0) +
    (form.advance_payment || 0)
  )

  // Calculate net salary
  form.net_salary = form.gross_salary - form.total_deductions + (form.adjustment || 0)
}

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users')
    users.value = response.data.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const submit = async () => {
  processing.value = true
  errors.value = {}

  const url = props.modelValue
    ? `/api/payrolls/${props.modelValue.id}`
    : '/api/payrolls'

  const data = {
    ...form,
    basic_salary: parseFloat(form.basic_salary) || 0,
    working_days: parseInt(form.working_days) || 0,
    present_days: parseInt(form.present_days) || 0,
    absent_days: parseInt(form.absent_days) || 0,
    leave_days: parseInt(form.leave_days) || 0,
    overtime_hours: parseFloat(form.overtime_hours) || 0,
    overtime_rate: parseFloat(form.overtime_rate) || 0,
    overtime_pay: parseFloat(form.overtime_pay) || 0,
    house_rent_allowance: parseFloat(form.house_rent_allowance) || 0,
    medical_allowance: parseFloat(form.medical_allowance) || 0,
    transport_allowance: parseFloat(form.transport_allowance) || 0,
    other_allowances: parseFloat(form.other_allowances) || 0,
    tax_deduction: parseFloat(form.tax_deduction) || 0,
    provident_fund: parseFloat(form.provident_fund) || 0,
    loan_deduction: parseFloat(form.loan_deduction) || 0,
    other_deductions: parseFloat(form.other_deductions) || 0,
    performance_bonus: parseFloat(form.performance_bonus) || 0,
    festival_bonus: parseFloat(form.festival_bonus) || 0,
    advance_payment: parseFloat(form.advance_payment) || 0,
    adjustment: parseFloat(form.adjustment) || 0,
    gross_salary: parseFloat(form.gross_salary) || 0,
    total_deductions: parseFloat(form.total_deductions) || 0,
    net_salary: parseFloat(form.net_salary) || 0
  }

  try {
    if (props.modelValue) {
      await axios.put(url, data)
    } else {
      await axios.post(url, data)
    }
    emit('saved')
    emit('close')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
    console.error('Failed to save payroll:', error)
  } finally {
    processing.value = false
  }
}

onMounted(() => {
  if (!props.modelValue) {
    loadUsers()
  }
  calculateTotals()
})

watch(() => props.modelValue, () => {
  if (props.modelValue) {
    calculateTotals()
  }
}, { immediate: true })
</script>
