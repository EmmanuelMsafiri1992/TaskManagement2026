<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <div class="mb-6">
      <router-link to="/legume/inventory" class="text-sm text-indigo-600 hover:text-indigo-500">
        &larr; Back to Inventory
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">Stock Movement History</h1>
      <p class="text-sm text-gray-500">Track all inventory changes</p>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
      <select
        v-model="filterProduct"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchMovements"
      >
        <option value="">All Products</option>
        <option v-for="product in products" :key="product.id" :value="product.id">
          {{ product.name }}
        </option>
      </select>

      <select
        v-model="filterType"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchMovements"
      >
        <option value="">All Types</option>
        <option value="purchase">Purchase</option>
        <option value="sale">Sale</option>
        <option value="adjustment">Adjustment</option>
        <option value="damage">Damage</option>
        <option value="return">Return</option>
      </select>

      <input
        v-model="filterDateFrom"
        type="date"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchMovements"
      />

      <input
        v-model="filterDateTo"
        type="date"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchMovements"
      />
    </div>

    <!-- Movements Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Quantity</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Unit Cost</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Balance After</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reference</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="movement in movements" :key="movement.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ formatDateTime(movement.created_at) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ movement.product?.name }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getTypeClass(movement.type)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                {{ movement.type }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="movement.quantity >= 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                {{ movement.quantity >= 0 ? '+' : '' }}{{ formatNumber(movement.quantity) }} kg
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              MK {{ formatNumber(movement.unit_cost) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
              {{ formatNumber(movement.balance_after) }} kg
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              <span v-if="movement.reference_type">
                {{ getRefLabel(movement.reference_type) }} #{{ movement.reference_id }}
              </span>
              <span v-else>-</span>
            </td>
          </tr>
          <tr v-if="movements.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No movements found</td>
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
const movements = ref([])
const products = ref([])
const filterProduct = ref('')
const filterType = ref('')
const filterDateFrom = ref('')
const filterDateTo = ref('')

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString('en-MW', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTypeClass = (type) => {
  const classes = {
    purchase: 'bg-green-100 text-green-800',
    sale: 'bg-blue-100 text-blue-800',
    adjustment: 'bg-yellow-100 text-yellow-800',
    damage: 'bg-red-100 text-red-800',
    return: 'bg-purple-100 text-purple-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getRefLabel = (type) => {
  if (type.includes('Purchase')) return 'Purchase'
  if (type.includes('OrderItem')) return 'Order'
  return type
}

const fetchMovements = async () => {
  try {
    const params = {}
    if (filterProduct.value) params.product_id = filterProduct.value
    if (filterType.value) params.type = filterType.value
    if (filterDateFrom.value) params.date_from = filterDateFrom.value
    if (filterDateTo.value) params.date_to = filterDateTo.value

    const response = await axios.get('/api/legume/inventory/movements', { params })
    movements.value = response.data.data
  } catch (error) {
    console.error('Error fetching movements:', error)
  } finally {
    processing.value = false
  }
}

const fetchProducts = async () => {
  try {
    const response = await axios.get('/api/legume/products/dropdown')
    products.value = response.data.data
  } catch (error) {
    console.error('Error fetching products:', error)
  }
}

onMounted(() => {
  fetchProducts()
  fetchMovements()
})
</script>
