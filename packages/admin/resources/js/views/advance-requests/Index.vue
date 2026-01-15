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
              <BanknotesIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Outstanding') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ formatCurrency(statistics.total_outstanding) }}
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
              <ArrowDownTrayIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Deducted') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.deducted }}
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
            @click="activeTab = 'my-requests'"
            :class="[
              activeTab === 'my-requests'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium'
            ]"
          >
            {{ __('My Requests') }}
          </button>
          <button
            v-if="can('advance_request:view-all')"
            @click="activeTab = 'all-requests'"
            :class="[
              activeTab === 'all-requests'
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium'
            ]"
          >
            {{ __('All Requests') }}
          </button>
          <button
            v-if="can('advance_request:approve')"
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
          <option value="deducted">{{ __('Deducted') }}</option>
        </select>
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton
          v-if="can('advance_request:create')"
          size="sm"
          @click="openRequestModal()"
        >
          {{ __('Request Advance') }}
        </TheButton>
      </div>
    </div>

    <!-- Requests Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      v-if="activeTab !== 'my-requests'"
                      name="advance_request"
                      :index="index"
                      :label="__('Employee')"
                    />
                    <TableTh
                      name="advance_request"
                      :index="index"
                      :label="__('Amount')"
                      sort="amount"
                    />
                    <TableTh
                      name="advance_request"
                      :index="index"
                      :label="__('Reason')"
                    />
                    <TableTh
                      name="advance_request"
                      :index="index"
                      :label="__('Status')"
                      sort="status"
                    />
                    <TableTh
                      name="advance_request"
                      :index="index"
                      :label="__('Outstanding')"
                    />
                    <TableTh
                      name="advance_request"
                      :index="index"
                      :label="__('Requested')"
                      sort="created_at"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="request in index.data.data"
                    :key="request.id"
                    class="hover:bg-gray-50"
                  >
                    <td v-if="activeTab !== 'my-requests'" class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                          <UserAvatar :user="request.user" size="10" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">
                            {{ request.user.name }}
                          </div>
                          <div class="text-sm text-gray-500">
                            {{ request.user.email }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {{ formatCurrency(request.amount, request.currency) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                      {{ request.reason }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-yellow-100 text-yellow-800': request.status === 'pending',
                          'bg-green-100 text-green-800': request.status === 'approved',
                          'bg-red-100 text-red-800': request.status === 'rejected',
                          'bg-blue-100 text-blue-800': request.status === 'deducted'
                        }"
                      >
                        {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span v-if="request.status === 'approved'" class="text-orange-600 font-medium">
                        {{ formatCurrency(request.amount - request.amount_deducted, request.currency) }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(request.created_at).toLocaleDateString() }}
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <!-- Approve/Reject buttons for pending approvals -->
                      <template v-if="activeTab === 'pending-approvals' && request.status === 'pending'">
                        <TheButton
                          size="xs"
                          variant="success"
                          @click="approveRequest(request)"
                        >
                          {{ __('Approve') }}
                        </TheButton>
                        <TheButton
                          size="xs"
                          variant="danger"
                          class="ml-2"
                          @click="rejectRequest(request)"
                        >
                          {{ __('Reject') }}
                        </TheButton>
                      </template>

                      <!-- Deduct button for approved requests -->
                      <template v-if="can('advance_request:deduct') && request.status === 'approved' && request.amount > request.amount_deducted">
                        <TheButton
                          size="xs"
                          variant="primary"
                          @click="openDeductModal(request)"
                        >
                          {{ __('Deduct') }}
                        </TheButton>
                      </template>

                      <!-- Edit/Delete for own pending requests -->
                      <template v-if="request.status === 'pending' && request.user_id === currentUserId">
                        <PencilIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800 ml-2"
                          @click="openRequestModal(request)"
                        />
                        <TrashIcon
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="index.deleteIt(request.id)"
                        />
                      </template>

                      <!-- View details -->
                      <EyeIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="viewRequestDetails(request)"
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

    <!-- Request Form Modal -->
    <FormModal v-if="form.show" size="lg" @saved="index.get()">
      <Form :model-value="form.model" @close="form.show = false" />
    </FormModal>

    <!-- Request Details Modal -->
    <ModalBase v-if="detailsModal.show" width="max-w-2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ __('Advance Request Details') }}</h3>
          <button @click="detailsModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div v-if="detailsModal.request" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Employee') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.request.user?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Amount') }}</label>
              <p class="mt-1 text-sm text-gray-900 font-semibold">{{ formatCurrency(detailsModal.request.amount, detailsModal.request.currency) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Amount Deducted') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(detailsModal.request.amount_deducted, detailsModal.request.currency) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Outstanding Balance') }}</label>
              <p class="mt-1 text-sm text-orange-600 font-semibold">{{ formatCurrency(detailsModal.request.amount - detailsModal.request.amount_deducted, detailsModal.request.currency) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
              <p class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                  :class="{
                    'bg-yellow-100 text-yellow-800': detailsModal.request.status === 'pending',
                    'bg-green-100 text-green-800': detailsModal.request.status === 'approved',
                    'bg-red-100 text-red-800': detailsModal.request.status === 'rejected',
                    'bg-blue-100 text-blue-800': detailsModal.request.status === 'deducted'
                  }"
                >
                  {{ detailsModal.request.status.charAt(0).toUpperCase() + detailsModal.request.status.slice(1) }}
                </span>
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Requested Date') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ new Date(detailsModal.request.created_at).toLocaleDateString() }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Reason') }}</label>
            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ detailsModal.request.reason }}</p>
          </div>

          <div v-if="detailsModal.request.admin_notes">
            <label class="block text-sm font-medium text-gray-700">{{ __('Admin Notes') }}</label>
            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ detailsModal.request.admin_notes }}</p>
          </div>

          <div v-if="detailsModal.request.approved_by">
            <label class="block text-sm font-medium text-gray-700">{{ __('Approved/Rejected By') }}</label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.request.approved_by?.name }}</p>
            <p class="text-sm text-gray-500">{{ new Date(detailsModal.request.approved_at).toLocaleString() }}</p>
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
            {{ approvalModal.action === 'approve' ? __('Approve Advance Request') : __('Reject Advance Request') }}
          </h3>
          <button @click="approvalModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-700">
              <strong>{{ __('Employee') }}:</strong> {{ approvalModal.request?.user?.name }}
            </p>
            <p class="text-sm text-gray-700 mt-1">
              <strong>{{ __('Amount') }}:</strong> {{ formatCurrency(approvalModal.request?.amount, approvalModal.request?.currency) }}
            </p>
          </div>

          <p class="text-sm text-gray-700">
            {{ approvalModal.action === 'approve'
              ? __('Are you sure you want to approve this advance request?')
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

    <!-- Deduct Modal -->
    <ModalBase v-if="deductModal.show" width="max-w-md">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ __('Record Deduction') }}</h3>
          <button @click="deductModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-700">
              <strong>{{ __('Employee') }}:</strong> {{ deductModal.request?.user?.name }}
            </p>
            <p class="text-sm text-gray-700 mt-1">
              <strong>{{ __('Total Advance') }}:</strong> {{ formatCurrency(deductModal.request?.amount, deductModal.request?.currency) }}
            </p>
            <p class="text-sm text-gray-700 mt-1">
              <strong>{{ __('Already Deducted') }}:</strong> {{ formatCurrency(deductModal.request?.amount_deducted, deductModal.request?.currency) }}
            </p>
            <p class="text-sm text-orange-600 font-semibold mt-1">
              <strong>{{ __('Outstanding') }}:</strong> {{ formatCurrency(deductModal.maxAmount, deductModal.request?.currency) }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Deduction Amount') }}</label>
            <input
              v-model.number="deductModal.amount"
              type="number"
              step="0.01"
              :min="0.01"
              :max="deductModal.maxAmount"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Maximum') }}: {{ formatCurrency(deductModal.maxAmount, deductModal.request?.currency) }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <TheButton white @click="deductModal.show = false">
            {{ __('Cancel') }}
          </TheButton>
          <TheButton
            @click="submitDeduction"
            :disabled="!deductModal.amount || deductModal.amount <= 0 || deductModal.amount > deductModal.maxAmount"
          >
            {{ __('Record Deduction') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue'
import { ArrowDownTrayIcon, BanknotesIcon, CheckCircleIcon, ClockIcon, EyeIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon, XCircleIcon } from '@heroicons/vue/24/outline'
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
const activeTab = ref('my-requests')
const currentUserId = ref(null)

const index = useIndex('advance-requests', {
  search: '',
  status: '',
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
  request: null
})

const approvalModal = reactive({
  show: false,
  action: null,
  request: null,
  notes: ''
})

const deductModal = reactive({
  show: false,
  request: null,
  amount: 0,
  maxAmount: 0
})

const formatCurrency = (amount, currency = 'MWK') => {
  return new Intl.NumberFormat('en-MW', {
    style: 'currency',
    currency: currency || 'MWK',
    minimumFractionDigits: 2
  }).format(amount || 0)
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openRequestModal = (request = null) => {
  form.model = request
  form.show = true
}

const viewRequestDetails = (request) => {
  detailsModal.request = request
  detailsModal.show = true
}

const approveRequest = (request) => {
  approvalModal.request = request
  approvalModal.action = 'approve'
  approvalModal.notes = ''
  approvalModal.show = true
}

const rejectRequest = (request) => {
  approvalModal.request = request
  approvalModal.action = 'reject'
  approvalModal.notes = ''
  approvalModal.show = true
}

const openDeductModal = (request) => {
  deductModal.request = request
  deductModal.maxAmount = request.amount - request.amount_deducted
  deductModal.amount = deductModal.maxAmount
  deductModal.show = true
}

const submitApproval = async () => {
  try {
    const endpoint = approvalModal.action === 'approve' ? 'approve' : 'reject'
    await axios.post(`advance-requests/${approvalModal.request.id}/${endpoint}`, {
      admin_notes: approvalModal.notes
    })

    approvalModal.show = false
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to process advance request:', error)
    alert(error.response?.data?.message || 'Failed to process advance request')
  }
}

const submitDeduction = async () => {
  try {
    await axios.post(`advance-requests/${deductModal.request.id}/deduct`, {
      amount: deductModal.amount
    })

    deductModal.show = false
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to record deduction:', error)
    alert(error.response?.data?.message || 'Failed to record deduction')
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('advance-requests/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadCurrentUser = () => {
  currentUserId.value = appData.user?.id
}

watch(activeTab, (newTab) => {
  if (newTab === 'my-requests') {
    index.params.user_id = currentUserId.value
    index.params.status = ''
  } else if (newTab === 'pending-approvals') {
    index.params.status = 'pending'
    index.params.user_id = ''
  } else {
    index.params.user_id = ''
    index.params.status = ''
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
