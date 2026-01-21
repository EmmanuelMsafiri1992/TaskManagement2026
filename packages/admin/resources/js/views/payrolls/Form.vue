<template>
  <div>
    <div class="border-b border-gray-200 px-6 py-4">
      <h2 class="text-lg font-semibold text-gray-900">
        {{ modelValue ? __('Edit Payroll') : __('Create Payroll') }}
      </h2>
    </div>

    <div class="space-y-6 px-6 py-4">
      <!-- Employee Selection -->
      <div v-if="!modelValue">
        <label class="block text-sm font-medium text-gray-700">{{ __('Employee') }} <span class="text-red-500">*</span></label>
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
        <p v-if="errors.user_id" class="mt-2 text-sm text-red-600">{{ errors.user_id[0] }}</p>
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
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Payroll Period') }} <span class="text-red-500">*</span></label>
          <input
            v-model="form.payroll_period"
            type="month"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
            @change="updatePeriodDates"
          />
          <p v-if="errors.payroll_period" class="mt-2 text-sm text-red-600">{{ errors.payroll_period[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Period Start') }} <span class="text-red-500">*</span></label>
          <input
            v-model="form.period_start"
            type="date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          />
          <p v-if="errors.period_start" class="mt-2 text-sm text-red-600">{{ errors.period_start[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Period End') }} <span class="text-red-500">*</span></label>
          <input
            v-model="form.period_end"
            type="date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          />
          <p v-if="errors.period_end" class="mt-2 text-sm text-red-600">{{ errors.period_end[0] }}</p>
        </div>
      </div>

      <!-- Basic Salary & Currency -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Basic Salary') }} <span class="text-red-500">*</span></label>
          <input
            v-model.number="form.basic_salary"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
            @input="calculateTotals"
          />
          <p v-if="errors.basic_salary" class="mt-2 text-sm text-red-600">{{ errors.basic_salary[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Currency') }}</label>
          <select
            v-model="form.currency"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="MWK">MWK (Malawian Kwacha)</option>
            <option value="USD">USD (US Dollar)</option>
            <option value="EUR">EUR (Euro)</option>
            <option value="GBP">GBP (British Pound)</option>
          </select>
        </div>
      </div>

      <!-- Allowances, Bonuses, Deductions -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Total Allowances') }}</label>
          <input
            v-model.number="form.allowances"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <p v-if="errors.allowances" class="mt-2 text-sm text-red-600">{{ errors.allowances[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Total Bonuses') }}</label>
          <input
            v-model.number="form.bonuses"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <p v-if="errors.bonuses" class="mt-2 text-sm text-red-600">{{ errors.bonuses[0] }}</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Total Deductions') }}</label>
          <input
            v-model.number="form.deductions"
            type="number"
            step="0.01"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="calculateTotals"
          />
          <p v-if="errors.deductions" class="mt-2 text-sm text-red-600">{{ errors.deductions[0] }}</p>
        </div>
      </div>

      <!-- Outstanding Advances -->
      <div v-if="outstandingAdvances.length > 0" class="border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-orange-600">{{ __('Outstanding Salary Advances') }}</h3>
        </div>
        <div class="rounded-lg border-2 border-orange-200 bg-orange-50 p-4 mb-4">
          <p class="text-sm text-orange-800 mb-3">
            {{ __('This employee has outstanding salary advances. Select advances to deduct from this payroll:') }}
          </p>
          <div class="space-y-2">
            <div v-for="advance in outstandingAdvances" :key="advance.id" class="flex items-center justify-between bg-white rounded p-3 border border-orange-200">
              <div class="flex items-center">
                <input
                  :id="'advance-' + advance.id"
                  v-model="selectedAdvances"
                  type="checkbox"
                  :value="advance.id"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  @change="handleAdvanceSelection"
                />
                <label :for="'advance-' + advance.id" class="ml-3">
                  <span class="text-sm font-medium text-gray-900">{{ formatCurrency(advance.amount - advance.amount_deducted) }}</span>
                  <span class="text-xs text-gray-500 ml-2">({{ __('Requested') }}: {{ new Date(advance.created_at).toLocaleDateString() }})</span>
                </label>
              </div>
              <div class="text-xs text-gray-500 max-w-xs truncate">{{ advance.reason }}</div>
            </div>
          </div>
          <div class="mt-3 pt-3 border-t border-orange-200 flex justify-between items-center">
            <span class="text-sm font-medium text-orange-800">{{ __('Total Outstanding') }}:</span>
            <span class="text-lg font-bold text-orange-600">{{ formatCurrency(totalOutstandingAdvances) }}</span>
          </div>
        </div>
      </div>

      <!-- Payroll Items -->
      <div class="border-t border-gray-200 pt-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ __('Payroll Items') }}</h3>
          <TheButton size="sm" variant="secondary" @click="addItem">
            <PlusIcon class="mr-1 h-4 w-4" />
            {{ __('Add Item') }}
          </TheButton>
        </div>

        <div v-if="form.items.length === 0" class="text-center text-gray-500 py-4">
          {{ __('No items added. Click "Add Item" to add allowances, bonuses, or deductions.') }}
        </div>

        <div v-else class="space-y-3">
          <div v-for="(item, index) in form.items" :key="index" class="flex items-start gap-3 rounded-lg border border-gray-200 p-3">
            <div class="flex-1 grid grid-cols-1 gap-3 sm:grid-cols-4">
              <div>
                <select
                  v-model="item.item_type"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  @change="calculateFromItems"
                >
                  <option value="allowance">{{ __('Allowance') }}</option>
                  <option value="bonus">{{ __('Bonus') }}</option>
                  <option value="deduction">{{ __('Deduction') }}</option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <input
                  v-model="item.description"
                  type="text"
                  :placeholder="__('Description')"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
              </div>
              <div>
                <input
                  v-model.number="item.amount"
                  type="number"
                  step="0.01"
                  :placeholder="__('Amount')"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  @input="calculateFromItems"
                />
              </div>
            </div>
            <button
              type="button"
              class="text-red-500 hover:text-red-700"
              @click="removeItem(index)"
            >
              <TrashIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Totals Summary -->
      <div class="rounded-lg border-2 border-indigo-200 bg-indigo-50 p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <div class="text-sm font-medium text-gray-500">{{ __('Gross Salary') }}</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">
              {{ formatCurrency(form.gross_salary) }}
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">{{ __('Total Deductions') }}</div>
            <div class="mt-1 text-2xl font-bold text-red-600">
              {{ formatCurrency(form.deductions) }}
            </div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-500">{{ __('Net Salary') }}</div>
            <div class="mt-1 text-2xl font-bold text-green-600">
              {{ formatCurrency(form.net_salary) }}
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
          :placeholder="__('Optional notes')"
        ></textarea>
      </div>
    </div>

    <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
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
import { axios } from 'spack/axios'
import TheButton from '@/thetheme/components/TheButton.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'

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
const outstandingAdvances = ref([])
const selectedAdvances = ref([])
const totalOutstandingAdvances = ref(0)

const getCurrentMonth = () => {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
}

const getMonthStartEnd = (monthStr) => {
  const [year, month] = monthStr.split('-').map(Number)
  const startDate = new Date(year, month - 1, 1)
  const endDate = new Date(year, month, 0)
  return {
    start: startDate.toISOString().split('T')[0],
    end: endDate.toISOString().split('T')[0]
  }
}

const form = reactive({
  user_id: props.modelValue?.user_id || '',
  payroll_period: props.modelValue?.payroll_period || getCurrentMonth(),
  period_start: props.modelValue?.period_start?.split('T')[0] || getMonthStartEnd(getCurrentMonth()).start,
  period_end: props.modelValue?.period_end?.split('T')[0] || getMonthStartEnd(getCurrentMonth()).end,
  basic_salary: props.modelValue?.basic_salary || 0,
  allowances: props.modelValue?.allowances || 0,
  bonuses: props.modelValue?.bonuses || 0,
  deductions: props.modelValue?.deductions || 0,
  gross_salary: props.modelValue?.gross_salary || 0,
  net_salary: props.modelValue?.net_salary || 0,
  currency: props.modelValue?.currency || 'MWK',
  notes: props.modelValue?.notes || '',
  items: props.modelValue?.items || []
})

const updatePeriodDates = () => {
  if (form.payroll_period) {
    const { start, end } = getMonthStartEnd(form.payroll_period)
    form.period_start = start
    form.period_end = end
  }
}

const calculateTotals = () => {
  form.gross_salary = (form.basic_salary || 0) + (form.allowances || 0) + (form.bonuses || 0)
  form.net_salary = form.gross_salary - (form.deductions || 0)
}

const calculateFromItems = () => {
  let allowances = 0
  let bonuses = 0
  let deductions = 0

  form.items.forEach(item => {
    const amount = parseFloat(item.amount) || 0
    if (item.item_type === 'allowance') allowances += amount
    else if (item.item_type === 'bonus') bonuses += amount
    else if (item.item_type === 'deduction') deductions += amount
  })

  form.allowances = allowances
  form.bonuses = bonuses
  form.deductions = deductions
  calculateTotals()
}

const addItem = () => {
  form.items.push({
    item_type: 'allowance',
    description: '',
    amount: 0,
    category: ''
  })
}

const removeItem = (index) => {
  form.items.splice(index, 1)
  calculateFromItems()
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat().format(amount || 0) + ' ' + form.currency
}

const loadUsers = async () => {
  try {
    const response = await axios.get('users')
    users.value = response.data.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const loadOutstandingAdvances = async (userId) => {
  if (!userId) {
    outstandingAdvances.value = []
    totalOutstandingAdvances.value = 0
    return
  }
  try {
    const response = await axios.get(`advance-requests/user/${userId}`)
    outstandingAdvances.value = response.data.data || []
    totalOutstandingAdvances.value = response.data.total_outstanding || 0
  } catch (error) {
    console.error('Failed to load outstanding advances:', error)
    outstandingAdvances.value = []
    totalOutstandingAdvances.value = 0
  }
}

const handleAdvanceSelection = () => {
  // Remove existing advance deduction items
  form.items = form.items.filter(item => item.category !== 'Salary Advance')

  // Add selected advances as deduction items
  selectedAdvances.value.forEach(advanceId => {
    const advance = outstandingAdvances.value.find(a => a.id === advanceId)
    if (advance) {
      const outstanding = advance.amount - advance.amount_deducted
      form.items.push({
        item_type: 'deduction',
        description: `Salary Advance Repayment (ID: ${advance.id})`,
        amount: outstanding,
        category: 'Salary Advance',
        advance_id: advance.id
      })
    }
  })

  calculateFromItems()
}

const submit = async () => {
  processing.value = true
  errors.value = {}

  const url = props.modelValue
    ? `payrolls/${props.modelValue.id}`
    : 'payrolls'

  const data = {
    user_id: form.user_id,
    payroll_period: form.payroll_period,
    period_start: form.period_start,
    period_end: form.period_end,
    basic_salary: parseFloat(form.basic_salary) || 0,
    allowances: parseFloat(form.allowances) || 0,
    bonuses: parseFloat(form.bonuses) || 0,
    deductions: parseFloat(form.deductions) || 0,
    currency: form.currency,
    notes: form.notes,
    items: form.items.filter(item => item.description && item.amount > 0)
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

onMounted(async () => {
  if (!props.modelValue) {
    loadUsers()
  } else {
    // Load outstanding advances for existing payroll
    await loadOutstandingAdvances(props.modelValue.user_id)
  }
  calculateTotals()
})

// Watch for user_id changes to load their outstanding advances
watch(() => form.user_id, async (newUserId) => {
  if (newUserId) {
    await loadOutstandingAdvances(newUserId)
    selectedAdvances.value = []
  }
})

watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    Object.assign(form, {
      user_id: newVal.user_id,
      payroll_period: newVal.payroll_period || getCurrentMonth(),
      period_start: newVal.period_start?.split('T')[0] || getMonthStartEnd(getCurrentMonth()).start,
      period_end: newVal.period_end?.split('T')[0] || getMonthStartEnd(getCurrentMonth()).end,
      basic_salary: newVal.basic_salary || 0,
      allowances: newVal.allowances || 0,
      bonuses: newVal.bonuses || 0,
      deductions: newVal.deductions || 0,
      gross_salary: newVal.gross_salary || 0,
      net_salary: newVal.net_salary || 0,
      currency: newVal.currency || 'MWK',
      notes: newVal.notes || '',
      items: newVal.items || []
    })
    calculateTotals()
  }
}, { immediate: true })
</script>
