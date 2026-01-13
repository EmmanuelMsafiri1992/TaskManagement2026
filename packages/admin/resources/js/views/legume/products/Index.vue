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
              <CheckCircleIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Active Products</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.active_products }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Low Stock</dt>
                <dd class="text-lg font-semibold text-yellow-600">{{ statistics.low_stock }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">Inventory Value</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.total_inventory_value) }}</dd>
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
            placeholder="Search products..."
            class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="filterActive"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchProducts"
        >
          <option value="">All Status</option>
          <option value="1">Active</option>
          <option value="0">Inactive</option>
        </select>
      </div>

      <div class="ml-auto">
        <button
          type="button"
          class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          @click="openModal()"
        >
          <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
          Add Product
        </button>
      </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">SKU</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Buying Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Selling Price</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Margin</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Stock</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ product.name }}</div>
              <div class="text-sm text-gray-500">{{ product.unit }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ product.sku }}</td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">MK {{ formatNumber(product.buying_price) }}</td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">MK {{ formatNumber(product.selling_price) }}</td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="product.profit_margin > 0 ? 'text-green-600' : 'text-red-600'" class="text-sm font-medium">
                {{ product.profit_margin }}%
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getStockClass(product)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                {{ formatNumber(product.current_stock) }} {{ product.unit }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                {{ product.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <button @click="openModal(product)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button @click="confirmDelete(product)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="products.length === 0">
            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No products found</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} results
          </div>
          <div class="flex space-x-2">
            <button
              v-for="page in pagination.last_page"
              :key="page"
              :class="page === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
              class="rounded-md border px-3 py-1 text-sm font-medium"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">
            {{ editingProduct ? 'Edit Product' : 'Add Product' }}
          </h3>

          <form @submit.prevent="saveProduct">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">SKU</label>
                  <input v-model="form.sku" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Auto-generated" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Unit</label>
                  <select v-model="form.unit" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="bag">Bag</option>
                    <option value="piece">Piece</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Buying Price (MWK) *</label>
                  <input v-model="form.buying_price" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Selling Price (MWK) *</label>
                  <input v-model="form.selling_price" type="number" step="0.01" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Low Stock Threshold</label>
                <input v-model="form.low_stock_threshold" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="10" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>

              <div class="flex items-center">
                <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                <label class="ml-2 block text-sm text-gray-900">Active</label>
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
  CubeIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  BanknotesIcon,
  MagnifyingGlassIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const products = ref([])
const statistics = ref(null)
const pagination = ref({})
const searchQuery = ref('')
const filterActive = ref('')
const showModal = ref(false)
const editingProduct = ref(null)
const saving = ref(false)

const form = ref({
  name: '',
  sku: '',
  description: '',
  unit: 'kg',
  buying_price: '',
  selling_price: '',
  low_stock_threshold: 10,
  is_active: true
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const getStockClass = (product) => {
  const stock = product.current_stock || 0
  const threshold = product.low_stock_threshold || 10
  if (stock <= 0) return 'bg-red-100 text-red-800'
  if (stock <= threshold) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchProducts(), 300)
}

const fetchProducts = async (page = 1) => {
  try {
    const params = { page, per_page: 15 }
    if (searchQuery.value) params.search = searchQuery.value
    if (filterActive.value !== '') params.is_active = filterActive.value

    const [productsRes, statsRes] = await Promise.all([
      axios.get('/api/legume/products', { params }),
      axios.get('/api/legume/products/statistics')
    ])

    products.value = productsRes.data.data
    pagination.value = {
      current_page: productsRes.data.current_page,
      last_page: productsRes.data.last_page,
      from: productsRes.data.from,
      to: productsRes.data.to,
      total: productsRes.data.total
    }
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching products:', error)
  } finally {
    processing.value = false
  }
}

const goToPage = (page) => fetchProducts(page)

const openModal = (product = null) => {
  editingProduct.value = product
  if (product) {
    form.value = { ...product }
  } else {
    form.value = {
      name: '',
      sku: '',
      description: '',
      unit: 'kg',
      buying_price: '',
      selling_price: '',
      low_stock_threshold: 10,
      is_active: true
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingProduct.value = null
}

const saveProduct = async () => {
  saving.value = true
  try {
    if (editingProduct.value) {
      await axios.put(`/api/legume/products/${editingProduct.value.id}`, form.value)
    } else {
      await axios.post('/api/legume/products', form.value)
    }
    closeModal()
    fetchProducts()
  } catch (error) {
    console.error('Error saving product:', error)
    alert(error.response?.data?.message || 'Error saving product')
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (product) => {
  if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
    try {
      await axios.delete(`/api/legume/products/${product.id}`)
      fetchProducts()
    } catch (error) {
      alert(error.response?.data?.message || 'Error deleting product')
    }
  }
}

onMounted(() => fetchProducts())
</script>
