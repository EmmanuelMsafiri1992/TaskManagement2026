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
              <ClockIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Total Custom Schedules') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.total_custom }}
                </dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Currently Active') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.active }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CalendarIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Permanent Schedules') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.permanent }}
                </dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Expiring Soon') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.expiring_soon }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 gap-2">
        <div class="relative w-64 rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 flex items-center ltr:left-0 ltr:pl-3 rtl:right-0 rtl:pr-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="index.params.search"
            type="search"
            :placeholder="__('Search by name or email...')"
            class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="index.params.status"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Statuses') }}</option>
          <option value="current">{{ __('Currently Active') }}</option>
          <option value="active">{{ __('Active (Including Future)') }}</option>
          <option value="expired">{{ __('Expired') }}</option>
        </select>

        <select
          v-model="index.params.user_id"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Users') }}</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton size="sm" @click="openFormModal()">
          {{ __('Assign Working Hours') }}
        </TheButton>
      </div>
    </div>

    <!-- Working Hours Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh name="working-hours" :index="index" :label="__('Employee')" />
                    <TableTh name="working-hours" :index="index" :label="__('Schedule')" />
                    <TableTh name="working-hours" :index="index" :label="__('Type')" />
                    <TableTh name="working-hours" :index="index" :label="__('Effective Period')" />
                    <TableTh name="working-hours" :index="index" :label="__('Status')" />
                    <TableTh name="working-hours" :index="index" :label="__('Assigned By')" />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="record in index.data.data" :key="record.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                          <UserAvatar :user="record.user" size="10" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ record.user?.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ record.user?.email }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ formatTime(record.start_time) }} - {{ formatTime(record.end_time) }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ calculateDuration(record.start_time, record.end_time) }} hours
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-blue-100 text-blue-800': !record.effective_from && !record.effective_until,
                          'bg-purple-100 text-purple-800': record.effective_from || record.effective_until
                        }"
                      >
                        {{ (!record.effective_from && !record.effective_until) ? 'Permanent' : 'Temporary' }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <template v-if="!record.effective_from && !record.effective_until">
                        {{ __('Permanent') }}
                      </template>
                      <template v-else>
                        {{ formatDate(record.effective_from) }} - {{ formatDate(record.effective_until) || 'Ongoing' }}
                      </template>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="getStatusClass(record)"
                      >
                        {{ getStatusLabel(record) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ record.assigned_by_user?.name || '-' }}
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <PencilIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="openFormModal(record)"
                      />
                      <TrashIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-red-600"
                        @click="index.deleteIt(record.id)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexNoData v-if="!index.data.data?.length" />
              <IndexPagination :index="index" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Form Modal -->
    <FormModal v-if="form.show" size="lg" @saved="handleSaved">
      <Form :model-value="form.model" :users="users" @close="form.show = false" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { CalendarIcon, CheckCircleIcon, ClockIcon, ExclamationTriangleIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexNoData from '@/thetheme/components/IndexNoData.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Form from './Form.vue'
import { useIndex } from '@/composables/useIndex'

const processing = ref(true)
const statistics = ref(null)
const users = ref([])

const index = useIndex('working-hours', {
  search: '',
  status: '',
  user_id: '',
  per_page: 15
})

const form = reactive({
  show: false,
  model: null
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openFormModal = (record = null) => {
  form.model = record
  form.show = true
}

const handleSaved = () => {
  index.get()
  loadStatistics()
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('working-hours/statistics')
    statistics.value = response.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await axios.get('working-hours/users')
    users.value = response.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const formatTime = (time) => {
  if (!time) return '-'
  const [hours, minutes] = time.split(':')
  const hour = parseInt(hours)
  const ampm = hour >= 12 ? 'PM' : 'AM'
  const displayHour = hour % 12 || 12
  return `${displayHour}:${minutes} ${ampm}`
}

const formatDate = (date) => {
  if (!date) return null
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const calculateDuration = (startTime, endTime) => {
  if (!startTime || !endTime) return 0
  const [startH, startM] = startTime.split(':').map(Number)
  const [endH, endM] = endTime.split(':').map(Number)
  const startMinutes = startH * 60 + startM
  const endMinutes = endH * 60 + endM
  return ((endMinutes - startMinutes) / 60).toFixed(1)
}

const getStatusClass = (record) => {
  if (!record.is_active) {
    return 'bg-gray-100 text-gray-800'
  }

  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const effectiveFrom = record.effective_from ? new Date(record.effective_from) : null
  const effectiveUntil = record.effective_until ? new Date(record.effective_until) : null

  // Check if expired
  if (effectiveUntil && effectiveUntil < today) {
    return 'bg-red-100 text-red-800'
  }

  // Check if future
  if (effectiveFrom && effectiveFrom > today) {
    return 'bg-yellow-100 text-yellow-800'
  }

  // Currently active
  return 'bg-green-100 text-green-800'
}

const getStatusLabel = (record) => {
  if (!record.is_active) {
    return 'Inactive'
  }

  const today = new Date()
  today.setHours(0, 0, 0, 0)

  const effectiveFrom = record.effective_from ? new Date(record.effective_from) : null
  const effectiveUntil = record.effective_until ? new Date(record.effective_until) : null

  if (effectiveUntil && effectiveUntil < today) {
    return 'Expired'
  }

  if (effectiveFrom && effectiveFrom > today) {
    return 'Scheduled'
  }

  return 'Active'
}

onMounted(async () => {
  try {
    await Promise.all([
      index.get(),
      loadStatistics(),
      loadUsers()
    ])
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    processing.value = false
  }
})
</script>
