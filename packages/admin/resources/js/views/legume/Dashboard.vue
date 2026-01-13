<template>
  <div v-if="loading" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Legume Business Dashboard</h1>
      <p class="mt-1 text-sm text-gray-500">Overview of your legume trading business</p>
    </div>

    <!-- Budget and Today Stats -->
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-8 w-8 text-white" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-indigo-100">Available Budget</dt>
                <dd class="text-2xl font-bold text-white">MK {{ formatNumber(budget.current_budget) }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">Today's Revenue</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(dashboard.today?.revenue || 0) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowTrendingUpIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Today's Profit</dt>
                <dd class="text-lg font-semibold" :class="dashboard.today?.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                  MK {{ formatNumber(dashboard.today?.profit || 0) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ShoppingCartIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Today's Orders</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ dashboard.today?.orders || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Monthly Stats -->
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">This Month Revenue</p>
              <p class="mt-1 text-xl font-semibold text-gray-900">MK {{ formatNumber(dashboard.this_month?.revenue || 0) }}</p>
            </div>
            <span v-if="dashboard.growth?.revenue" :class="dashboard.growth.revenue >= 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
              {{ dashboard.growth.revenue >= 0 ? '+' : '' }}{{ dashboard.growth.revenue }}%
            </span>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">This Month Profit</p>
              <p class="mt-1 text-xl font-semibold" :class="dashboard.this_month?.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                MK {{ formatNumber(dashboard.this_month?.profit || 0) }}
              </p>
            </div>
            <span v-if="dashboard.growth?.profit" :class="dashboard.growth.profit >= 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
              {{ dashboard.growth.profit >= 0 ? '+' : '' }}{{ dashboard.growth.profit }}%
            </span>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div>
            <p class="text-sm font-medium text-gray-500">Pending Orders</p>
            <p class="mt-1 text-xl font-semibold text-yellow-600">{{ dashboard.pending_orders || 0 }}</p>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div>
            <p class="text-sm font-medium text-gray-500">Unpaid Orders</p>
            <p class="mt-1 text-xl font-semibold text-red-600">{{ dashboard.unpaid_orders || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <router-link to="/legume/purchases/create" class="flex items-center rounded-lg bg-indigo-600 p-4 text-white shadow hover:bg-indigo-700">
        <PlusIcon class="h-8 w-8" />
        <div class="ml-3">
          <p class="font-semibold">New Purchase</p>
          <p class="text-sm text-indigo-200">Buy from farmer</p>
        </div>
      </router-link>

      <router-link to="/legume/orders/create" class="flex items-center rounded-lg bg-green-600 p-4 text-white shadow hover:bg-green-700">
        <ShoppingCartIcon class="h-8 w-8" />
        <div class="ml-3">
          <p class="font-semibold">New Order</p>
          <p class="text-sm text-green-200">Create sale order</p>
        </div>
      </router-link>

      <router-link to="/legume/budget" class="flex items-center rounded-lg bg-purple-600 p-4 text-white shadow hover:bg-purple-700">
        <BanknotesIcon class="h-8 w-8" />
        <div class="ml-3">
          <p class="font-semibold">Add Budget</p>
          <p class="text-sm text-purple-200">Add capital</p>
        </div>
      </router-link>

      <router-link to="/legume/reports/monthly" class="flex items-center rounded-lg bg-yellow-600 p-4 text-white shadow hover:bg-yellow-700">
        <ChartBarIcon class="h-8 w-8" />
        <div class="ml-3">
          <p class="font-semibold">View Reports</p>
          <p class="text-sm text-yellow-200">Profit analysis</p>
        </div>
      </router-link>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Recent Orders -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Recent Orders</h2>
            <router-link to="/legume/orders" class="text-sm text-indigo-600 hover:text-indigo-500">View all</router-link>
          </div>
        </div>
        <div class="divide-y divide-gray-200">
          <div v-for="order in dashboard.recent_orders" :key="order.id" class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900">{{ order.order_number }}</p>
                <p class="text-sm text-gray-500">{{ order.customer_name }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-900">MK {{ formatNumber(order.total_amount) }}</p>
                <span :class="getOrderStatusClass(order.order_status)" class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium">
                  {{ order.order_status }}
                </span>
              </div>
            </div>
          </div>
          <div v-if="!dashboard.recent_orders?.length" class="px-6 py-8 text-center text-gray-500">
            No recent orders
          </div>
        </div>
      </div>

      <!-- Top Products -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Top Products (This Month)</h2>
            <router-link to="/legume/reports/by-product" class="text-sm text-indigo-600 hover:text-indigo-500">View details</router-link>
          </div>
        </div>
        <div class="divide-y divide-gray-200">
          <div v-for="product in dashboard.top_products" :key="product.product_id" class="px-6 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900">{{ product.product_name }}</p>
                <p class="text-sm text-gray-500">{{ formatNumber(product.quantity_sold) }} kg sold</p>
              </div>
              <div class="text-right">
                <p class="font-semibold text-green-600">MK {{ formatNumber(product.profit) }}</p>
                <p class="text-sm text-gray-500">{{ product.margin }}% margin</p>
              </div>
            </div>
          </div>
          <div v-if="!dashboard.top_products?.length" class="px-6 py-8 text-center text-gray-500">
            No sales data yet
          </div>
        </div>
      </div>
    </div>

    <!-- Stock Alerts -->
    <div v-if="stockAlerts.length > 0" class="mt-6 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 bg-red-50 px-6 py-4">
        <div class="flex items-center">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
          <h2 class="ml-2 text-lg font-medium text-red-800">Stock Alerts</h2>
        </div>
      </div>
      <div class="divide-y divide-gray-200">
        <div v-for="alert in stockAlerts" :key="alert.id" class="flex items-center justify-between px-6 py-4">
          <div>
            <p class="font-medium text-gray-900">{{ alert.name }}</p>
            <span :class="alert.is_out_of_stock ? 'text-red-600' : 'text-yellow-600'" class="text-sm">
              {{ alert.is_out_of_stock ? 'Out of Stock' : 'Low Stock' }} - {{ formatNumber(alert.current_stock) }} kg remaining
            </span>
          </div>
          <router-link :to="`/legume/purchases/create?product=${alert.id}`" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
            Restock
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'
import {
  BanknotesIcon,
  CurrencyDollarIcon,
  ArrowTrendingUpIcon,
  ShoppingCartIcon,
  PlusIcon,
  ChartBarIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const loading = ref(true)
const dashboard = ref({})
const budget = ref({ current_budget: 0 })
const stockAlerts = ref([])

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const getOrderStatusClass = (status) => {
  const classes = {
    pending: 'bg-gray-100 text-gray-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-yellow-100 text-yellow-800',
    ready: 'bg-purple-100 text-purple-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchData = async () => {
  try {
    const [dashboardRes, budgetRes, alertsRes] = await Promise.all([
      axios.get('/api/legume/reports/dashboard'),
      axios.get('/api/legume/budget/current'),
      axios.get('/api/legume/inventory/low-stock')
    ])

    dashboard.value = dashboardRes.data.data || {}
    budget.value = budgetRes.data.data || { current_budget: 0 }
    stockAlerts.value = alertsRes.data.data || []
  } catch (error) {
    console.error('Error fetching dashboard data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>
