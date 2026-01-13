<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Profit by Product</h1>
      <p class="text-sm text-gray-500">Analyze profitability of each product</p>
    </div>

    <!-- Date Range Selector -->
    <div class="mb-6 flex items-center gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">From</label>
        <input
          v-model="dateFrom"
          type="date"
          class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchReport"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">To</label>
        <input
          v-model="dateTo"
          type="date"
          class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchReport"
        />
      </div>
    </div>

    <!-- Summary -->
    <div v-if="summary" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Total Revenue</p>
          <p class="mt-1 text-xl font-semibold text-green-600">MK {{ formatNumber(summary.total_revenue) }}</p>
        </div>
      </div>
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Total COGS</p>
          <p class="mt-1 text-xl font-semibold text-gray-900">MK {{ formatNumber(summary.total_cogs) }}</p>
        </div>
      </div>
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Total Profit</p>
          <p class="mt-1 text-xl font-semibold" :class="summary.total_profit >= 0 ? 'text-green-600' : 'text-red-600'">
            MK {{ formatNumber(summary.total_profit) }}
          </p>
        </div>
      </div>
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Avg Margin</p>
          <p class="mt-1 text-xl font-semibold" :class="summary.avg_margin >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ summary.avg_margin?.toFixed(1) }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Qty Sold</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Revenue</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">COGS</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Gross Profit</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Margin</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">% of Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="product in products" :key="product.product_id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ product.product_name }}</div>
              <div class="text-sm text-gray-500">{{ product.sku }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              {{ formatNumber(product.quantity_sold) }} kg
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              MK {{ formatNumber(product.revenue) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              MK {{ formatNumber(product.cost_of_goods) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium" :class="product.profit >= 0 ? 'text-green-600' : 'text-red-600'">
              MK {{ formatNumber(product.profit) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="flex items-center">
                <span :class="product.margin >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                  {{ product.margin?.toFixed(1) }}%
                </span>
                <div class="ml-2 h-2 w-16 overflow-hidden rounded-full bg-gray-200">
                  <div
                    :class="product.margin >= 0 ? 'bg-green-500' : 'bg-red-500'"
                    :style="{ width: Math.min(Math.abs(product.margin), 100) + '%' }"
                    class="h-full"
                  ></div>
                </div>
              </div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ product.percentage_of_total?.toFixed(1) }}%
            </td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No sales data for this period</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'

const processing = ref(true)
const products = ref([])

// Default to current month
const now = new Date()
const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0)

const dateFrom = ref(firstDay.toISOString().split('T')[0])
const dateTo = ref(lastDay.toISOString().split('T')[0])

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const summary = computed(() => {
  if (products.value.length === 0) return null
  const total_revenue = products.value.reduce((sum, p) => sum + p.revenue, 0)
  const total_cogs = products.value.reduce((sum, p) => sum + p.cost_of_goods, 0)
  const total_profit = products.value.reduce((sum, p) => sum + p.profit, 0)
  const avg_margin = total_revenue > 0 ? (total_profit / total_revenue) * 100 : 0
  return { total_revenue, total_cogs, total_profit, avg_margin }
})

const fetchReport = async () => {
  processing.value = true
  try {
    const response = await axios.get('/api/legume/reports/by-product', {
      params: { date_from: dateFrom.value, date_to: dateTo.value }
    })
    products.value = response.data.data
  } catch (error) {
    console.error('Error fetching report:', error)
  } finally {
    processing.value = false
  }
}

onMounted(() => fetchReport())
</script>
