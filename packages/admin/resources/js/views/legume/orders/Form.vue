<template>
  <div class="mx-auto max-w-5xl">
    <div class="mb-6">
      <router-link to="/legume/orders" class="text-sm text-indigo-600 hover:text-indigo-500">
        &larr; Back to Orders
      </router-link>
      <h1 class="mt-2 text-2xl font-bold text-gray-900">New Order</h1>
      <p class="text-sm text-gray-500">Create a new customer order</p>
    </div>

    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Customer Selection -->
      <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Customer Information</h2>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Customer *</label>
            <select
              v-model="form.legume_customer_id"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              @change="onCustomerChange"
            >
              <option value="">Select a customer</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                {{ customer.name }} - {{ customer.phone }}
              </option>
            </select>
            <button type="button" @click="showNewCustomerModal = true" class="mt-1 text-sm text-indigo-600 hover:text-indigo-500">
              + Add new customer
            </button>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Order Date *</label>
            <input
              v-model="form.order_date"
              type="date"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Fulfillment Type *</label>
            <select
              v-model="form.fulfillment_type"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="pickup">Pickup</option>
              <option value="delivery">Delivery</option>
            </select>
          </div>

          <div v-if="form.fulfillment_type === 'delivery'">
            <label class="block text-sm font-medium text-gray-700">Delivery Fee (MK)</label>
            <input
              v-model.number="form.delivery_fee"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="0.00"
            />
          </div>
        </div>

        <div v-if="form.fulfillment_type === 'delivery'" class="mt-4">
          <label class="block text-sm font-medium text-gray-700">Delivery Address</label>
          <textarea
            v-model="form.delivery_address"
            rows="2"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            placeholder="Enter delivery address..."
          ></textarea>
        </div>
      </div>

      <!-- Order Items -->
      <div class="rounded-lg bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
        </div>

        <!-- Add Item -->
        <div class="mb-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Product</label>
              <select
                v-model="newItem.legume_product_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @change="onNewItemProductChange"
              >
                <option value="">Select product</option>
                <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                  {{ product.name }} ({{ formatNumber(product.current_stock) }} kg)
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Quantity (kg)</label>
              <input
                v-model.number="newItem.quantity"
                type="number"
                step="0.001"
                min="0.001"
                :max="getMaxQuantity(newItem.legume_product_id)"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="0.000"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">Unit Price (MK)</label>
              <input
                v-model.number="newItem.unit_price"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="0.00"
              />
            </div>

            <div class="flex items-end">
              <button
                type="button"
                @click="addItem"
                :disabled="!canAddItem"
                class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
              >
                Add Item
              </button>
            </div>
          </div>
        </div>

        <!-- Items Table -->
        <table v-if="orderItems.length > 0" class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Product</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Quantity</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Unit Price</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Total</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="(item, index) in orderItems" :key="index">
              <td class="whitespace-nowrap px-4 py-3">
                <div class="font-medium text-gray-900">{{ getProductName(item.legume_product_id) }}</div>
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                {{ formatNumber(item.quantity) }} kg
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">
                MK {{ formatNumber(item.unit_price) }}
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900">
                MK {{ formatNumber(item.quantity * item.unit_price) }}
              </td>
              <td class="whitespace-nowrap px-4 py-3 text-right">
                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                  Remove
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="py-8 text-center text-gray-500">
          No items added yet. Add products above.
        </div>
      </div>

      <!-- Discount -->
      <div class="rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Discount</h2>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-gray-700">Discount Amount (MK)</label>
            <input
              v-model.number="form.discount_amount"
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
          placeholder="Any additional notes..."
        ></textarea>
      </div>

      <!-- Summary -->
      <div class="rounded-lg bg-gray-50 p-6 shadow">
        <h2 class="mb-4 text-lg font-medium text-gray-900">Order Summary</h2>

        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Subtotal ({{ orderItems.length }} items)</span>
            <span class="text-gray-900">MK {{ formatNumber(subtotal) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Discount</span>
            <span class="text-red-600">- MK {{ formatNumber(form.discount_amount || 0) }}</span>
          </div>
          <div v-if="form.fulfillment_type === 'delivery'" class="flex justify-between text-sm">
            <span class="text-gray-500">Delivery Fee</span>
            <span class="text-gray-900">MK {{ formatNumber(form.delivery_fee || 0) }}</span>
          </div>
          <div class="border-t border-gray-200 pt-2">
            <div class="flex justify-between text-lg font-bold">
              <span class="text-gray-900">Total</span>
              <span class="text-green-600">MK {{ formatNumber(totalAmount) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-3">
        <router-link
          to="/legume/orders"
          class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        >
          Cancel
        </router-link>
        <button
          type="submit"
          :disabled="saving || orderItems.length === 0"
          class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ saving ? 'Creating...' : 'Create Order' }}
        </button>
      </div>
    </form>

    <!-- New Customer Modal -->
    <div v-if="showNewCustomerModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showNewCustomerModal = false"></div>

        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">Add New Customer</h3>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input v-model="newCustomer.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Phone *</label>
              <input v-model="newCustomer.phone" type="tel" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input v-model="newCustomer.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Address</label>
              <textarea v-model="newCustomer.address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
            </div>
          </div>

          <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="showNewCustomerModal = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
              Cancel
            </button>
            <button type="button" @click="saveNewCustomer" :disabled="savingCustomer" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
              {{ savingCustomer ? 'Saving...' : 'Save Customer' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const saving = ref(false)
const customers = ref([])
const products = ref([])
const orderItems = ref([])
const showNewCustomerModal = ref(false)
const savingCustomer = ref(false)

const form = ref({
  legume_customer_id: '',
  order_date: new Date().toISOString().split('T')[0],
  fulfillment_type: 'pickup',
  delivery_fee: 0,
  delivery_address: '',
  discount_amount: 0,
  notes: ''
})

const newItem = ref({
  legume_product_id: '',
  quantity: '',
  unit_price: ''
})

const newCustomer = ref({
  name: '',
  phone: '',
  email: '',
  address: ''
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const availableProducts = computed(() => {
  const usedProductIds = orderItems.value.map(item => item.legume_product_id)
  return products.value.filter(p => !usedProductIds.includes(p.id) && p.current_stock > 0)
})

const canAddItem = computed(() => {
  return newItem.value.legume_product_id &&
         newItem.value.quantity > 0 &&
         newItem.value.unit_price > 0 &&
         newItem.value.quantity <= getMaxQuantity(newItem.value.legume_product_id)
})

const subtotal = computed(() => {
  return orderItems.value.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0)
})

const totalAmount = computed(() => {
  let total = subtotal.value - (form.value.discount_amount || 0)
  if (form.value.fulfillment_type === 'delivery') {
    total += form.value.delivery_fee || 0
  }
  return Math.max(0, total)
})

const getProductName = (productId) => {
  const product = products.value.find(p => p.id === productId)
  return product ? product.name : 'Unknown'
}

const getMaxQuantity = (productId) => {
  const product = products.value.find(p => p.id === productId)
  return product ? product.current_stock : 0
}

const onCustomerChange = () => {
  const customer = customers.value.find(c => c.id === form.value.legume_customer_id)
  if (customer && customer.address) {
    form.value.delivery_address = customer.address
  }
}

const onNewItemProductChange = () => {
  const product = products.value.find(p => p.id === newItem.value.legume_product_id)
  if (product) {
    newItem.value.unit_price = product.selling_price
  }
}

const addItem = () => {
  if (!canAddItem.value) return

  orderItems.value.push({
    legume_product_id: newItem.value.legume_product_id,
    quantity: newItem.value.quantity,
    unit_price: newItem.value.unit_price
  })

  newItem.value = { legume_product_id: '', quantity: '', unit_price: '' }
}

const removeItem = (index) => {
  orderItems.value.splice(index, 1)
}

const fetchData = async () => {
  try {
    const [customersRes, productsRes] = await Promise.all([
      axios.get('/api/legume/customers/dropdown'),
      axios.get('/api/legume/products/dropdown')
    ])

    customers.value = customersRes.data.data
    products.value = productsRes.data.data
  } catch (error) {
    console.error('Error fetching data:', error)
  }
}

const saveNewCustomer = async () => {
  savingCustomer.value = true
  try {
    const response = await axios.post('/api/legume/customers', newCustomer.value)
    customers.value.push(response.data.data)
    form.value.legume_customer_id = response.data.data.id
    showNewCustomerModal.value = false
    newCustomer.value = { name: '', phone: '', email: '', address: '' }
  } catch (error) {
    console.error('Error saving customer:', error)
    alert(error.response?.data?.message || 'Error saving customer')
  } finally {
    savingCustomer.value = false
  }
}

const submitForm = async () => {
  if (orderItems.value.length === 0) {
    alert('Please add at least one item')
    return
  }

  saving.value = true
  try {
    const payload = {
      ...form.value,
      items: orderItems.value
    }
    await axios.post('/api/legume/orders', payload)
    router.push('/legume/orders')
  } catch (error) {
    console.error('Error creating order:', error)
    alert(error.response?.data?.message || 'Error creating order')
  } finally {
    saving.value = false
  }
}

onMounted(() => fetchData())
</script>
