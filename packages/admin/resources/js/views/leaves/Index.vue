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
              <ClockIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Pending') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.pending }}
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
                  {{ __('Approved') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.approved }}
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
              <XCircleIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Rejected') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.rejected }}
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
                  {{ __('This Month') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.this_month }} {{ __('days') }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'my-leaves'"
            :class="[
              activeTab === 'my-leaves'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium'
            ]"
          >
            {{ __('My Leave Requests') }}
          </button>
          <button
            v-if="can('leave:view-all')"
            @click="activeTab = 'all-leaves'"
            :class="[
              activeTab === 'all-leaves'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium'
            ]"
          >
            {{ __('All Requests') }}
          </button>
          <button
            v-if="can('leave:approve')"
            @click="activeTab = 'pending-approvals'"
            :class="[
              activeTab === 'pending-approvals'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium'
            ]"
          >
            {{ __('Pending Approvals') }}
            <span v-if="statistics && statistics.pending > 0" class="ml-2 inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
              {{ statistics.pending }}
            </span>
          </button>
        </nav>
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
            :placeholder="__('Search...')"
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
          <option value="pending">{{ __('Pending') }}</option>
          <option value="approved">{{ __('Approved') }}</option>
          <option value="rejected">{{ __('Rejected') }}</option>
        </select>

        <select
          v-model="index.params.leave_type"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Types') }}</option>
          <option value="Annual Leave">{{ __('Annual Leave') }}</option>
          <option value="Sick Leave">{{ __('Sick Leave') }}</option>
          <option value="Maternity Leave">{{ __('Maternity Leave') }}</option>
          <option value="Paternity Leave">{{ __('Paternity Leave') }}</option>
          <option value="Unpaid Leave">{{ __('Unpaid Leave') }}</option>
        </select>
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton
          v-if="can('leave:create')"
          size="sm"
          @click="openLeaveModal()"
        >
          {{ __('Request Leave') }}
        </TheButton>
      </div>
    </div>

    <!-- Leaves Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      v-if="activeTab !== 'my-leaves'"
                      name="leave"
                      :index="index"
                      :label="__('Employee')"
                    />
                    <TableTh
                      name="leave"
                      :index="index"
                      :label="__('Leave Type')"
                      sort="leave_type"
                    />
                    <TableTh
                      name="leave"
                      :index="index"
                      :label="__('Start Date')"
                      sort="start_date"
                    />
                    <TableTh
                      name="leave"
                      :index="index"
                      :label="__('End Date')"
                      sort="end_date"
                    />
                    <TableTh
                      name="leave"
                      :index="index"
                      :label="__('Days')"
                      sort="days"
                    />
                    <TableTh
                      name="leave"
                      :index="index"
                      :label="__('Status')"
                      sort="status"
                    />
                    <TableTh
                      name="leave"
                      :index="index"
                      :label="__('Requested')"
                      sort="created_at"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="leave in index.data.data"
                    :key="leave.id"
                    class="hover:bg-gray-50"
                  >
                    <td v-if="activeTab !== 'my-leaves'" class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                          <UserAvatar :user="leave.user" size="10" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ leave.user.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ leave.user.email }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ leave.leave_type }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(leave.start_date).toLocaleDateString() }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(leave.end_date).toLocaleDateString() }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ leave.days }} {{ leave.is_half_day ? '(Half Day)' : '' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-yellow-100 text-yellow-800': leave.status === 'pending',
                          'bg-green-100 text-green-800': leave.status === 'approved',
                          'bg-red-100 text-red-800': leave.status === 'rejected'
                        }"
                      >
                        {{ leave.status.charAt(0).toUpperCase() + leave.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(leave.created_at).toLocaleDateString() }}
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <!-- Approve/Reject buttons for pending approvals -->
                      <template v-if="activeTab === 'pending-approvals' && leave.status === 'pending'">
                        <TheButton
                          size="xs"
                          variant="success"
                          @click="approveLeave(leave)"
                        >
                          {{ __('Approve') }}
                        </TheButton>
                        <TheButton
                          size="xs"
                          variant="danger"
                          class="ml-2"
                          @click="rejectLeave(leave)"
                        >
                          {{ __('Reject') }}
                        </TheButton>
                      </template>

                      <!-- Edit/Delete for own pending requests -->
                      <template v-else-if="leave.status === 'pending' && leave.user_id === currentUserId">
                        <PencilIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="openLeaveModal(leave)"
                        />
                        <TrashIcon
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="index.deleteIt(leave.id)"
                        />
                      </template>

                      <!-- View details -->
                      <EyeIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="viewLeaveDetails(leave)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexPagination :index="index" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Leave Form Modal -->
    <FormModal v-if="form.show" size="lg" @saved="index.get()">
      <Form :model-value="form.model" @close="form.show = false" />
    </FormModal>

    <!-- Leave Details Modal -->
    <ModalBase v-if="detailsModal.show" width="max-w-2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ __('Leave Request Details') }}</h3>
          <button @click="detailsModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div v-if="detailsModal.leave" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Employee') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.leave.user?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Leave Type') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.leave.leave_type }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ new Date(detailsModal.leave.start_date).toLocaleDateString() }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ new Date(detailsModal.leave.end_date).toLocaleDateString() }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Days') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.leave.days }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
              <p class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                  :class="{
                    'bg-yellow-100 text-yellow-800': detailsModal.leave.status === 'pending',
                    'bg-green-100 text-green-800': detailsModal.leave.status === 'approved',
                    'bg-red-100 text-red-800': detailsModal.leave.status === 'rejected'
                  }"
                >
                  {{ detailsModal.leave.status.charAt(0).toUpperCase() + detailsModal.leave.status.slice(1) }}
                </span>
              </p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Reason') }}</label>
            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ detailsModal.leave.reason }}</p>
          </div>

          <div v-if="detailsModal.leave.admin_notes">
            <label class="block text-sm font-medium text-gray-700">{{ __('Admin Notes') }}</label>
            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ detailsModal.leave.admin_notes }}</p>
          </div>

          <div v-if="detailsModal.leave.approved_by">
            <label class="block text-sm font-medium text-gray-700">{{ __('Approved/Rejected By') }}</label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.leave.approved_by?.name }}</p>
            <p class="text-sm text-gray-500">{{ new Date(detailsModal.leave.approved_at).toLocaleString() }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <TheButton @click="detailsModal.show = false">
            {{ __('Close') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>

    <!-- Approval Modal -->
    <ModalBase v-if="approvalModal.show" width="max-w-md">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ approvalModal.action === 'approve' ? __('Approve Leave') : __('Reject Leave') }}
          </h3>
          <button @click="approvalModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="space-y-4">
          <p class="text-sm text-gray-700">
            {{ approvalModal.action === 'approve'
              ? __('Are you sure you want to approve this leave request?')
              : __('Please provide a reason for rejection:')
            }}
          </p>

          <div>
            <label class="block text-sm font-medium text-gray-700">
              {{ approvalModal.action === 'approve' ? __('Admin Notes (Optional)') : __('Rejection Reason') }}
            </label>
            <textarea
              v-model="approvalModal.notes"
              rows="3"
              :required="approvalModal.action === 'reject'"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            ></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <TheButton white @click="approvalModal.show = false">
            {{ __('Cancel') }}
          </TheButton>
          <TheButton
            :class="approvalModal.action === 'reject' ? 'bg-red-600 hover:bg-red-700' : ''"
            @click="submitApproval"
            :disabled="approvalModal.action === 'reject' && !approvalModal.notes"
          >
            {{ approvalModal.action === 'approve' ? __('Approve') : __('Reject') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { CalendarIcon, CheckCircleIcon, ClockIcon, EyeIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon, XCircleIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import ModalBase from '@/thetheme/components/ModalBase.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Form from './Form.vue'
import { useIndex } from '@/composables/useIndex'
import { can } from '@/helpers'
import { appData } from '@/app-data'

const processing = ref(true)
const statistics = ref(null)
const activeTab = ref('my-leaves')
const currentUserId = ref(null)

const index = useIndex('leaves', {
  search: '',
  status: '',
  leave_type: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 15
})

const form = reactive({
  show: false,
  model: null
})

const detailsModal = reactive({
  show: false,
  leave: null
})

const approvalModal = reactive({
  show: false,
  action: null,
  leave: null,
  notes: ''
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openLeaveModal = (leave = null) => {
  form.model = leave
  form.show = true
}

const viewLeaveDetails = (leave) => {
  detailsModal.leave = leave
  detailsModal.show = true
}

const approveLeave = (leave) => {
  approvalModal.leave = leave
  approvalModal.action = 'approve'
  approvalModal.notes = ''
  approvalModal.show = true
}

const rejectLeave = (leave) => {
  approvalModal.leave = leave
  approvalModal.action = 'reject'
  approvalModal.notes = ''
  approvalModal.show = true
}

const submitApproval = async () => {
  try {
    const endpoint = approvalModal.action === 'approve' ? 'approve' : 'reject'
    await axios.post(`leaves/${approvalModal.leave.id}/${endpoint}`, {
      admin_notes: approvalModal.notes
    })

    approvalModal.show = false
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to process leave request:', error)
    alert(error.response?.data?.message || 'Failed to process leave request')
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('leaves/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadCurrentUser = () => {
  // Get current user ID from appData
  currentUserId.value = appData.user?.id
}

watch(activeTab, (newTab) => {
  if (newTab === 'my-leaves') {
    index.params.user_id = currentUserId.value
  } else if (newTab === 'pending-approvals') {
    index.params.status = 'pending'
    index.params.user_id = ''
  } else {
    index.params.user_id = ''
  }
  index.get()
})

onMounted(async () => {
  loadCurrentUser()
  index.params.user_id = currentUserId.value
  await index.get()
  await loadStatistics()
  processing.value = false
})
</script>
