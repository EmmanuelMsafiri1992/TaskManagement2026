<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Attendance Reports') }}</h1>
      <p class="mt-1 text-sm text-gray-600">{{ __('View and manage employee attendance records') }}</p>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Present Today') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.present_today }}
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
              <ClockIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Avg Hours Today') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.avg_hours_today }}h
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
              <CalendarIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('This Month') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.total_this_month }}
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
              <ChartBarIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Avg Monthly Hours') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.avg_hours_month }}h
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center">
      <div class="flex flex-1 gap-2">
        <select
          v-model="filters.userId"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadAttendance"
        >
          <option value="">{{ __('All Employees') }}</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>

        <input
          v-model="filters.startDate"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadAttendance"
        />

        <input
          v-model="filters.endDate"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="loadAttendance"
        />

        <TheButton variant="secondary" @click="resetFilters">
          {{ __('Reset') }}
        </TheButton>
      </div>

      <div class="flex gap-2">
        <TheButton variant="secondary" @click="exportReport">
          <DocumentArrowDownIcon class="mr-2 h-4 w-4" />
          {{ __('Export') }}
        </TheButton>
        <TheButton v-if="can('attendance:create')" @click="openAttendanceModal()">
          {{ __('Add Record') }}
        </TheButton>
      </div>
    </div>

    <!-- Attendance Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      {{ __('Employee') }}
                    </th>
                    <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      {{ __('Date') }}
                    </th>
                    <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      {{ __('Clock In') }}
                    </th>
                    <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      {{ __('Clock Out') }}
                    </th>
                    <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      {{ __('Total Hours') }}
                    </th>
                    <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                      {{ __('Notes') }}
                    </th>
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="record in attendance" :key="record.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                          <UserAvatar :user="record.user" size="10" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ record.user.name }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(record.date).toLocaleDateString() }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ formatTime(record.clock_in) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ record.clock_out ? formatTime(record.clock_out) : '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ record.total_hours ? formatHours(record.total_hours) : '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                      {{ record.notes || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <PencilIcon
                        v-if="can('attendance:update')"
                        class="inline w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="openAttendanceModal(record)"
                      />
                      <TrashIcon
                        v-if="can('attendance:delete')"
                        class="ml-2 inline w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="deleteRecord(record.id)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

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
                      @click="changePage(pagination.current_page - 1)"
                    >
                      {{ __('Previous') }}
                    </TheButton>
                    <TheButton
                      variant="secondary"
                      size="sm"
                      :disabled="pagination.current_page === pagination.last_page"
                      @click="changePage(pagination.current_page + 1)"
                    >
                      {{ __('Next') }}
                    </TheButton>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { CalendarIcon, ChartBarIcon, ClockIcon, DocumentArrowDownIcon, PencilIcon, TrashIcon, UsersIcon } from '@heroicons/vue/24/outline'
import axios from 'axios'
import { useModalsStore } from 'spack'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Form from './Form.vue'
import { can } from '@/helpers'

const processing = ref(true)
const statistics = ref(null)
const attendance = ref([])
const users = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 50,
  total: 0
})

const filters = reactive({
  userId: '',
  startDate: '',
  endDate: '',
  page: 1
})

const formatTime = (timestamp) => {
  if (!timestamp) return '-'
  return new Date(timestamp).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatHours = (totalHours) => {
  if (!totalHours) return '0h 0m'
  const hours = Math.floor(totalHours / 60)
  const minutes = totalHours % 60
  return `${hours}h ${minutes}m`
}

const loadAttendance = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.userId) params.append('user_id', filters.userId)
    if (filters.startDate) params.append('start_date', filters.startDate)
    if (filters.endDate) params.append('end_date', filters.endDate)
    params.append('page', filters.page)
    params.append('per_page', pagination.value.per_page)

    const response = await axios.get(`/api/attendance?${params}`)
    attendance.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Failed to load attendance:', error)
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('/api/attendance/report')
    statistics.value = response.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/attendance/users')
    users.value = response.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const resetFilters = () => {
  filters.userId = ''
  filters.startDate = ''
  filters.endDate = ''
  filters.page = 1
  loadAttendance()
}

const changePage = (page) => {
  filters.page = page
  loadAttendance()
}

const openAttendanceModal = (record = null) => {
  useModalsStore().add(Form, { id: record?.id || null })
}

const deleteRecord = async (id) => {
  // eslint-disable-next-line no-alert
  if (!confirm('Are you sure you want to delete this attendance record?')) return

  try {
    await axios.delete(`/api/attendance/${id}`)
    loadAttendance()
  } catch (error) {
    console.error('Failed to delete record:', error)
  }
}

const exportReport = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.userId) params.append('user_id', filters.userId)
    if (filters.startDate) params.append('start_date', filters.startDate)
    if (filters.endDate) params.append('end_date', filters.endDate)

    const response = await axios.get(`/api/attendance/export?${params}`, {
      responseType: 'blob'
    })

    // Create a download link
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `attendance_report_${new Date().toISOString().slice(0, 10)}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Failed to export:', error)
  }
}

onMounted(async () => {
  await Promise.all([
    loadAttendance(),
    loadStatistics(),
    loadUsers()
  ])
  processing.value = false
})
</script>
