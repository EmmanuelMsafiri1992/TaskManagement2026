<template>
  <Dropdown :close-outside="true">
    <template #trigger>
      <button
        class="relative bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none"
      >
        <BoltIcon class="h-6 w-6" :class="{ 'text-green-500': isRunning }" />
        <div
          v-if="isLowFuel"
          class="absolute -top-0.5 z-40 flex h-[1.125rem] w-[1.125rem] items-center justify-center rounded-full bg-red-600 text-[0.65rem] text-white ltr:-right-0.5 rtl:-left-0.5"
        >
          !
        </div>
      </button>
    </template>

    <template #content>
      <div
        class="absolute z-50 mt-2 w-80 origin-top-right overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 rtl:left-0"
        role="menu"
      >
        <!-- Header -->
        <div class="border-b border-gray-200 bg-gradient-to-r from-orange-500 to-orange-600 px-4 py-3">
          <div class="flex items-center justify-between">
            <span class="font-semibold text-white">Generator</span>
            <span
              :class="[
                'rounded-full px-2 py-0.5 text-xs font-medium',
                isRunning ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
              ]"
            >
              {{ isRunning ? 'Running' : 'Stopped' }}
            </span>
          </div>
        </div>

        <div class="p-4">
          <!-- Fuel Gauge -->
          <div class="mb-4">
            <div class="mb-1 flex items-center justify-between text-sm">
              <span class="text-gray-600">Fuel Level</span>
              <span class="font-semibold" :class="fuelPercentage < 20 ? 'text-red-600' : 'text-gray-900'">
                {{ currentLevel.toFixed(1) }} / {{ tankCapacity }} L
              </span>
            </div>
            <div class="h-4 w-full overflow-hidden rounded-full bg-gray-200">
              <div
                class="h-full rounded-full transition-all duration-500"
                :class="fuelBarColor"
                :style="{ width: fuelPercentage + '%' }"
              ></div>
            </div>
            <div class="mt-1 text-right text-xs text-gray-500">{{ fuelPercentage }}%</div>
          </div>

          <!-- Low Fuel Warning -->
          <div v-if="isLowFuel" class="mb-4 rounded-md bg-red-50 p-2">
            <p class="flex items-center text-xs text-red-700">
              <ExclamationTriangleIcon class="mr-1 h-4 w-4" />
              Low fuel! Please refuel soon.
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="mb-4 flex gap-2">
            <button
              v-if="!isRunning"
              :disabled="currentLevel <= 0 || loading"
              class="flex flex-1 items-center justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
              @click.stop="startGenerator"
            >
              <PlayIcon class="mr-1 h-4 w-4" />
              Start
            </button>
            <button
              v-else
              :disabled="loading"
              class="flex flex-1 items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
              @click.stop="stopGenerator"
            >
              <StopIcon class="mr-1 h-4 w-4" />
              Stop
            </button>
            <button
              class="flex flex-1 items-center justify-center rounded-md bg-orange-600 px-3 py-2 text-sm font-medium text-white hover:bg-orange-700"
              @click.stop="showAddFuelForm = true"
            >
              <PlusIcon class="mr-1 h-4 w-4" />
              Add Fuel
            </button>
          </div>

          <!-- Add Fuel Form -->
          <div v-if="showAddFuelForm" class="mb-4 rounded-md border border-gray-200 bg-gray-50 p-3">
            <div class="mb-2">
              <label class="mb-1 block text-xs font-medium text-gray-700">Amount (Liters)</label>
              <input
                v-model="fuelAmount"
                type="number"
                step="0.5"
                min="0.5"
                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                placeholder="Enter amount"
                @click.stop
              />
            </div>
            <div class="mb-2">
              <label class="mb-1 block text-xs font-medium text-gray-700">Notes (optional)</label>
              <input
                v-model="fuelNotes"
                type="text"
                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-orange-500 focus:ring-orange-500"
                placeholder="e.g., Purchased from station"
                @click.stop
              />
            </div>
            <div class="flex gap-2">
              <button
                class="flex-1 rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300"
                @click.stop="showAddFuelForm = false"
              >
                Cancel
              </button>
              <button
                :disabled="!fuelAmount || loading"
                class="flex-1 rounded-md bg-orange-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-orange-700 disabled:opacity-50"
                @click.stop="addFuel"
              >
                Add
              </button>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="border-t border-gray-200 pt-3">
            <h4 class="mb-2 text-xs font-semibold uppercase text-gray-500">Recent Activity</h4>
            <div v-if="logs.length" class="max-h-32 space-y-1 overflow-y-auto">
              <div v-for="log in logs" :key="log.id" class="flex items-center justify-between text-xs">
                <span class="text-gray-600">
                  <span
                    :class="[
                      'mr-1 inline-block rounded px-1 py-0.5 text-xs font-medium',
                      log.action === 'refuel' ? 'bg-green-100 text-green-700' :
                      log.action === 'start' ? 'bg-blue-100 text-blue-700' :
                      'bg-red-100 text-red-700'
                    ]"
                  >{{ log.action }}</span>
                  {{ log.user?.name || 'System' }}
                </span>
                <span class="text-gray-400">{{ formatTime(log.created_at) }}</span>
              </div>
            </div>
            <p v-else class="text-xs text-gray-400">No recent activity</p>
          </div>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Dropdown } from 'thetheme'
