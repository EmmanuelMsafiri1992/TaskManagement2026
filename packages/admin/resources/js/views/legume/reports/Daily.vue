<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Daily Report</h1>
      <p class="text-sm text-gray-500">Track daily sales and profit</p>
    </div>

    <!-- Date Selector -->
    <div class="mb-6">
      <input
        v-model="selectedDate"
        type="date"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchReport"
      />
    </div>

    <!-- Summary Cards -->
    <div v-if="report" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Orders</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">{{ report.total_orders }}</p>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Revenue</p>
          <p class="mt-1 text-2xl font-semibold text-green-600">MK {{ formatNumber(report.revenue) }}</p>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Cost of Goods</p>
          <p class="mt-1 text-2xl font-semibold text-gray-900">MK {{ formatNumber(report.cost_of_goods) }}</p>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-gray-500">Gross Profit</p>
          <p class="mt-1 text-2xl font-semibold" :class="report.gross_profit >= 0 ? 'text-green-600' : 'text-red-600'">
            MK {{ formatNumber(report.gross_profit) }}
          </p>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 shadow">
        <div class="p-5">
          <p class="text-sm font-medium text-indigo-100">Net Profit</p>
          <p class="mt-1 text-2xl font-bold text-white">MK {{ formatNumber(report.net_profit) }}</p>
        </div>
      </div>
    </div>

    <div v-if="report" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Details -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Day Summary</h2>
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
              <dt class="text-sm text-gray-500">Quantity Sold</dt>
              <dd class="text-sm font-medium text-gray-900">{{ formatNumber(report.quantity_sold) }} kg</dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm text-gray-500">Revenue</dt>
              <dd class="text-sm font-medium text-green-600">MK {{ formatNumber(report.revenue) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Cost of Goods</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.cost_of_goods) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Operating Expenses</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(report.operating_expenses) }}</dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">Net Profit</dt>
              <dd class="text-sm font-bold" :class="report.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                MK {{ formatNumber(report.net_profit) }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm font-medium text-gray-900">Profit Margin</dt>
              <dd class="text-sm font-bold" :class="report.net_margin >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ report.net_margin?.toFixed(1) }}%
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Purchases Today -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Purchases Today</h2>
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
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm text-gray-500">Payments Received</dt>
              <dd class="text-sm font-medium text-green-600">MK {{ formatNumber(report.payments_received) }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div v-if="report?.orders?.length" class="mt-6 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Orders on {{ formatDate(selectedDate) }}</h2>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Order</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="order in report.orders" :key="order.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <router-link :to="`/legume/orders/${order.id}`" class="font-medium text-indigo-600 hover:text-indigo-900">
                {{ order.order_number }}
              </router-link>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ order.customer?.name }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
              MK {{ formatNumber(order.total_amount) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getStatusClass(order.order_status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                {{ order.order_status }}
              </span>
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
import Loader from '@/thetheme/components/Loader.vue'

const processing = ref(true)
const report = ref(null)
const selectedDate = ref(new Date().toISOString().split('T')[0])

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-MW', { year: 'numeric', month: 'long', day: 'numeric' })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-gray-100 text-gray-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-yellow-100 text-yellow-800',
    ready: 'bg-purple-100 text-purple-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchReport = async () => {
  processing.value = true
  try {
    const response = await axios.get('/api/legume/reports/daily', {
      params: { date: selectedDate.value }
    })
    report.value = response.data.data
  } catch (error) {
    console.error('Error fetching report:', error)
  } finally {
    processing.value = false
  }
}

onMounted(() => fetchReport())
</script>
