<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Stock Alerts</h1>
        <p class="text-sm text-gray-500">Monitor low and out-of-stock products</p>
      </div>
      <button
        v-if="alerts.filter(a => !a.is_acknowledged).length > 0"
        @click="acknowledgeAll"
        :disabled="acknowledging"
        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50"
      >
        Acknowledge All
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-3">
      <div class="overflow-hidden rounded-lg bg-red-50 shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ExclamationCircleIcon class="h-8 w-8 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-red-800">Out of Stock</dt>
                <dd class="text-2xl font-bold text-red-900">{{ outOfStockCount }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-yellow-50 shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-8 w-8 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-yellow-800">Low Stock</dt>
                <dd class="text-2xl font-bold text-yellow-900">{{ lowStockCount }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-green-50 shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CheckCircleIcon class="h-8 w-8 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-green-800">Acknowledged</dt>
                <dd class="text-2xl font-bold text-green-900">{{ acknowledgedCount }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex gap-2">
      <select
        v-model="filterType"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchAlerts"
      >
        <option value="">All Types</option>
        <option value="out_of_stock">Out of Stock</option>
        <option value="low_stock">Low Stock</option>
      </select>

      <select
        v-model="filterAcknowledged"
        class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchAlerts"
      >
        <option value="">All Status</option>
        <option value="0">Unacknowledged</option>
        <option value="1">Acknowledged</option>
      </select>
    </div>

    <!-- Alerts List -->
    <div class="space-y-4">
      <div
        v-for="alert in alerts"
        :key="alert.id"
        :class="[
          'rounded-lg shadow',
          alert.alert_type === 'out_of_stock' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200'
        ]"
      >
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <component
                :is="alert.alert_type === 'out_of_stock' ? ExclamationCircleIcon : ExclamationTriangleIcon"
                :class="[
                  'h-6 w-6',
                  alert.alert_type === 'out_of_stock' ? 'text-red-400' : 'text-yellow-400'
                ]"
              />
              <div class="ml-3">
                <h3 :class="[
                  'text-sm font-medium',
                  alert.alert_type === 'out_of_stock' ? 'text-red-800' : 'text-yellow-800'
                ]">
                  {{ alert.product?.name }}
                </h3>
                <p :class="[
                  'mt-1 text-sm',
                  alert.alert_type === 'out_of_stock' ? 'text-red-700' : 'text-yellow-700'
                ]">
                  {{ alert.alert_type === 'out_of_stock' ? 'Out of Stock' : 'Low Stock' }}
                  - Current: {{ formatNumber(alert.current_quantity) }} kg
                  (Threshold: {{ formatNumber(alert.threshold_quantity) }} kg)
                </p>
                <p v-if="alert.is_acknowledged" class="mt-1 text-xs text-gray-500">
                  Acknowledged by {{ alert.acknowledgedBy?.name }} on {{ formatDateTime(alert.acknowledged_at) }}
                </p>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <router-link
                :to="`/legume/purchases/create?product=${alert.legume_product_id}`"
                :class="[
                  'inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-white',
                  alert.alert_type === 'out_of_stock' ? 'bg-red-600 hover:bg-red-700' : 'bg-yellow-600 hover:bg-yellow-700'
                ]"
              >
                Restock Now
              </router-link>
              <button
                v-if="!alert.is_acknowledged"
                @click="acknowledge(alert)"
                class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
              >
                Acknowledge
              </button>
              <span v-else class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                <CheckIcon class="mr-1 h-3 w-3" />
                Acknowledged
              </span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="alerts.length === 0" class="rounded-lg bg-white py-12 text-center shadow">
        <CheckCircleIcon class="mx-auto h-12 w-12 text-green-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No alerts</h3>
        <p class="mt-1 text-sm text-gray-500">All products have sufficient stock levels.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'
import {
  ExclamationCircleIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  CheckIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const alerts = ref([])
const filterType = ref('')
const filterAcknowledged = ref('')
const acknowledging = ref(false)

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

const outOfStockCount = computed(() => alerts.value.filter(a => a.alert_type === 'out_of_stock' && !a.is_acknowledged).length)
const lowStockCount = computed(() => alerts.value.filter(a => a.alert_type === 'low_stock' && !a.is_acknowledged).length)
const acknowledgedCount = computed(() => alerts.value.filter(a => a.is_acknowledged).length)

const fetchAlerts = async () => {
  try {
    const params = {}
    if (filterType.value) params.alert_type = filterType.value
    if (filterAcknowledged.value !== '') params.is_acknowledged = filterAcknowledged.value

    const response = await axios.get('/api/legume/alerts', { params })
    alerts.value = response.data.data
  } catch (error) {
    console.error('Error fetching alerts:', error)
  } finally {
    processing.value = false
  }
}

const acknowledge = async (alert) => {
  try {
    await axios.post(`/api/legume/alerts/${alert.id}/acknowledge`)
    fetchAlerts()
  } catch (error) {
    alert(error.response?.data?.message || 'Error acknowledging alert')
  }
}

const acknowledgeAll = async () => {
  acknowledging.value = true
  try {
    await axios.post('/api/legume/alerts/acknowledge-all')
    fetchAlerts()
  } catch (error) {
    alert(error.response?.data?.message || 'Error acknowledging alerts')
  } finally {
    acknowledging.value = false
  }
}

onMounted(() => fetchAlerts())
</script>