import { axios } from 'spack'
import { BoltIcon, PlayIcon, StopIcon, PlusIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const currentLevel = ref(0)
const tankCapacity = ref(12)
const reserveFuel = ref(2)
const isRunning = ref(false)
const logs = ref<any[]>([])
const loading = ref(false)
const showAddFuelForm = ref(false)
const fuelAmount = ref('')
const fuelNotes = ref('')

const fuelPercentage = computed(() => {
  if (tankCapacity.value <= 0) return 0
  return Math.round((currentLevel.value / tankCapacity.value) * 100)
})

const isLowFuel = computed(() => currentLevel.value <= reserveFuel.value)

const fuelBarColor = computed(() => {
  if (fuelPercentage.value < 20) return 'bg-red-500'
  if (fuelPercentage.value < 50) return 'bg-yellow-500'
  return 'bg-green-500'
})

const formatTime = (datetime: string) => {
  if (!datetime) return ''
  const date = new Date(datetime)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / (1000 * 60))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  return date.toLocaleDateString()
}

const loadData = () => {
  axios.get('generator/status').then((res: any) => {
    if (res.data.generator) {
      currentLevel.value = Number(res.data.generator.current_level) || 0
      tankCapacity.value = Number(res.data.generator.tank_capacity) || 12
      reserveFuel.value = Number(res.data.generator.reserve_fuel) || 2
      isRunning.value = Boolean(res.data.generator.is_running)
    }
  }).catch(() => {})

  axios.get('generator/logs', { params: { per_page: 5 } }).then((res: any) => {
    logs.value = res.data.data || []
  }).catch(() => {})
}

const startGenerator = () => {
  loading.value = true
  axios.post('generator/start').then(() => {
    loadData()
  }).catch((err: any) => {
    alert(err.response?.data?.message || 'Failed to start generator')
  }).finally(() => {
    loading.value = false
  })
}

const stopGenerator = () => {
  loading.value = true
  axios.post('generator/stop').then(() => {
    loadData()
  }).catch((err: any) => {
    alert(err.response?.data?.message || 'Failed to stop generator')
  }).finally(() => {
    loading.value = false
  })
}

const addFuel = () => {
  if (!fuelAmount.value) return
  loading.value = true
  axios.post('generator/add-fuel', {
    amount: parseFloat(fuelAmount.value),
    notes: fuelNotes.value,
  }).then(() => {
    fuelAmount.value = ''
    fuelNotes.value = ''
    showAddFuelForm.value = false
    loadData()
  }).catch((err: any) => {
    alert(err.response?.data?.message || 'Failed to add fuel')
  }).finally(() => {
    loading.value = false
  })
}

// Load data on component init
loadData()
</script>
