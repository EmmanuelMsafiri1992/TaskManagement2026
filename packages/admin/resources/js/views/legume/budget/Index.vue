<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Current Budget Card -->
    <div class="mb-6 overflow-hidden rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 shadow-lg">
      <div class="p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-indigo-100">Current Available Budget</p>
            <p class="mt-1 text-4xl font-bold text-white">MK {{ formatNumber(currentBudget) }}</p>
          </div>
          <BanknotesIcon class="h-16 w-16 text-white opacity-75" />
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowUpIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Added</dt>
                <dd class="text-lg font-semibold text-green-600">MK {{ formatNumber(statistics.total_additions) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowDownIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Deducted</dt>
                <dd class="text-lg font-semibold text-red-600">MK {{ formatNumber(statistics.total_deductions) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ShoppingBagIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Spent on Purchases</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.total_spent_on_purchases) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CalendarIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">This Month Added</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.this_month_additions) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Budget Button -->
    <div class="mb-6 flex justify-end">
      <button
        type="button"
        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
        @click="openModal()"
      >
        <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
        Add Budget
      </button>
    </div>

    <!-- Budget History Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Budget History</h2>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Description</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Added By</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="entry in budgetEntries" :key="entry.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ formatDate(entry.budget_date) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getTypeClass(entry.type)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                {{ entry.type }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="entry.amount >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                {{ entry.amount >= 0 ? '+' : '' }}MK {{ formatNumber(entry.amount) }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ entry.description || '-' }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ entry.user?.name || '-' }}
            </td>
          </tr>
          <tr v-if="budgetEntries.length === 0">
            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No budget entries found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Add Budget Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">Add Budget Entry</h3>

          <form @submit.prevent="saveBudget">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Type *</label>
                <select v-model="form.type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="initial">Initial Capital</option>
                  <option value="addition">Addition</option>
                  <option value="deduction">Deduction</option>
                  <option value="adjustment">Adjustment</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Amount (MK) *</label>
                <input
                  v-model.number="form.amount"
                  type="number"
                  step="0.01"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Enter amount"
                />
                <p class="mt-1 text-xs text-gray-500">
                  {{ form.type === 'deduction' ? 'Enter positive amount (will be deducted)' : 'Enter the amount to add' }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Date *</label>
                <input
                  v-model="form.budget_date"
                  type="date"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Reason for this budget entry..."
                ></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button type="button" @click="closeModal" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/components/Loader.vue'
import {
  BanknotesIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ShoppingBagIcon,
  CalendarIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const budgetEntries = ref([])
const statistics = ref(null)
const currentBudget = ref(0)
const showModal = ref(false)
const saving = ref(false)

const form = ref({
  type: 'addition',
  amount: '',
  budget_date: new Date().toISOString().split('T')[0],
  description: ''
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-MW', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getTypeClass = (type) => {
  const classes = {
    initial: 'bg-blue-100 text-blue-800',
    addition: 'bg-green-100 text-green-800',
    deduction: 'bg-red-100 text-red-800',
    adjustment: 'bg-yellow-100 text-yellow-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const fetchData = async () => {
  try {
    const [entriesRes, currentRes, statsRes] = await Promise.all([
      axios.get('/api/legume/budget'),
      axios.get('/api/legume/budget/current'),
      axios.get('/api/legume/budget/statistics')
    ])

    budgetEntries.value = entriesRes.data.data
    currentBudget.value = currentRes.data.data?.current_budget || 0
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching budget data:', error)
  } finally {
    processing.value = false
  }
}

const openModal = () => {
  form.value = {
    type: 'addition',
    amount: '',
    budget_date: new Date().toISOString().split('T')[0],
    description: ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const saveBudget = async () => {
  saving.value = true
  try {
    await axios.post('/api/legume/budget', form.value)
    closeModal()
    fetchData()
  } catch (error) {
    console.error('Error saving budget:', error)
    alert(error.response?.data?.message || 'Error saving budget entry')
  } finally {
    saving.value = false
  }
}

onMounted(() => fetchData())
</script>
