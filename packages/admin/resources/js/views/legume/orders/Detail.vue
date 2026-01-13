<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else-if="order">
    <div class="mb-6">
      <router-link to="/legume/orders" class="text-sm text-indigo-600 hover:text-indigo-500">
        &larr; Back to Orders
      </router-link>
      <div class="mt-2 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ order.order_number }}</h1>
          <p class="text-sm text-gray-500">Order placed on {{ formatDate(order.order_date) }}</p>
        </div>
        <div class="flex items-center space-x-3">
          <span :class="getOrderStatusClass(order.order_status)" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize">
            {{ order.order_status }}
          </span>
          <span :class="getPaymentStatusClass(order.payment_status)" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium capitalize">
            {{ order.payment_status }}
          </span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Order Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
          </div>
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Quantity</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Unit Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in order.items" :key="item.id">
                <td class="whitespace-nowrap px-6 py-4">
                  <div class="font-medium text-gray-900">{{ item.product?.name }}</div>
                  <div class="text-sm text-gray-500">{{ item.product?.sku }}</div>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                  {{ formatNumber(item.quantity) }} kg
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                  MK {{ formatNumber(item.unit_price) }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                  MK {{ formatNumber(item.total) }}
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50">
              <tr>
                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-900">MK {{ formatNumber(order.subtotal) }}</td>
              </tr>
              <tr v-if="order.discount_amount > 0">
                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Discount</td>
                <td class="px-6 py-3 text-sm font-medium text-red-600">- MK {{ formatNumber(order.discount_amount) }}</td>
              </tr>
              <tr v-if="order.delivery_fee > 0">
                <td colspan="3" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Delivery Fee</td>
                <td class="px-6 py-3 text-sm font-medium text-gray-900">MK {{ formatNumber(order.delivery_fee) }}</td>
              </tr>
              <tr class="border-t-2 border-gray-200">
                <td colspan="3" class="px-6 py-3 text-right text-lg font-bold text-gray-900">Total</td>
                <td class="px-6 py-3 text-lg font-bold text-gray-900">MK {{ formatNumber(order.total_amount) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- Payment History -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">Payment History</h2>
              <button
                v-if="order.payment_status !== 'paid'"
                @click="showPaymentModal = true"
                class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
              >
                Record Payment
              </button>
            </div>
          </div>
          <div class="divide-y divide-gray-200">
            <div v-for="payment in order.payments" :key="payment.id" class="flex items-center justify-between px-6 py-4">
              <div>
                <div class="font-medium text-gray-900">{{ payment.payment_reference }}</div>
                <div class="text-sm text-gray-500">
                  {{ getPaymentMethodLabel(payment.payment_method) }} - {{ formatDate(payment.payment_date) }}
                </div>
                <div v-if="payment.transaction_id" class="text-xs text-gray-400">
                  Trans ID: {{ payment.transaction_id }}
                </div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-green-600">MK {{ formatNumber(payment.amount) }}</div>
                <span :class="payment.status === 'completed' ? 'text-green-600' : 'text-yellow-600'" class="text-xs capitalize">
                  {{ payment.status }}
                </span>
              </div>
            </div>
            <div v-if="!order.payments?.length" class="px-6 py-8 text-center text-gray-500">
              No payments recorded yet
            </div>
          </div>
          <div v-if="order.amount_paid > 0" class="border-t bg-gray-50 px-6 py-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Amount Paid</span>
              <span class="font-medium text-green-600">MK {{ formatNumber(order.amount_paid) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Balance Due</span>
              <span class="font-medium text-red-600">MK {{ formatNumber(order.total_amount - order.amount_paid) }}</span>
            </div>
          </div>
        </div>

        <!-- Delivery Info -->
        <div v-if="order.fulfillment_type === 'delivery'" class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center justify-between">
              <h2 class="text-lg font-medium text-gray-900">Delivery Information</h2>
              <button
                v-if="!order.delivery"
                @click="showDeliveryModal = true"
                class="rounded-md bg-blue-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-blue-700"
              >
                Add Delivery
              </button>
            </div>
          </div>
          <div v-if="order.delivery" class="p-6">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Status</p>
                <span :class="getDeliveryStatusClass(order.delivery.status)" class="mt-1 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                  {{ order.delivery.status }}
                </span>
              </div>
              <div>
                <p class="text-sm text-gray-500">Driver</p>
                <p class="font-medium text-gray-900">{{ order.delivery.driver_name || '-' }}</p>
                <p v-if="order.delivery.driver_phone" class="text-sm text-gray-500">{{ order.delivery.driver_phone }}</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-gray-500">Delivery Address</p>
                <p class="font-medium text-gray-900">{{ order.delivery.delivery_address || order.delivery_address }}</p>
              </div>
              <div v-if="order.delivery.estimated_delivery">
                <p class="text-sm text-gray-500">Estimated Delivery</p>
                <p class="font-medium text-gray-900">{{ formatDateTime(order.delivery.estimated_delivery) }}</p>
              </div>
              <div v-if="order.delivery.actual_delivery">
                <p class="text-sm text-gray-500">Delivered At</p>
                <p class="font-medium text-green-600">{{ formatDateTime(order.delivery.actual_delivery) }}</p>
              </div>
            </div>
            <div v-if="order.delivery.status !== 'delivered'" class="mt-4 pt-4 border-t">
              <button @click="showDeliveryModal = true" class="text-sm text-indigo-600 hover:text-indigo-500">
                Update Delivery Status
              </button>
            </div>
          </div>
          <div v-else class="px-6 py-8 text-center text-gray-500">
            No delivery information yet
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Customer Info -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-medium text-gray-900">Customer</h2>
          </div>
          <div class="p-6">
            <div class="font-medium text-gray-900">{{ order.customer?.name }}</div>
            <div class="mt-1 text-sm text-gray-500">{{ order.customer?.phone }}</div>
            <div v-if="order.customer?.email" class="text-sm text-gray-500">{{ order.customer?.email }}</div>
            <div v-if="order.customer?.address" class="mt-2 text-sm text-gray-500">{{ order.customer?.address }}</div>
          </div>
        </div>

        <!-- Order Status Update -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-medium text-gray-900">Update Status</h2>
          </div>
          <div class="p-6">
            <select
              v-model="selectedStatus"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="processing">Processing</option>
              <option value="ready">Ready</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
            <button
              @click="updateOrderStatus"
              :disabled="updatingStatus || selectedStatus === order.order_status"
              class="mt-3 w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
            >
              {{ updatingStatus ? 'Updating...' : 'Update Status' }}
            </button>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="order.notes" class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-medium text-gray-900">Notes</h2>
          </div>
          <div class="p-6">
            <p class="text-sm text-gray-700">{{ order.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showPaymentModal = false"></div>

        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">Record Payment</h3>

          <form @submit.prevent="savePayment">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Amount (MK) *</label>
                <input
                  v-model.number="paymentForm.amount"
                  type="number"
                  step="0.01"
                  :max="order.total_amount - order.amount_paid"
                  required
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                />
                <p class="mt-1 text-xs text-gray-500">Balance due: MK {{ formatNumber(order.total_amount - order.amount_paid) }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Payment Method *</label>
                <select v-model="paymentForm.payment_method" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="airtel_money">Airtel Money</option>
                  <option value="tnm_mpamba">TNM Mpamba</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div v-if="['airtel_money', 'tnm_mpamba'].includes(paymentForm.payment_method)">
                <label class="block text-sm font-medium text-gray-700">Transaction ID</label>
                <input v-model="paymentForm.transaction_id" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div v-if="['airtel_money', 'tnm_mpamba'].includes(paymentForm.payment_method)">
                <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input v-model="paymentForm.phone_number" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Payment Date *</label>
                <input v-model="paymentForm.payment_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button type="button" @click="showPaymentModal = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="savingPayment" class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 disabled:opacity-50">
                {{ savingPayment ? 'Saving...' : 'Save Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delivery Modal -->
    <div v-if="showDeliveryModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showDeliveryModal = false"></div>

        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">{{ order.delivery ? 'Update Delivery' : 'Add Delivery' }}</h3>

          <form @submit.prevent="saveDelivery">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Status *</label>
                <select v-model="deliveryForm.status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="pending">Pending</option>
                  <option value="assigned">Assigned</option>
                  <option value="picked_up">Picked Up</option>
                  <option value="in_transit">In Transit</option>
                  <option value="delivered">Delivered</option>
                  <option value="failed">Failed</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Driver Name</label>
                <input v-model="deliveryForm.driver_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Driver Phone</label>
                <input v-model="deliveryForm.driver_phone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Delivery Address</label>
                <textarea v-model="deliveryForm.delivery_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Delivery Cost (MK)</label>
                <input v-model.number="deliveryForm.delivery_cost" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Estimated Delivery</label>
                <input v-model="deliveryForm.estimated_delivery" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button type="button" @click="showDeliveryModal = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="savingDelivery" class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 disabled:opacity-50">
                {{ savingDelivery ? 'Saving...' : 'Save' }}
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
import { useRoute } from 'vue-router'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'

const route = useRoute()

const processing = ref(true)
const order = ref(null)
const selectedStatus = ref('')
const updatingStatus = ref(false)
const showPaymentModal = ref(false)
const savingPayment = ref(false)
const showDeliveryModal = ref(false)
const savingDelivery = ref(false)

const paymentForm = ref({
  amount: '',
  payment_method: 'cash',
  transaction_id: '',
  phone_number: '',
  payment_date: new Date().toISOString().split('T')[0]
})

const deliveryForm = ref({
  status: 'pending',
  driver_name: '',
  driver_phone: '',
  delivery_address: '',
  delivery_cost: 0,
  estimated_delivery: ''
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-MW', { year: 'numeric', month: 'short', day: 'numeric' })
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

const getOrderStatusClass = (status) => {
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

const getPaymentStatusClass = (status) => {
  const classes = {
    unpaid: 'bg-red-100 text-red-800',
    partial: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    refunded: 'bg-gray-100 text-gray-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getDeliveryStatusClass = (status) => {
  const classes = {
    pending: 'bg-gray-100 text-gray-800',
    assigned: 'bg-blue-100 text-blue-800',
    picked_up: 'bg-yellow-100 text-yellow-800',
    in_transit: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentMethodLabel = (method) => {
  const labels = {
    cash: 'Cash',
    bank_transfer: 'Bank Transfer',
    airtel_money: 'Airtel Money',
    tnm_mpamba: 'TNM Mpamba',
    other: 'Other'
  }
  return labels[method] || method
}

const fetchOrder = async () => {
  try {
    const response = await axios.get(`/api/legume/orders/${route.params.id}`)
    order.value = response.data.data
    selectedStatus.value = order.value.order_status

    if (order.value.delivery) {
      deliveryForm.value = { ...order.value.delivery }
    } else {
      deliveryForm.value.delivery_address = order.value.delivery_address || ''
    }
  } catch (error) {
    console.error('Error fetching order:', error)
  } finally {
    processing.value = false
  }
}

const updateOrderStatus = async () => {
  updatingStatus.value = true
  try {
    await axios.put(`/api/legume/orders/${order.value.id}/status`, { order_status: selectedStatus.value })
    fetchOrder()
  } catch (error) {
    alert(error.response?.data?.message || 'Error updating status')
  } finally {
    updatingStatus.value = false
  }
}

const savePayment = async () => {
  savingPayment.value = true
  try {
    await axios.post('/api/legume/payments', {
      ...paymentForm.value,
      legume_order_id: order.value.id
    })
    showPaymentModal.value = false
    paymentForm.value = {
      amount: '',
      payment_method: 'cash',
      transaction_id: '',
      phone_number: '',
      payment_date: new Date().toISOString().split('T')[0]
    }
    fetchOrder()
  } catch (error) {
    alert(error.response?.data?.message || 'Error saving payment')
  } finally {
    savingPayment.value = false
  }
}

const saveDelivery = async () => {
  savingDelivery.value = true
  try {
    if (order.value.delivery) {
      await axios.put(`/api/legume/deliveries/${order.value.delivery.id}`, deliveryForm.value)
    } else {
      await axios.post('/api/legume/deliveries', {
        ...deliveryForm.value,
        legume_order_id: order.value.id
      })
    }
    showDeliveryModal.value = false
    fetchOrder()
  } catch (error) {
    alert(error.response?.data?.message || 'Error saving delivery')
  } finally {
    savingDelivery.value = false
  }
}

onMounted(() => fetchOrder())
</script>
