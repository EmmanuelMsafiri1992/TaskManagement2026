<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-8 w-8 text-white" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-indigo-100">Available Budget</dt>
                <dd class="text-xl font-bold text-white">MK {{ formatNumber(statistics.current_budget) }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">Total Purchases</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.total_purchases }}</dd>
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
                <dd class="text-lg font-semibold text-yellow-600">{{ statistics.pending_purchases }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">This Month Spent</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.this_month_spent) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-2">
        <div class="relative w-64 rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search purchases..."
            class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="filterStatus"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchPurchases"
        >
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div class="ml-auto">
        <router-link
          to="/legume/purchases/create"
          class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
        >
          <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
          New Purchase
        </router-link>
      </div>
    </div>

    <!-- Purchases Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Purchase #</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="purchase in purchases" :key="purchase.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ purchase.purchase_number }}</div>
              <div class="text-xs text-gray-500">Grade {{ purchase.quality_grade }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-900">{{ purchase.supplier?.name }}</div>
              <div class="text-sm text-gray-500">{{ purchase.supplier?.district }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-900">{{ purchase.product?.name }}</div>
              <div class="text-sm text-gray-500">{{ purchase.product?.sku }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ formatDate(purchase.purchase_date) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              {{ formatNumber(purchase.quantity) }} kg
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm font-medium text-gray-900">MK {{ formatNumber(purchase.grand_total) }}</div>
              <div class="text-xs text-gray-500">@ MK {{ formatNumber(purchase.price_per_unit) }}/kg</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getStatusClass(purchase.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                {{ purchase.status }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
              <button v-if="purchase.status === 'pending'" @click="completePurchase(purchase)" class="text-green-600 hover:text-green-900">Complete</button>
              <button v-if="purchase.status === 'pending'" @click="cancelPurchase(purchase)" class="text-red-600 hover:text-red-900">Cancel</button>
              <router-link :to="`/legume/purchases/${purchase.id}`" class="text-indigo-600 hover:text-indigo-900">View</router-link>
            </td>
          </tr>
          <tr v-if="purchases.length === 0">
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No purchases found</td>
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
  ShoppingBagIcon,
  ClockIcon,
  CurrencyDollarIcon,
  MagnifyingGlassIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const purchases = ref([])
const statistics = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-MW', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchPurchases(), 300)
}

const fetchPurchases = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (filterStatus.value) params.status = filterStatus.value

    const [purchasesRes, statsRes] = await Promise.all([
      axios.get('/api/legume/purchases', { params }),
      axios.get('/api/legume/purchases/statistics')
    ])

    purchases.value = purchasesRes.data.data
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching purchases:', error)
  } finally {
    processing.value = false
  }
}

const completePurchase = async (purchase) => {
  if (confirm(`Complete purchase ${purchase.purchase_number}? This will add stock and deduct from budget.`)) {
    try {
      await axios.post(`/api/legume/purchases/${purchase.id}/complete`)
      fetchPurchases()
    } catch (error) {
      alert(error.response?.data?.message || 'Error completing purchase')
    }
  }
}

const cancelPurchase = async (purchase) => {
  if (confirm(`Cancel purchase ${purchase.purchase_number}?`)) {
    try {
      await axios.post(`/api/legume/purchases/${purchase.id}/cancel`)
      fetchPurchases()
    } catch (error) {
      alert(error.response?.data?.message || 'Error cancelling purchase')
    }
  }
}

onMounted(() => fetchPurchases())
</script>
