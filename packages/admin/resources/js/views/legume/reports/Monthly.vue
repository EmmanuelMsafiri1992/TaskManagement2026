<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Monthly Profit Report</h1>
      <p class="text-sm text-gray-500">Analyze profit and performance by month</p>
    </div>

    <!-- Month Selector -->
    <div class="mb-6 flex items-center gap-4">
      <select
        v-model="selectedYear"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchReport"
      >
        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
      </select>

      <select
        v-model="selectedMonth"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchReport"
      >
        <option v-for="(month, index) in months" :key="index" :value="index + 1">{{ month }}</option>
      </select>
    </div>

    <!-- Summary Cards -->
    <div v-if="report" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Revenue</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(report.revenue) }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">Cost of Goods</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(report.cost_of_goods) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowTrendingUpIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Gross Profit</dt>
                <dd class="text-lg font-semibold" :class="report.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                  MK {{ formatNumber(report.gross_profit) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-6 w-6 text-white" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-indigo-100">Net Profit</dt>
                <dd class="text-lg font-bold text-white">MK {{ formatNumber(report.net_profit) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Report -->
    <div v-if="report" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Revenue Breakdown -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Revenue Breakdown</h2>
        </div>
        <div class="p-6">
          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Total Orders</dt>
              <dd class="text-sm font-medium text-gray-900">{{ report.total_orders }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Delivered Orders</dt>
              <dd class="text-sm font-medium text-gray-900">{{ report.delivered_orders }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Gross Revenue</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.revenue) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Discounts Given</dt>
              <dd class="text-sm font-medium text-red-600">- MK {{ formatNumber(report.discounts) }}</dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">Net Revenue</dt>
              <dd class="text-sm font-bold text-gray-900">MK {{ formatNumber(report.revenue - report.discounts) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Expenses Breakdown -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Expenses Breakdown</h2>
        </div>
        <div class="p-6">
          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Cost of Goods Sold</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.cost_of_goods) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Purchase Costs (Transport, Packaging)</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.purchase_costs) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Delivery Costs</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.delivery_costs) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Operating Expenses</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.operating_expenses) }}</dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">Total Expenses</dt>
              <dd class="text-sm font-bold text-red-600">MK {{ formatNumber(report.total_expenses) }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Profit Analysis -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Profit Analysis</h2>
        </div>
        <div class="p-6">
          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Gross Profit</dt>
              <dd class="text-sm font-medium" :class="report.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                MK {{ formatNumber(report.gross_profit) }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Gross Margin</dt>
              <dd class="text-sm font-medium" :class="report.gross_margin >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ report.gross_margin?.toFixed(1) }}%
              </dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">Net Profit</dt>
              <dd class="text-sm font-bold" :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                MK {{ formatNumber(report.net_profit) }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm font-medium text-gray-900">Net Margin</dt>
              <dd class="text-sm font-bold" :class="report.net_margin >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ report.net_margin?.toFixed(1) }}%
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Purchase Summary -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Purchase Summary</h2>
        </div>
        <div class="p-6">
          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Total Purchases</dt>
              <dd class="text-sm font-medium text-gray-900">{{ report.total_purchases }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Quantity Purchased</dt>
              <dd class="text-sm font-medium text-gray-900">{{ formatNumber(report.quantity_purchased) }} kg</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Purchase Value</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.purchase_value) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Average Cost per kg</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.avg_purchase_cost) }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Profit by Product -->
    <div v-if="productProfit?.length" class="mt-6 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Profit by Product</h2>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Qty Sold</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Revenue</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">COGS</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Profit</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Margin</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="product in productProfit" :key="product.product_id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ product.product_name }}</div>
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
            <td class="whitespace-nowrap px-6 py-4 text-sm" :class="product.margin >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ product.margin?.toFixed(1) }}%
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/components/Loader.vue'
import {
  CurrencyDollarIcon,
  ShoppingBagIcon,
  ArrowTrendingUpIcon,
  BanknotesIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const report = ref(null)
const productProfit = ref([])

const currentDate = new Date()
const selectedYear = ref(currentDate.getFullYear())
const selectedMonth = ref(currentDate.getMonth() + 1)

const years = Array.from({ length: 5 }, (_, i) => currentDate.getFullYear() - i)
const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const fetchReport = async () => {
  processing.value = true
  try {
    const [reportRes, productRes] = await Promise.all([
      axios.get('/api/legume/reports/monthly', {
        params: { year: selectedYear.value, month: selectedMonth.value }
      }),
      axios.get('/api/legume/reports/by-product', {
        params: { year: selectedYear.value, month: selectedMonth.value }
      })
    ])

    report.value = reportRes.data.data
    productProfit.value = productRes.data.data
  } catch (error) {
    console.error('Error fetching report:', error)
  } finally {
    processing.value = false
  }
}

onMounted(() => fetchReport())
</script>
