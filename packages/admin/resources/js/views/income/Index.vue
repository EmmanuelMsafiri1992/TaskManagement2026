<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Total Income') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ formatCurrency(statistics.total_income) }}
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
                  {{ __('Received') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.received }}
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
              <ArrowTrendingUpIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Last Month') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ formatCurrency(statistics.last_month) }}
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
          <option value="received">{{ __('Received') }}</option>
          <option value="cancelled">{{ __('Cancelled') }}</option>
        </select>

        <select
          v-model="index.params.source"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Sources') }}</option>
          <option value="sales">{{ __('Sales') }}</option>
          <option value="services">{{ __('Services') }}</option>
          <option value="consulting">{{ __('Consulting') }}</option>
          <option value="adsense">{{ __('AdSense') }}</option>
          <option value="quotation">{{ __('Quotation') }}</option>
          <option value="other">{{ __('Other') }}</option>
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
          @click="openIncomeModal()"
        >
          {{ __('Add Income') }}
        </TheButton>
      </div>
    </div>

    <!-- Income Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="income"
                      :index="index"
                      :label="__('Date')"
                      sort="income_date"
                    />
                    <TableTh
                      name="income"
                      :index="index"
                      :label="__('Description')"
                    />
                    <TableTh
                      name="income"
                      :index="index"
                      :label="__('Source')"
                      sort="source"
                    />
                    <TableTh
                      name="income"
                      :index="index"
                      :label="__('Amount')"
                      sort="amount"
                    />
                    <TableTh
                      name="income"
                      :index="index"
                      :label="__('Status')"
                      sort="status"
                    />
                    <TableTh
                      name="income"
                      :index="index"
                      :label="__('Created By')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="income in index.data.data"
                    :key="income.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ formatDate(income.income_date) }}
                    </td>
                    <td class="max-w-xs truncate px-6 py-4 text-sm text-gray-900">
                      <div>{{ income.description }}</div>
                      <div v-if="income.client_name" class="text-xs text-gray-500">
                        {{ income.client_name }}
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span
                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                        :class="getSourceBadgeClass(income.source)"
                      >
                        {{ formatSource(income.source) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {{ income.currency }} {{ formatNumber(income.amount) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-yellow-100 text-yellow-800': income.status === 'pending',
                          'bg-green-100 text-green-800': income.status === 'received',
                          'bg-red-100 text-red-800': income.status === 'cancelled'
                        }"
                      >
                        {{ income.status.charAt(0).toUpperCase() + income.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-8 w-8 flex-shrink-0">
                          <UserAvatar :user="income.user" size="8" />
                        </div>
                        <div class="ml-3">
                          <div class="text-sm font-medium text-gray-900">
                            {{ income.user?.name }}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <!-- Mark as Received button for pending (admin only) -->
                      <template v-if="income.status === 'pending' && isSuperAdmin">
                        <TheButton
                          size="xs"
                          variant="success"
                          @click="markAsReceived(income)"
                        >
                          {{ __('Mark Received') }}
                        </TheButton>
                      </template>

                      <!-- Edit/Delete for own pending income -->
                      <template v-if="income.status === 'pending' && (income.user_id === currentUserId || isSuperAdmin)">
                        <PencilIcon
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="openIncomeModal(income)"
                        />
                        <TrashIcon
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="index.deleteIt(income.id)"
                        />
                      </template>

                      <!-- View details -->
                      <EyeIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="viewIncomeDetails(income)"
                      />
                    </td>
                  </tr>
                  <tr v-if="!index.data.data || index.data.data.length === 0">
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                      {{ __('No income records found') }}
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

    <!-- Income Form Modal -->
    <FormModal v-if="form.show" size="lg" @saved="handleFormSaved">
      <Form :model-value="form.model" @close="form.show = false" />
    </FormModal>

    <!-- Income Details Modal -->
    <ModalBase v-if="detailsModal.show" width="max-w-2xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ __('Income Details') }}</h3>
          <button @click="detailsModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div v-if="detailsModal.income" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Date') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(detailsModal.income.income_date) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Amount') }}</label>
              <p class="mt-1 text-sm font-semibold text-gray-900">
                {{ detailsModal.income.currency }} {{ formatNumber(detailsModal.income.amount) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Source') }}</label>
              <p class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                  :class="getSourceBadgeClass(detailsModal.income.source)"
                >
                  {{ formatSource(detailsModal.income.source) }}
                </span>
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
              <p class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                  :class="{
                    'bg-yellow-100 text-yellow-800': detailsModal.income.status === 'pending',
                    'bg-green-100 text-green-800': detailsModal.income.status === 'received',
                    'bg-red-100 text-red-800': detailsModal.income.status === 'cancelled'
                  }"
                >
                  {{ detailsModal.income.status.charAt(0).toUpperCase() + detailsModal.income.status.slice(1) }}
                </span>
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Created By') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.income.user?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Created On') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(detailsModal.income.created_at) }}</p>
            </div>
            <div v-if="detailsModal.income.invoice_number">
              <label class="block text-sm font-medium text-gray-700">{{ __('Invoice Number') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.income.invoice_number }}</p>
            </div>
            <div v-if="detailsModal.income.client_name">
              <label class="block text-sm font-medium text-gray-700">{{ __('Client') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailsModal.income.client_name }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.income.description }}</p>
          </div>

          <div v-if="detailsModal.income.category">
            <label class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.income.category }}</p>
          </div>

          <div v-if="detailsModal.income.notes">
            <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
            <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ detailsModal.income.notes }}</p>
          </div>

          <div v-if="detailsModal.income.receiver">
            <label class="block text-sm font-medium text-gray-700">{{ __('Received By') }}</label>
            <p class="mt-1 text-sm text-gray-900">{{ detailsModal.income.receiver?.name }}</p>
            <p class="text-sm text-gray-500">{{ formatDateTime(detailsModal.income.received_at) }}</p>
          </div>

          <div v-if="detailsModal.income.quotation">
            <label class="block text-sm font-medium text-gray-700">{{ __('Related Quotation') }}</label>
            <p class="mt-1 text-sm text-indigo-600">
              {{ detailsModal.income.quotation.quotation_number || `#${detailsModal.income.quotation.id}` }}
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <TheButton @click="detailsModal.show = false">
            {{ __('Close') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>

    <!-- Mark as Received Confirmation Modal -->
    <ModalBase v-if="receivedModal.show" width="max-w-md">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ __('Mark as Received') }}
          </h3>
          <button @click="receivedModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="space-y-4">
          <p class="text-sm text-gray-700">
            {{ __('Are you sure you want to mark this income as received?') }}
          </p>

          <div class="rounded-md bg-gray-50 p-3">
            <p class="text-sm font-medium text-gray-900">{{ receivedModal.income?.description }}</p>
            <p class="text-sm text-gray-600">
              {{ receivedModal.income?.currency }} {{ formatNumber(receivedModal.income?.amount) }}
            </p>
          </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <TheButton white @click="receivedModal.show = false">
            {{ __('Cancel') }}
          </TheButton>
          <TheButton @click="submitMarkAsReceived">
            {{ __('Mark Received') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import {
  ArrowTrendingUpIcon,
  BanknotesIcon,
  CheckCircleIcon,
  ClockIcon,
  CurrencyDollarIcon,
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
const currentUserId = ref(null)
const isSuperAdmin = ref(false)

const index = useIndex('income', {
  search: '',
  status: '',
  source: '',
  start_date: '',
  end_date: '',
  sort_by: 'income_date',
  sort_order: 'desc',
  per_page: 15
})

const form = reactive({
  show: false,
  model: null
})

const detailsModal = reactive({
  show: false,
  income: null
})

const receivedModal = reactive({
  show: false,
  income: null
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openIncomeModal = (income = null) => {
  form.model = income
  form.show = true
}

const viewIncomeDetails = (income) => {
  detailsModal.income = income
  detailsModal.show = true
}

const markAsReceived = (income) => {
  receivedModal.income = income
  receivedModal.show = true
}

const submitMarkAsReceived = async () => {
  try {
    await axios.post(`income/${receivedModal.income.id}/mark-as-received`)

    receivedModal.show = false
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to mark income as received:', error)
    alert(error.response?.data?.message || 'Failed to mark income as received')
  }
}

const handleFormSaved = () => {
  index.get()
  loadStatistics()
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('income/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
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

const formatSource = (source) => {
  const sources = {
    sales: 'Sales',
    services: 'Services',
    consulting: 'Consulting',
    adsense: 'AdSense',
    quotation: 'Quotation',
    other: 'Other'
  }
  return sources[source] || source
}

const getSourceBadgeClass = (source) => {
  const classes = {
    sales: 'bg-blue-100 text-blue-800',
    services: 'bg-green-100 text-green-800',
    consulting: 'bg-purple-100 text-purple-800',
    adsense: 'bg-yellow-100 text-yellow-800',
    quotation: 'bg-indigo-100 text-indigo-800',
    other: 'bg-gray-100 text-gray-800'
  }
  return classes[source] || 'bg-gray-100 text-gray-800'
}

onMounted(async () => {
  currentUserId.value = appData.user?.id
  isSuperAdmin.value = appData.user?.is_super_admin || false

  await Promise.all([
    index.get(),
    loadStatistics()
  ])

  processing.value = false
})
</script>
