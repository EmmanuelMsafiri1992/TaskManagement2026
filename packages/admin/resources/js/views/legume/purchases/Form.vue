<template>
  <div class="mx-auto max-w-4xl">
    <div class="mb-6">
      <router-link to="/legume/purchases" class="text-sm text-indigo-600 hover:text-indigo-500">
        &larr; Back to Purchases
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">New Purchase</h1>
      <p class="text-sm text-gray-500">Record a purchase from a farmer/supplier</p>
    </div>

    <!-- Budget Info -->
    <div class="mb-6 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 p-4 text-white shadow">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-indigo-100">Available Budget</p>
          <p class="text-2xl font-bold">MK {{ formatNumber(currentBudget) }}</p>
        </div>
        <BanknotesIcon class="h-10 w-10 text-white opacity-75" />
      </div>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Supplier & Product Selection -->
      <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Supplier & Product</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier *</label>
            <select
              v-model="form.supplier_id"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">Select a supplier</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.name }} - {{ supplier.district }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Product *</label>
            <select
              v-model="form.legume_product_id"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="onProductChange"
            >
              <option value="">Select a product</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} ({{ product.sku }}) - MK {{ formatNumber(product.buying_price) }}/kg
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Purchase Date *</label>
            <input
              v-model="form.purchase_date"
              type="date"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Quality Grade *</label>
            <select
              v-model="form.quality_grade"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="A">Grade A - Premium</option>
              <option value="B">Grade B - Standard</option>
              <option value="C">Grade C - Economy</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Weighing & Pricing -->
      <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Weighing & Pricing</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Quantity (kg) *</label>
            <input
              v-model.number="form.quantity"
              type="number"
              step="0.001"
              min="0.001"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Enter weight in kg"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Price per kg (MK) *</label>
            <input
              v-model.number="form.price_per_unit"
              type="number"
              step="0.01"
              min="0"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Price per kg"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Product Total</label>
            <div class="mt-1 rounded-md bg-gray-100 px-3 py-2 text-lg font-semibold text-gray-900">
              MK {{ formatNumber(productTotal) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Costs -->
      <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Additional Costs</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-gray-700">Packaging Cost (MK)</label>
            <input
              v-model.number="form.packaging_cost"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Transport Cost (MK)</label>
            <input
              v-model.number="form.transport_cost"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Other Costs (MK)</label>
            <input
              v-model.number="form.other_costs"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="0.00"
            />
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Notes</h2>
        <textarea
          v-model="form.notes"
          rows="3"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          placeholder="Any additional notes about this purchase..."
        ></textarea>
      </div>

      <!-- Summary -->
      <div class="rounded-lg bg-gray-50 p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Purchase Summary</h2>

        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Product Total ({{ form.quantity || 0 }} kg × MK {{ formatNumber(form.price_per_unit || 0) }})</span>
            <span class="text-gray-900">MK {{ formatNumber(productTotal) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Packaging Cost</span>
            <span class="text-gray-900">MK {{ formatNumber(form.packaging_cost || 0) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Transport Cost</span>
            <span class="text-gray-900">MK {{ formatNumber(form.transport_cost || 0) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Other Costs</span>
            <span class="text-gray-900">MK {{ formatNumber(form.other_costs || 0) }}</span>
          </div>
          <div class="border-t border-gray-200 pt-2">
            <div class="flex justify-between text-lg font-bold">
              <span class="text-gray-900">Grand Total</span>
              <span :class="grandTotal > currentBudget ? 'text-red-600' : 'text-green-600'">
                MK {{ formatNumber(grandTotal) }}
              </span>
            </div>
          </div>
          <div v-if="grandTotal > currentBudget" class="mt-2 rounded-md bg-red-50 p-3 text-sm text-red-700">
            Warning: Purchase total exceeds available budget
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3">
        <router-link
          to="/legume/purchases"
          class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="saving || grandTotal > currentBudget"
          class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ saving ? 'Saving...' : 'Create Purchase' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { BanknotesIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()

const saving = ref(false)
const suppliers = ref([])
const products = ref([])
const currentBudget = ref(0)

const form = ref({
  supplier_id: '',
  legume_product_id: '',
  purchase_date: new Date().toISOString().split('T')[0],
  quantity: '',
  price_per_unit: '',
  packaging_cost: 0,
  transport_cost: 0,
  other_costs: 0,
  quality_grade: 'A',
  notes: ''
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const productTotal = computed(() => {
  return (form.value.quantity || 0) * (form.value.price_per_unit || 0)
})

const grandTotal = computed(() => {
  return productTotal.value +
    (form.value.packaging_cost || 0) +
    (form.value.transport_cost || 0) +
    (form.value.other_costs || 0)
})

const onProductChange = () => {
  const product = products.value.find(p => p.id === form.value.legume_product_id)
  if (product) {
    form.value.price_per_unit = product.buying_price
  }
}

const fetchData = async () => {
  try {
    const [suppliersRes, productsRes, budgetRes] = await Promise.all([
      axios.get('/api/legume/suppliers/dropdown'),
      axios.get('/api/legume/products/dropdown'),
      axios.get('/api/legume/budget/current')
    ])

    suppliers.value = suppliersRes.data.data
    products.value = productsRes.data.data
    currentBudget.value = budgetRes.data.data?.current_budget || 0

    // Pre-select product from query param
    if (route.query.product) {
      form.value.legume_product_id = parseInt(route.query.product)
      onProductChange()
    }
  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

const submitForm = async () => {
  saving.value = true
  try {
    await axios.post('/api/legume/purchases', form.value)
    router.push('/legume/purchases')
  } catch (error) {
    console.error('Error creating purchase:', error)
    alert(error.response?.data?.message || 'Error creating purchase')
  } finally {
    saving.value = false
  }
}

onMounted(() => fetchData())
</script>
