<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Clock In/Out Section -->
    <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-md">
      <div class="border-b border-gray-200 bg-gradient-to-r from-indigo-500 to-indigo-600 px-6 py-4">
        <h3 class="text-xl font-semibold text-white">
          {{ __('Today\'s Attendance') }}
        </h3>
      </div>

      <div class="p-6">
        <div v-if="todayAttendance" class="grid grid-cols-1 gap-4 md:grid-cols-4">
          <!-- Clock In Card -->
          <div class="rounded-lg border border-green-200 bg-green-50 p-4 shadow-sm">
            <div class="mb-2 flex items-center">
              <svg class="mr-2 h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-sm font-medium text-green-800">{{ __('Clock In') }}</span>
            </div>
            <p class="text-2xl font-bold text-green-900">
              {{ formatTime(todayAttendance.clock_in) }}
            </p>
          </div>

          <!-- Clock Out Card -->
          <div class="rounded-lg border border-red-200 bg-red-50 p-4 shadow-sm">
            <div class="mb-2 flex items-center">
              <svg class="mr-2 h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-sm font-medium text-red-800">{{ __('Clock Out') }}</span>
            </div>
            <p class="text-2xl font-bold text-red-900">
              {{ todayAttendance.clock_out ? formatTime(todayAttendance.clock_out) : '-' }}
            </p>
          </div>

          <!-- Total Hours Card -->
          <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <div class="mb-2 flex items-center">
              <svg class="mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span class="text-sm font-medium text-blue-800">{{ __('Total Hours') }}</span>
            </div>
            <p class="text-2xl font-bold text-blue-900">
              {{ todayAttendance.total_hours ? formatHours(todayAttendance.total_hours) : '-' }}
            </p>
          </div>

          <!-- Action Button Card -->
          <div class="flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 p-4 shadow-sm">
            <TheButton
              v-if="!isClockedIn"
              size="lg"
              :disabled="clockingIn"
              @click="clockIn"
              class="w-full"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ clockingIn ? __('Clocking In...') : __('Clock In') }}
            </TheButton>
            <TheButton
              v-else
              size="lg"
              color="red"
              :disabled="clockingOut"
              @click="clockOut"
              class="w-full"
            >
              <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              {{ clockingOut ? __('Clocking Out...') : __('Clock Out') }}
            </TheButton>
          </div>
        </div>

        <!-- Not Clocked In Message -->
        <div v-else class="flex items-center justify-between">
          <div class="flex items-center text-gray-500">
            <svg class="mr-3 h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <p class="text-lg font-medium text-gray-700">{{ __('You have not clocked in today') }}</p>
              <p class="text-sm text-gray-500">{{ __('Click the button to start your work day') }}</p>
            </div>
          </div>
          <TheButton
            size="lg"
            :disabled="clockingIn"
            @click="clockIn"
          >
            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ clockingIn ? __('Clocking In...') : __('Clock In') }}
          </TheButton>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div v-if="isAttendanceAdmin" class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
      <div class="relative rounded-md shadow-sm">
        <input
          v-model="filters.start_date"
          type="date"
          :placeholder="__('Start Date')"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="applyFilters"
        />
      </div>
      <div class="relative rounded-md shadow-sm">
        <input
          v-model="filters.end_date"
          type="date"
          :placeholder="__('End Date')"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="applyFilters"
        />
      </div>
      <div class="relative rounded-md shadow-sm">
        <select
          v-model="filters.user_id"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="applyFilters"
        >
          <option value="">{{ __('All Users') }}</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
      </div>
    </div>

    <!-- Date Filter for Regular Users -->
    <div v-else class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="relative rounded-md shadow-sm">
        <input
          v-model="filters.start_date"
          type="date"
          :placeholder="__('Start Date')"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="applyFilters"
        />
      </div>
      <div class="relative rounded-md shadow-sm">
        <input
          v-model="filters.end_date"
          type="date"
          :placeholder="__('End Date')"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="applyFilters"
        />
      </div>
    </div>

    <section>
      <Topbar :title="isAttendanceAdmin ? __('Attendance Records') : __('My Attendance')">
        <div v-if="isAttendanceAdmin" class="ltr:ml-auto rtl:mr-auto">
          <TheButton
            size="sm"
            data-cy="topbar-create-button"
            @click="openModal()"
          >
            {{ __('Add Attendance') }}
          </TheButton>
        </div>
      </Topbar>

      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div
              class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg"
            >
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      v-if="isAttendanceAdmin"
                      name="attendance"
                      :index="indexAttendance"
                      :label="__('User')"
                      sort="user_id"
                    />
                    <TableTh
                      name="attendance"
                      :index="indexAttendance"
                      :label="__('Date')"
                      sort="date"
                    />
                    <TableTh
                      name="attendance"
                      :index="indexAttendance"
                      :label="__('Clock In')"
                      sort="clock_in"
                    />
                    <TableTh
                      name="attendance"
                      :index="indexAttendance"
                      :label="__('Clock Out')"
                      sort="clock_out"
                    />
                    <TableTh
                      name="attendance"
                      :index="indexAttendance"
                      :label="__('Total Hours')"
                    />
                    <TableTh
                      name="attendance"
                      :index="indexAttendance"
                      :label="__('Notes')"
                    />
                    <th v-if="isAttendanceAdmin" class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="item in indexAttendance.data.data"
                    :key="item.id"
                  >
                    <td
                      v-if="isAttendanceAdmin"
                      class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                    >
                      <div class="flex items-center">
                        <UserAvatar
                          v-if="item.user"
                          class="h-6 w-6"
                          :avatar="item.user.avatar"
                        />
                        <span class="ltr:ml-2 rtl:mr-2">
                          {{ item.user?.name || 'N/A' }}
                        </span>
                      </div>
                    </td>
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                    >
                      {{ formatDate(item.date) }}
                    </td>
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                    >
                      {{ formatTime(item.clock_in) }}
                    </td>
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                    >
                      {{ item.clock_out ? formatTime(item.clock_out) : '-' }}
                    </td>
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                    >
                      {{ formatHours(item.total_hours) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                      <div class="max-w-xs truncate">
                        {{ item.notes || '-' }}
                      </div>
                    </td>

                    <td
                      v-if="isAttendanceAdmin"
                      class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5"
                    >
                      <span
                        class="ml-2 cursor-pointer"
                        @click="openModal(item.id)"
                      >
                        <PencilSquareIcon
                          class="w-5 text-gray-400 hover:text-gray-800"
                        />
                      </span>

                      <TrashIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.prevent="deleteRecord(item.id)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexPagination :index="indexAttendance" />
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
  import { inject, ref, onMounted } from 'vue'
  import { useIndexStore, useModalsStore } from 'spack'
  import {
    IndexPagination,
    Loader,
    TableTh,
    TheButton,
    Topbar,
    UserAvatar,
  } from 'thetheme'
  import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'
  import { axios } from 'spack/axios'
  import Form from './Form.vue'
  import { appData } from '../../app-data'
  import { computed } from 'vue'

  const can = inject('can')
  const __ = inject('__')
  const indexAttendance = useIndexStore('attendance')()
  const processing = ref(true)
  const users = ref([])
  const todayAttendance = ref(null)
  const isClockedIn = ref(false)
  const clockingIn = ref(false)
  const clockingOut = ref(false)
  const isAttendanceAdmin = computed(() => appData.is_attendance_admin)

  const filters = ref({
    start_date: '',
    end_date: '',
    user_id: '',
  })

  onMounted(() => {
    if (isAttendanceAdmin.value) {
      fetchUsers()
    }
    fetchStatus()
    checkProcessing()
  })

  indexAttendance.setConfig({
    uri: 'attendance',
    orderByDirection: 'desc',
    orderBy: 'date',
  })
  indexAttendance.fetch()

  async function fetchUsers() {
    try {
      const response = await axios.get('attendance/users')
      users.value = response.data
    } catch (error) {
      console.error('Error fetching users:', error)
    }
  }

  async function fetchStatus() {
    try {
      const response = await axios.get('attendance/status')
      todayAttendance.value = response.data.attendance
      isClockedIn.value = response.data.is_clocked_in
    } catch (error) {
      console.error('Error fetching attendance status:', error)
    }
  }

  async function clockIn() {
    clockingIn.value = true
    try {
      const response = await axios.post('attendance', {
        action: 'clock_in',
      })
      console.log('Clock in response:', response.data)
      // Refresh status to get the full attendance record
      await fetchStatus()
      indexAttendance.fetch() // Refresh the list
      alert(__('Clocked in successfully!'))
    } catch (error) {
      console.error('Error clocking in:', error)
      const message = error.response?.data?.message || __('Error clocking in. Please try again.')
      alert(message)
    } finally {
      clockingIn.value = false
    }
  }

  async function clockOut() {
    if (!todayAttendance.value) {
      alert(__('No attendance record found for today'))
      return
    }

    console.log('Clocking out with ID:', todayAttendance.value.id)
    console.log('Full attendance object:', todayAttendance.value)
    clockingOut.value = true
    try {
      const response = await axios.patch(`attendance/${todayAttendance.value.id}`, {
        action: 'clock_out',
      })
      console.log('Clock out response:', response.data)
      // Refresh status to get the updated attendance record
      await fetchStatus()
      indexAttendance.fetch() // Refresh the list
      alert(__('Clocked out successfully!'))
    } catch (error) {
      console.error('Error clocking out:', error)
      console.error('Error details:', error.response)
      const message = error.response?.data?.message || __('Error clocking out. Please try again.')
      alert(message)
    } finally {
      clockingOut.value = false
    }
  }

  function checkProcessing() {
    setTimeout(function () {
      if (indexAttendance.fetching) {
        checkProcessing()
        return
      }

      renderData()
    }, 150)
  }

  function renderData() {
    processing.value = false
  }

  function openModal(id: number | null = null) {
    useModalsStore().add(Form, { id })
  }

  async function deleteRecord(id: number) {
    if (!confirm(__('Are you sure you want to delete this attendance record?'))) {
      return
    }

    try {
      await axios.delete(`attendance/${id}`)
      indexAttendance.fetch() // Refresh the list
      alert(__('Attendance record deleted successfully!'))
    } catch (error) {
      console.error('Error deleting record:', error)
      alert(__('Error deleting record. Please try again.'))
    }
  }

  function applyFilters() {
    processing.value = true
    const params: any = {}

    if (filters.value.start_date) params.start_date = filters.value.start_date
    if (filters.value.end_date) params.end_date = filters.value.end_date
    if (filters.value.user_id) params.user_id = filters.value.user_id

    indexAttendance.params = { ...indexAttendance.params, ...params }
    indexAttendance.fetch()
    checkProcessing()
  }

  function formatDate(date: string): string {
    if (!date) return '-'
    const d = new Date(date)
    return d.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  }

  function formatTime(datetime: string): string {
    if (!datetime) return '-'
    const d = new Date(datetime)
    return d.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  function formatHours(minutes: number | null): string {
    if (!minutes) return '-'
    const hours = Math.floor(minutes / 60)
    const mins = minutes % 60
    return `${hours}h ${mins}m`
  }
</script>
