<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Activity Reports') }}</h1>
      <p class="mt-1 text-sm text-gray-500">
        {{ __('Monitor employee activity and view inactivity explanations') }}
      </p>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <DocumentTextIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Reports Today') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.total_reports_today }}
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
              <ClockIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Pending Responses') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.pending_reports }}
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
                  {{ __('Acknowledged Today') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.acknowledged_today }}
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
              <BoltIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Power Outages Today') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.power_outages_today }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-2">
        <select
          v-model="filters.user_id"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadReports"
        >
          <option value="">{{ __('All Employees') }}</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>

        <select
          v-model="filters.status"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadReports"
        >
          <option value="">{{ __('All Statuses') }}</option>
          <option value="pending">{{ __('Pending') }}</option>
          <option value="acknowledged">{{ __('Acknowledged') }}</option>
        </select>

        <select
          v-model="filters.reason_type"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadReports"
        >
          <option value="">{{ __('All Types') }}</option>
          <option value="same_page">{{ __('Same Page') }}</option>
          <option value="computer_inactive">{{ __('Computer Inactive') }}</option>
          <option value="power_outage">{{ __('Power Outage') }}</option>
        </select>

        <input
          v-model="filters.date_from"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadReports"
        />

        <input
          v-model="filters.date_to"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadReports"
        />

        <button
          v-if="hasFilters"
          type="button"
          class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="clearFilters"
        >
          <XMarkIcon class="mr-1 h-4 w-4" />
          {{ __('Clear') }}
        </button>
      </div>

      <button
        type="button"
        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
        @click="loadReports"
      >
        <ArrowPathIcon class="mr-2 h-4 w-4" />
        {{ __('Refresh') }}
      </button>
    </div>

    <!-- Reports Table -->
    <div class="overflow-hidden border-b border-gray-200 bg-white shadow sm:rounded-lg">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              {{ __('Employee') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              {{ __('Type') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              {{ __('Duration') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              {{ __('Time Period') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              {{ __('Status') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
              {{ __('Explanation') }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="report in reports.data" :key="report.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <UserAvatar :user="report.user" size="10" />
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ report.user?.name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ report.user?.email }}
                  </div>
                </div>
              </div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span
                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                :class="getTypeClass(report.reason_type)"
              >
                {{ getTypeLabel(report.reason_type) }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
              {{ report.inactive_duration_minutes }} {{ __('minutes') }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              <div>{{ formatDateTime(report.inactive_from) }}</div>
              <div class="text-xs text-gray-400">{{ __('to') }} {{ formatDateTime(report.inactive_until) }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span
                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                :class="report.is_pending ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'"
              >
                {{ report.is_pending ? __('Pending') : __('Acknowledged') }}
              </span>
            </td>
            <td class="max-w-xs px-6 py-4 text-sm text-gray-500">
              <div v-if="report.user_explanation" class="truncate" :title="report.user_explanation">
                {{ report.user_explanation }}
              </div>
              <span v-else class="italic text-gray-400">{{ __('Awaiting response...') }}</span>
            </td>
          </tr>
          <tr v-if="!reports.data?.length">
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
              {{ __('No activity reports found') }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="reports.data?.length" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
          <button
            :disabled="!reports.prev_page_url"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            @click="goToPage(reports.current_page - 1)"
          >
            {{ __('Previous') }}
          </button>
          <button
            :disabled="!reports.next_page_url"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            @click="goToPage(reports.current_page + 1)"
          >
            {{ __('Next') }}
          </button>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              {{ __('Showing') }}
              <span class="font-medium">{{ reports.from }}</span>
              {{ __('to') }}
              <span class="font-medium">{{ reports.to }}</span>
              {{ __('of') }}
              <span class="font-medium">{{ reports.total }}</span>
              {{ __('results') }}
            </p>
          </div>
          <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
              <button
                :disabled="!reports.prev_page_url"
                class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                @click="goToPage(reports.current_page - 1)"
              >
                <ChevronLeftIcon class="h-5 w-5" />
              </button>
              <button
                :disabled="!reports.next_page_url"
                class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                @click="goToPage(reports.current_page + 1)"
              >
                <ChevronRightIcon class="h-5 w-5" />
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, inject, onMounted, reactive, ref } from 'vue'
import {
  ArrowPathIcon,
  BoltIcon,
  CheckCircleIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ClockIcon,
  DocumentTextIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'

const __ = inject('__')

const processing = ref(true)
const statistics = ref(null)
const users = ref([])
const reports = ref({ data: [] })

const filters = reactive({
  user_id: '',
  status: '',
  reason_type: '',
  date_from: '',
  date_to: '',
  page: 1,
})

const hasFilters = computed(() => {
  return filters.user_id || filters.status || filters.reason_type || filters.date_from || filters.date_to
})

const loadStatistics = async () => {
  try {
    const response = await axios.get('activity/statistics')
    statistics.value = response.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await axios.get('activity/users')
    users.value = response.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const loadReports = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.user_id) params.append('user_id', filters.user_id)
    if (filters.status) params.append('status', filters.status)
    if (filters.reason_type) params.append('reason_type', filters.reason_type)
    if (filters.date_from) params.append('date_from', filters.date_from)
    if (filters.date_to) params.append('date_to', filters.date_to)
    params.append('page', filters.page)

    const response = await axios.get(`activity/reports?${params.toString()}`)
    reports.value = response.data
  } catch (error) {
    console.error('Failed to load reports:', error)
  }
}

const clearFilters = () => {
  filters.user_id = ''
  filters.status = ''
  filters.reason_type = ''
  filters.date_from = ''
  filters.date_to = ''
  filters.page = 1
  loadReports()
}

const goToPage = (page) => {
  filters.page = page
  loadReports()
}

const formatDateTime = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  })
}

const getTypeClass = (type) => {
  switch (type) {
    case 'same_page':
      return 'bg-blue-100 text-blue-800'
    case 'computer_inactive':
      return 'bg-orange-100 text-orange-800'
    case 'power_outage':
      return 'bg-red-100 text-red-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const getTypeLabel = (type) => {
  switch (type) {
    case 'same_page':
      return __('Same Page')
    case 'computer_inactive':
      return __('Inactive')
    case 'power_outage':
      return __('Power Outage')
    default:
      return type
  }
}

onMounted(async () => {
  try {
    await Promise.all([loadStatistics(), loadUsers(), loadReports()])
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    processing.value = false
  }
})
</script>
