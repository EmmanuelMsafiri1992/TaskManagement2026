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
                  {{ __('This Month') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ formatCurrency(statistics.this_month) }}
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
              <CurrencyDollarIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Total Expenses') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ formatCurrency(statistics.total_expenses) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-2">
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
          v-model="index.params.category"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Categories') }}</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <input
          v-model="index.params.start_date"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        />

        <input
          v-model="index.params.end_date"
          type="date"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        />
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton
          size="sm"
          @click="openExpenseModal()"
        >
          {{ __('Add Expense') }}
        </TheButton>
      </div>
    </div>

    <!-- Expenses Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="expense"
                      :index="index"
                      :label="__('Date')"
                      sort="expense_date"
                    />
                    <TableTh
                      name="expense"
                      :index="index"
                      :label="__('Description')"
                    />
                    <TableTh
                      name="expense"
                      :index="index"
                      :label="__('Category')"
                      sort="category"
                    />
                    <TableTh
                      name="expense"
                      :index="index"
                      :label="__('Amount')"
                      sort="amount"
                    />
                    <TableTh
                      name="expense"
                      :index="index"
                      :label="__('Status')"
                      sort="status"
                    />
                    <TableTh
                      name="expense"
                      :index="index"
                      :label="__('Submitted By')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="expense in index.data.data"
                    :key="expense.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ formatDate(expense.expense_date) }}
                    </td>
                    <td class="max-w-xs truncate px-6 py-4 text-sm text-gray-900">
                      {{ expense.description }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span v-if="expense.category" class="inline-flex rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
                        {{ expense.category }}
                      </span>
                      <span v-else class="text-gray-400">-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {{ expense.currency }} {{ formatNumber(expense.amount) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-yellow-100 text-yellow-800': expense.status === 'pending',
                          'bg-green-100 text-green-800': expense.status === 'approved',
                          'bg-red-100 text-red-800': expense.status === 'rejected'
                        }"
                      >
                        {{ expense.status.charAt(0).toUpperCase() + expense.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-8 w-8 flex-shrink-0">
                          <UserAvatar :user="expense.user" size="8" />
                        </div>
                        <div class="ml-3">
                          <div class="text-sm font-medium text-gray-900">
                            {{ expense.user?.name }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <!-- Approve/Reject buttons for pending (admin only) -->
                      <template v-if="expense.status === 'pending' && isSuperAdmin">
                        <TheButton
                          size="xs"
                          variant="success"
                          @click="approveExpense(expense)"
                        >
                          {{ __('Approve') }}
                        </TheButton>
                        <TheButton
                          size="xs"
                          variant="danger"
                          class="ml-2"
                          @click="rejectExpense(expense)"
                        >
                          {{ __('Reject') }}
                        </TheButton>
                      </template>

                      <!-- View receipt -->
                      <a
                        v-if="expense.receipt_path"
                        :href="`/storage/${expense.receipt_path}`"
                        target="_blank"
                        class="ml-2 text-gray-400 hover:text-gray-600"
                        :title="__('View Receipt')"
                      >
                        <DocumentIcon class="h-5 w-5" />
                      </a>

                      <!-- Edit/Delete for own pending expenses -->
                      <template v-if="expense.status === 'pending' && (expense.user_id === currentUserId || isSuperAdmin)">
                        <PencilIcon
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="openExpenseModal(expense)"
                        />
                        <TrashIcon
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="index.deleteIt(expense.id)"
                        />
                      </template>

                      <!-- View details -->
                      <EyeIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="viewExpenseDetails(expense)"
                      />
                    </td>
                  </tr>
                  <tr v-if="!index.data.data || index.data.data.length === 0">
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                      {{ __('No expenses found') }}
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

    <!-- Expense Form Modal -->
    <FormModal v-if="form.show" size="lg" @saved="handleFormSaved">
      <Form :model-value="form.model" @close="form.show = false" />
    </FormModal>

    <!-- Expense Details Modal -->
    <ModalBase v-if="detailsModal.show" width="max-w-2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ __('Expense Details') }}</h3>
          <button class="text-gray-400 hover:text-gray-600" @click="detailsModal.show = false">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div v-if="detailsModal.expense" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Date') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(detailsModal.expense.expense_date) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Amount') }}</label>
              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ detailsModal.expense.currency }} {{ formatNumber(detailsModal.expense.amount) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.expense.category || '-' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
              <p class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                  :class="{
                    'bg-yellow-100 text-yellow-800': detailsModal.expense.status === 'pending',
                    'bg-green-100 text-green-800': detailsModal.expense.status === 'approved',
                    'bg-red-100 text-red-800': detailsModal.expense.status === 'rejected'
                  }"
                >
                  {{ detailsModal.expense.status.charAt(0).toUpperCase() + detailsModal.expense.status.slice(1) }}
                </span>
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Submitted By') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.expense.user?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Submitted On') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(detailsModal.expense.created_at) }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.expense.description }}</p>
          </div>

          <div v-if="detailsModal.expense.notes">
            <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ detailsModal.expense.notes }}</p>
          </div>

          <div v-if="detailsModal.expense.receipt_path">
            <label class="block text-sm font-medium text-gray-700">{{ __('Receipt') }}</label>
            <a
              :href="`/storage/${detailsModal.expense.receipt_path}`"
              target="_blank"
              class="mt-1 inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500"
            >
              <DocumentIcon class="mr-1 h-4 w-4" />
              {{ detailsModal.expense.receipt_name || __('View Receipt') }}
            </a>
          </div>

          <div v-if="detailsModal.expense.approver">
            <label class="block text-sm font-medium text-gray-700">
              {{ detailsModal.expense.status === 'approved' ? __('Approved By') : __('Rejected By') }}
            </label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.expense.approver?.name }}</p>
            <p class="text-sm text-gray-500">{{ formatDateTime(detailsModal.expense.approved_at) }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <TheButton @click="detailsModal.show = false">
            {{ __('Close') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>

    <!-- Approval Confirmation Modal -->
    <ModalBase v-if="approvalModal.show" width="max-w-md">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ approvalModal.action === 'approve' ? __('Approve Expense') : __('Reject Expense') }}
          </h3>
          <button class="text-gray-400 hover:text-gray-600" @click="approvalModal.show = false">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="space-y-4">
          <p class="text-sm text-gray-700">
            {{ approvalModal.action === 'approve'
              ? __('Are you sure you want to approve this expense?')
              : __('Are you sure you want to reject this expense?')
            }}
          </p>

          <div class="rounded-md bg-gray-50 p-3">
            <p class="text-sm font-medium text-gray-900">{{ approvalModal.expense?.description }}</p>
            <p class="text-sm text-gray-600">
              {{ approvalModal.expense?.currency }} {{ formatNumber(approvalModal.expense?.amount) }}
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <TheButton white @click="approvalModal.show = false">
            {{ __('Cancel') }}
          </TheButton>
          <TheButton
            :class="approvalModal.action === 'reject' ? 'bg-red-600 hover:bg-red-700' : ''"
            @click="submitApproval"
          >
            {{ approvalModal.action === 'approve' ? __('Approve') : __('Reject') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import {
  BanknotesIcon,
  CheckCircleIcon,
  ClockIcon,
  CurrencyDollarIcon,
  DocumentIcon,
  EyeIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'
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
import { appData } from '@/app-data'

const processing = ref(true)
const statistics = ref(null)
const categories = ref([])
const currentUserId = ref(null)
const isSuperAdmin = ref(false)

const index = useIndex('expenses', {
  search: '',
  status: '',
  category: '',
  start_date: '',
  end_date: '',
  sort_by: 'expense_date',
  sort_order: 'desc',
  per_page: 15
})

const form = reactive({
  show: false,
  model: null
})

const detailsModal = reactive({
  show: false,
  expense: null
})

const approvalModal = reactive({
  show: false,
  action: null,
  expense: null
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openExpenseModal = (expense = null) => {
  form.model = expense
  form.show = true
}

const viewExpenseDetails = (expense) => {
  detailsModal.expense = expense
  detailsModal.show = true
}

const approveExpense = (expense) => {
  approvalModal.expense = expense
  approvalModal.action = 'approve'
  approvalModal.show = true
}

const rejectExpense = (expense) => {
  approvalModal.expense = expense
  approvalModal.action = 'reject'
  approvalModal.show = true
}

const submitApproval = async () => {
  try {
    const endpoint = approvalModal.action === 'approve' ? 'approve' : 'reject'
    await axios.post(`expenses/${approvalModal.expense.id}/${endpoint}`)

    approvalModal.show = false
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to process expense:', error)
    alert(error.response?.data?.message || 'Failed to process expense')
  }
}

const handleFormSaved = () => {
  index.get()
  loadStatistics()
  loadCategories()
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('expenses/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadCategories = async () => {
  try {
    const response = await axios.get('expenses/categories')
    categories.value = response.data.data
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString()
}

const formatNumber = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatCurrency = (amount) => {
  if (!amount) return 'MWK 0.00'
  return 'MWK ' + formatNumber(amount)
}

onMounted(async () => {
  currentUserId.value = appData.user?.id
  isSuperAdmin.value = appData.user?.is_super_admin || false

  await Promise.all([
    index.get(),
    loadStatistics(),
    loadCategories()
  ])

  processing.value = false
})
</script>
