<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <TruckIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Deliveries</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.total_deliveries }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Pending</dt>
                <dd class="text-lg font-semibold text-yellow-600">{{ statistics.pending }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowPathIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">In Transit</dt>
                <dd class="text-lg font-semibold text-purple-600">{{ statistics.in_transit }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">Delivered</dt>
                <dd class="text-lg font-semibold text-green-600">{{ statistics.delivered }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <XCircleIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Failed</dt>
                <dd class="text-lg font-semibold text-red-600">{{ statistics.failed }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
      <div class="relative w-64 rounded-md shadow-sm">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
          <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="searchQuery"
          type="search"
          placeholder="Search deliveries..."
          class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @input="handleSearch"
        />
      </div>

      <select
        v-model="filterStatus"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchDeliveries"
      >
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="assigned">Assigned</option>
        <option value="picked_up">Picked Up</option>
        <option value="in_transit">In Transit</option>
        <option value="delivered">Delivered</option>
        <option value="failed">Failed</option>
      </select>
    </div>

    <!-- Deliveries Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Order</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Customer</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Address</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Driver</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">ETA</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="delivery in deliveries" :key="delivery.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <router-link :to="`/legume/orders/${delivery.legume_order_id}`" class="font-medium text-indigo-600 hover:text-indigo-900">
                {{ delivery.order?.order_number }}
              </router-link>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-900">{{ delivery.order?.customer?.name }}</div>
              <div class="text-sm text-gray-500">{{ delivery.order?.customer?.phone }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="max-w-xs truncate text-sm text-gray-500">{{ delivery.delivery_address }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div v-if="delivery.driver_name" class="text-sm text-gray-900">{{ delivery.driver_name }}</div>
              <div v-if="delivery.driver_phone" class="text-sm text-gray-500">{{ delivery.driver_phone }}</div>
              <span v-if="!delivery.driver_name" class="text-sm text-gray-400">Not assigned</span>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="getStatusClass(delivery.status)" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">
                {{ delivery.status.replace('_', ' ') }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              <span v-if="delivery.actual_delivery" class="text-green-600">
                Delivered {{ formatDateTime(delivery.actual_delivery) }}
              </span>
              <span v-else-if="delivery.estimated_delivery">
                {{ formatDateTime(delivery.estimated_delivery) }}
              </span>
              <span v-else class="text-gray-400">Not set</span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <button @click="openUpdateModal(delivery)" class="text-indigo-600 hover:text-indigo-900">Update</button>
            </td>
          </tr>
          <tr v-if="deliveries.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No deliveries found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Update Status Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">Update Delivery</h3>

          <form @submit.prevent="updateDelivery">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Status *</label>
                <select v-model="updateForm.status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                <input v-model="updateForm.driver_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Driver Phone</label>
                <input v-model="updateForm.driver_phone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Estimated Delivery</label>
                <input v-model="updateForm.estimated_delivery" type="datetime-local" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea v-model="updateForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button type="button" @click="closeModal" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="updating" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                {{ updating ? 'Updating...' : 'Update' }}
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
  TruckIcon,
  ClockIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const deliveries = ref([])
const statistics = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')
const showModal = ref(false)
const selectedDelivery = ref(null)
const updating = ref(false)

const updateForm = ref({
  status: '',
  driver_name: '',
  driver_phone: '',
  estimated_delivery: '',
  notes: ''
})

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString('en-MW', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getStatusClass = (status) => {
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

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchDeliveries(), 300)
}

const fetchDeliveries = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (filterStatus.value) params.status = filterStatus.value

    const [deliveriesRes, statsRes] = await Promise.all([
      axios.get('/api/legume/deliveries', { params }),
      axios.get('/api/legume/deliveries/statistics')
    ])

    deliveries.value = deliveriesRes.data.data
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching deliveries:', error)
  } finally {
    processing.value = false
  }
}

const openUpdateModal = (delivery) => {
  selectedDelivery.value = delivery
  updateForm.value = {
    status: delivery.status,
    driver_name: delivery.driver_name || '',
    driver_phone: delivery.driver_phone || '',
    estimated_delivery: delivery.estimated_delivery ? delivery.estimated_delivery.slice(0, 16) : '',
    notes: delivery.notes || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedDelivery.value = null
}

const updateDelivery = async () => {
  updating.value = true
  try {
    await axios.put(`/api/legume/deliveries/${selectedDelivery.value.id}`, updateForm.value)
    closeModal()
    fetchDeliveries()
  } catch (error) {
    alert(error.response?.data?.message || 'Error updating delivery')
  } finally {
    updating.value = false
  }
}

onMounted(() => fetchDeliveries())
</script>
