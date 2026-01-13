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
              <CubeIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Products</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.total_products }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ScaleIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Stock</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ formatNumber(statistics.total_stock) }} kg</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Stock Value</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.total_value) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Low Stock Items</dt>
                <dd class="text-lg font-semibold text-red-600">{{ statistics.low_stock_count }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter & Search -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-2">
        <div class="relative w-64 rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search products..."
            class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="filterStock"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchInventory"
        >
          <option value="">All Stock Levels</option>
          <option value="low">Low Stock</option>
          <option value="out">Out of Stock</option>
          <option value="available">In Stock</option>
        </select>
      </div>

      <div class="flex space-x-2">
        <router-link
          to="/legume/inventory/movements"
          class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        >
          <ClockIcon class="-ml-1 mr-2 h-5 w-5 text-gray-400" />
          Movement History
        </router-link>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Current Stock</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reserved</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Available</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Avg Cost</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Value</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="item in inventory" :key="item.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ item.product?.name }}</div>
              <div class="text-sm text-gray-500">{{ item.product?.sku }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
              {{ formatNumber(item.quantity) }} kg
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-yellow-600">
              {{ formatNumber(item.reserved_quantity) }} kg
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-green-600">
              {{ formatNumber(item.quantity - item.reserved_quantity) }} kg
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              MK {{ formatNumber(item.average_cost) }}/kg
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
              MK {{ formatNumber(item.quantity * item.average_cost) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getStockStatusClass(item)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                {{ getStockStatus(item) }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium space-x-2">
              <button @click="openAdjustModal(item)" class="text-indigo-600 hover:text-indigo-900">Adjust</button>
              <router-link :to="`/legume/purchases/create?product=${item.legume_product_id}`" class="text-green-600 hover:text-green-900">Restock</router-link>
            </td>
          </tr>
          <tr v-if="inventory.length === 0">
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No inventory items found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Adjust Stock Modal -->
    <div v-if="showAdjustModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeAdjustModal"></div>

        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">Adjust Stock: {{ adjustingItem?.product?.name }}</h3>

          <div class="mb-4 rounded-md bg-gray-50 p-3">
            <p class="text-sm text-gray-600">Current Stock: <span class="font-medium">{{ formatNumber(adjustingItem?.quantity) }} kg</span></p>
          </div>

          <form @submit.prevent="saveAdjustment">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Adjustment Type *</label>
                <select v-model="adjustForm.type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="add">Add Stock</option>
                  <option value="remove">Remove Stock</option>
                  <option value="set">Set to Specific Amount</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Quantity (kg) *</label>
                <input
                  v-model.number="adjustForm.quantity"
                  type="number"
                  step="0.001"
                  min="0.001"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Reason *</label>
                <select v-model="adjustForm.reason" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="count_correction">Count Correction</option>
                  <option value="damage">Damage/Spoilage</option>
                  <option value="return">Customer Return</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea
                  v-model="adjustForm.notes"
                  rows="2"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                  placeholder="Additional notes..."
                ></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button type="button" @click="closeAdjustModal" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="adjusting" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                {{ adjusting ? 'Saving...' : 'Save Adjustment' }}
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
import Loader from '@/thetheme/components/Loader.vue'
import {
  CubeIcon,
  ScaleIcon,
  BanknotesIcon,
  ExclamationTriangleIcon,
  MagnifyingGlassIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const inventory = ref([])
const statistics = ref(null)
const searchQuery = ref('')
const filterStock = ref('')
const showAdjustModal = ref(false)
const adjustingItem = ref(null)
const adjusting = ref(false)

const adjustForm = ref({
  type: 'add',
  quantity: '',
  reason: 'count_correction',
  notes: ''
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const getStockStatus = (item) => {
  if (item.quantity <= 0) return 'Out of Stock'
  if (item.product?.low_stock_threshold && item.quantity <= item.product.low_stock_threshold) return 'Low Stock'
  return 'In Stock'
}

const getStockStatusClass = (item) => {
  if (item.quantity <= 0) return 'bg-red-100 text-red-800'
  if (item.product?.low_stock_threshold && item.quantity <= item.product.low_stock_threshold) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchInventory(), 300)
}

const fetchInventory = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (filterStock.value) params.stock_filter = filterStock.value

    const [inventoryRes, statsRes] = await Promise.all([
      axios.get('/api/legume/inventory', { params }),
      axios.get('/api/legume/inventory/statistics')
    ])

    inventory.value = inventoryRes.data.data
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching inventory:', error)
  } finally {
    processing.value = false
  }
}

const openAdjustModal = (item) => {
  adjustingItem.value = item
  adjustForm.value = {
    type: 'add',
    quantity: '',
    reason: 'count_correction',
    notes: ''
  }
  showAdjustModal.value = true
}

const closeAdjustModal = () => {
  showAdjustModal.value = false
  adjustingItem.value = null
}

const saveAdjustment = async () => {
  adjusting.value = true
  try {
    await axios.post(`/api/legume/inventory/${adjustingItem.value.id}/adjust`, adjustForm.value)
    closeAdjustModal()
    fetchInventory()
  } catch (error) {
    console.error('Error adjusting inventory:', error)
    alert(error.response?.data?.message || 'Error adjusting inventory')
  } finally {
    adjusting.value = false
  }
}

onMounted(() => fetchInventory())
</script>
