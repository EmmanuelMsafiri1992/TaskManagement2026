<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Collected</dt>
                <dd class="text-lg font-semibold text-green-600">MK {{ formatNumber(statistics.total_collected) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Pending</dt>
                <dd class="text-lg font-semibold text-yellow-600">MK {{ formatNumber(statistics.pending_amount) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <DevicePhoneMobileIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Mobile Money</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.mobile_money_total) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Cash Payments</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.cash_total) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-2">
        <div class="relative w-64 rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search payments..."
            class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="filterMethod"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchPayments"
        >
          <option value="">All Methods</option>
          <option value="cash">Cash</option>
          <option value="bank_transfer">Bank Transfer</option>
          <option value="airtel_money">Airtel Money</option>
          <option value="tnm_mpamba">TNM Mpamba</option>
          <option value="other">Other</option>
        </select>

        <select
          v-model="filterStatus"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchPayments"
        >
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
          <option value="failed">Failed</option>
          <option value="reversed">Reversed</option>
        </select>

        <input
          v-model="filterDateFrom"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchPayments"
        />

        <input
          v-model="filterDateTo"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchPayments"
        />
      </div>
    </div>

    <!-- Payments Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reference</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Order</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Method</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ payment.payment_reference }}</div>
              <div v-if="payment.transaction_id" class="text-xs text-gray-500">Trans: {{ payment.transaction_id }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <router-link :to="`/legume/orders/${payment.legume_order_id}`" class="text-indigo-600 hover:text-indigo-900">
                {{ payment.order?.order_number }}
              </router-link>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ payment.order?.customer?.name }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ formatDate(payment.payment_date) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getMethodClass(payment.payment_method)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                {{ getMethodLabel(payment.payment_method) }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-green-600">
              MK {{ formatNumber(payment.amount) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getStatusClass(payment.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                {{ payment.status }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <button v-if="payment.status === 'pending'" @click="markCompleted(payment)" class="text-green-600 hover:text-green-900 mr-2">Complete</button>
              <button v-if="payment.status === 'completed'" @click="reversePayment(payment)" class="text-red-600 hover:text-red-900">Reverse</button>
            </td>
          </tr>
          <tr v-if="payments.length === 0">
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No payments found</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'
import {
  BanknotesIcon,
  ClockIcon,
  DevicePhoneMobileIcon,
  CurrencyDollarIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const payments = ref([])
const statistics = ref(null)
const searchQuery = ref('')
const filterMethod = ref('')
const filterStatus = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-MW', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getMethodLabel = (method) => {
  const labels = {
    cash: 'Cash',
    bank_transfer: 'Bank Transfer',
    airtel_money: 'Airtel Money',
    tnm_mpamba: 'TNM Mpamba',
    other: 'Other'
  }
  return labels[method] || method
}

const getMethodClass = (method) => {
  const classes = {
    cash: 'bg-green-100 text-green-800',
    bank_transfer: 'bg-blue-100 text-blue-800',
    airtel_money: 'bg-red-100 text-red-800',
    tnm_mpamba: 'bg-yellow-100 text-yellow-800',
    other: 'bg-gray-100 text-gray-800'
  }
  return classes[method] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    reversed: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchPayments(), 300)
}

const fetchPayments = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (filterMethod.value) params.payment_method = filterMethod.value
    if (filterStatus.value) params.status = filterStatus.value
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value) params.date_to = filterDateTo.value

    const [paymentsRes, statsRes] = await Promise.all([
      axios.get('/api/legume/payments', { params }),
      axios.get('/api/legume/payments/statistics')
    ])

    payments.value = paymentsRes.data.data
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching payments:', error)
  } finally {
    processing.value = false
  }
}

const markCompleted = async (payment) => {
  if (confirm('Mark this payment as completed?')) {
    try {
      await axios.post(`/api/legume/payments/${payment.id}/complete`)
      fetchPayments()
    } catch (error) {
      alert(error.response?.data?.message || 'Error completing payment')
    }
  }
}

const reversePayment = async (payment) => {
  if (confirm('Reverse this payment? This will update the order payment status.')) {
    try {
      await axios.post(`/api/legume/payments/${payment.id}/reverse`)
      fetchPayments()
    } catch (error) {
      alert(error.response?.data?.message || 'Error reversing payment')
    }
  }
}

onMounted(() => fetchPayments())
</script>
