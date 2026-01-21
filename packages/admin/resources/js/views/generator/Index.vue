<template>
  <div v-if="loading" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Generator Fuel Management') }}</h1>
      <p class="mt-1 text-sm text-gray-600">{{ __('Monitor and manage generator fuel levels') }}</p>
    </div>

    <!-- Generator Status Card -->
    <div class="overflow-hidden rounded-lg bg-white shadow-lg">
      <div class="border-b border-gray-200 bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-white">{{ __('Generator Status') }}</h3>
          <span
            :class="[
              'rounded-full px-3 py-1 text-sm font-medium',
              generator?.is_running
                ? 'bg-green-100 text-green-800'
                : 'bg-gray-100 text-gray-800'
            ]"
          >
            {{ generator?.is_running ? __('Running') : __('Stopped') }}
          </span>
        </div>
      </div>

      <div class="p-6">
        <!-- Fuel Gauge -->
        <div class="mb-6">
          <div class="mb-2 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">{{ __('Fuel Level') }}</span>
            <span class="text-sm font-bold" :class="fuelPercentage < 20 ? 'text-red-600' : 'text-gray-900'">
              {{ generator?.current_level?.toFixed(2) || '0.00' }} / {{ generator?.tank_capacity || '12' }} L
              ({{ fuelPercentage }}%)
            </span>
          </div>
          <div class="h-6 w-full overflow-hidden rounded-full bg-gray-200">
            <div
              class="h-full rounded-full transition-all duration-500"
              :class="[
                fuelPercentage < 20 ? 'bg-red-500' :
                fuelPercentage < 50 ? 'bg-yellow-500' : 'bg-green-500'
              ]"
              :style="{ width: fuelPercentage + '%' }"
            ></div>
          </div>
          <p v-if="isLowFuel" class="mt-2 text-sm text-red-600">
            <ExclamationTriangleIcon class="mr-1 inline h-4 w-4" />
            {{ __('Low fuel warning! Please refuel soon.') }}
          </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3">
          <TheButton
            v-if="!generator?.is_running"
            :disabled="generator?.current_level <= 0 || actionLoading"
            @click="startGenerator"
          >
            <PlayIcon class="mr-2 h-5 w-5" />
            {{ __('Start Generator') }}
          </TheButton>
          <TheButton
            v-else
            color="red"
            :disabled="actionLoading"
            @click="stopGenerator"
          >
            <StopIcon class="mr-2 h-5 w-5" />
            {{ __('Stop Generator') }}
          </TheButton>
          <TheButton variant="secondary" @click="showAddFuelModal = true">
            <PlusIcon class="mr-2 h-5 w-5" />
            {{ __('Add Fuel') }}
          </TheButton>
          <TheButton variant="secondary" @click="showSettingsModal = true">
            <Cog6ToothIcon class="mr-2 h-5 w-5" />
            {{ __('Settings') }}
          </TheButton>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-orange-100 p-3">
              <FireIcon class="h-6 w-6 text-orange-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Current Level') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics?.current_level?.toFixed(2) || '0.00' }} L</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-green-100 p-3">
              <ArrowUpIcon class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Monthly Refuels') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics?.monthly_refuels?.toFixed(2) || '0.00' }} L</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-red-100 p-3">
              <ArrowDownIcon class="h-6 w-6 text-red-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Monthly Usage') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics?.monthly_consumption?.toFixed(2) || '0.00' }} L</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 rounded-md bg-blue-100 p-3">
              <ChartBarIcon class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Usage') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics?.total_consumption?.toFixed(2) || '0.00' }} L</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Fuel Logs Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 px-6 py-4">
        <h3 class="text-lg font-semibold text-gray-900">{{ __('Fuel Activity Log') }}</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Date/Time') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('User') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Action') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Amount') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Level Change') }}</th>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Notes') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                {{ formatDateTime(log.created_at) }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                {{ log.user?.name || 'System' }}
              </td>
              <td class="whitespace-nowrap px-6 py-4">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                    log.action === 'refuel' ? 'bg-green-100 text-green-800' :
                    log.action === 'start' ? 'bg-blue-100 text-blue-800' :
                    log.action === 'stop' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ log.action }}
                </span>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                {{ log.amount > 0 ? log.amount.toFixed(2) + ' L' : '-' }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                {{ log.level_before.toFixed(2) }} → {{ log.level_after.toFixed(2) }} L
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                <div class="max-w-xs truncate">{{ log.notes || '-' }}</div>
              </td>
            </tr>
            <tr v-if="logs.length === 0">
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                {{ __('No fuel logs found') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            {{ __('Page') }} {{ pagination.current_page }} {{ __('of') }} {{ pagination.last_page }}
          </div>
          <div class="flex gap-2">
            <TheButton
              variant="secondary"
              size="sm"
              :disabled="pagination.current_page === 1"
              @click="loadLogs(pagination.current_page - 1)"
            >
              {{ __('Previous') }}
            </TheButton>
            <TheButton
              variant="secondary"
              size="sm"
              :disabled="pagination.current_page === pagination.last_page"
              @click="loadLogs(pagination.current_page + 1)"
            >
              {{ __('Next') }}
            </TheButton>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Fuel Modal -->
    <TransitionRoot appear :show="showAddFuelModal" as="template">
      <Dialog as="div" class="relative z-50" @close="showAddFuelModal = false">
        <TransitionChild
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 shadow-xl transition-all">
                <DialogTitle class="text-lg font-medium text-gray-900">{{ __('Add Fuel') }}</DialogTitle>
                <div class="mt-4 space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Amount (Liters)') }}</label>
                    <input
                      v-model="addFuelForm.amount"
                      type="number"
                      step="0.1"
                      min="0.1"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                      :placeholder="__('Enter fuel amount')"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                    <textarea
                      v-model="addFuelForm.notes"
                      rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                      :placeholder="__('Optional notes')"
                    ></textarea>
                  </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                  <TheButton variant="secondary" @click="showAddFuelModal = false">{{ __('Cancel') }}</TheButton>
                  <TheButton :disabled="!addFuelForm.amount || actionLoading" @click="addFuel">
                    {{ __('Add Fuel') }}
                  </TheButton>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Settings Modal -->
    <TransitionRoot appear :show="showSettingsModal" as="template">
      <Dialog as="div" class="relative z-50" @close="showSettingsModal = false">
        <TransitionChild
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-lg bg-white p-6 shadow-xl transition-all">
                <DialogTitle class="text-lg font-medium text-gray-900">{{ __('Generator Settings') }}</DialogTitle>
                <div class="mt-4 space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Tank Capacity (Liters)') }}</label>
                    <input
                      v-model="settingsForm.tank_capacity"
                      type="number"
                      step="0.5"
                      min="1"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Reserve Fuel Level (Liters)') }}</label>
                    <input
                      v-model="settingsForm.reserve_fuel"
                      type="number"
                      step="0.1"
                      min="0"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">{{ __('Low fuel warning will show when level drops below this') }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('Consumption Rate (Liters/Hour)') }}</label>
                    <input
                      v-model="settingsForm.consumption_rate"
                      type="number"
                      step="0.1"
                      min="0.1"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                    />
                  </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                  <TheButton variant="secondary" @click="showSettingsModal = false">{{ __('Cancel') }}</TheButton>
                  <TheButton :disabled="actionLoading" @click="saveSettings">
                    {{ __('Save Settings') }}
                  </TheButton>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import {
  ArrowDownIcon,
  ArrowUpIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  ExclamationTriangleIcon,
  FireIcon,
  PlayIcon,
  PlusIcon,
  StopIcon,
} from '@heroicons/vue/24/outline'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'

const __ = (key) => key // Translation helper

const loading = ref(true)
const actionLoading = ref(false)
const generator = ref(null)
const statistics = ref(null)
const logs = ref([])
const pagination = ref({ current_page: 1, last_page: 1 })

const showAddFuelModal = ref(false)
const showSettingsModal = ref(false)

const addFuelForm = reactive({
  amount: '',
  notes: '',
})

const settingsForm = reactive({
  tank_capacity: 12,
  reserve_fuel: 0.5,
  consumption_rate: 1,
})

let refreshInterval = null

const fuelPercentage = computed(() => {
  if (!generator.value || generator.value.tank_capacity <= 0) return 0
  return Math.round((generator.value.current_level / generator.value.tank_capacity) * 100)
})

const isLowFuel = computed(() => {
  if (!generator.value) return false
  return generator.value.current_level <= generator.value.reserve_fuel
})

const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleString()
}

const loadStatus = async () => {
  try {
    const response = await axios.get('/api/generator/status')
    generator.value = response.data.generator
    settingsForm.tank_capacity = generator.value.tank_capacity
    settingsForm.reserve_fuel = generator.value.reserve_fuel
    settingsForm.consumption_rate = generator.value.consumption_rate
  } catch (error) {
    console.error('Failed to load generator status:', error)
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('/api/generator/statistics')
    statistics.value = response.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadLogs = async (page = 1) => {
  try {
    const response = await axios.get('/api/generator/logs', { params: { page, per_page: 10 } })
    logs.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
    }
  } catch (error) {
    console.error('Failed to load logs:', error)
  }
}

const startGenerator = async () => {
  actionLoading.value = true
  try {
    await axios.post('/api/generator/start')
    await loadStatus()
    await loadLogs()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to start generator')
  } finally {
    actionLoading.value = false
  }
}

const stopGenerator = async () => {
  actionLoading.value = true
  try {
    await axios.post('/api/generator/stop')
    await loadStatus()
    await loadStatistics()
    await loadLogs()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to stop generator')
  } finally {
    actionLoading.value = false
  }
}

const addFuel = async () => {
  actionLoading.value = true
  try {
    await axios.post('/api/generator/add-fuel', {
      amount: parseFloat(addFuelForm.amount),
      notes: addFuelForm.notes,
    })
    showAddFuelModal.value = false
    addFuelForm.amount = ''
    addFuelForm.notes = ''
    await loadStatus()
    await loadStatistics()
    await loadLogs()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to add fuel')
  } finally {
    actionLoading.value = false
  }
}

const saveSettings = async () => {
  actionLoading.value = true
  try {
    await axios.put('/api/generator/settings', settingsForm)
    showSettingsModal.value = false
    await loadStatus()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save settings')
  } finally {
    actionLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadStatus(), loadStatistics(), loadLogs()])
  loading.value = false

  // Auto-refresh status every 30 seconds if generator is running
  refreshInterval = setInterval(() => {
    if (generator.value?.is_running) {
      loadStatus()
    }
  }, 30000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>
