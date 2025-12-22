<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header with Statistics -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Payroll Management') }}</h1>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Draft') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.draft }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Approved') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.approved }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Paid') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.paid }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('This Month') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ new Intl.NumberFormat().format(statistics.this_month_total) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions and Filters -->
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
          <option value="draft">{{ __('Draft') }}</option>
          <option value="approved">{{ __('Approved') }}</option>
          <option value="paid">{{ __('Paid') }}</option>
          <option value="sent">{{ __('Sent') }}</option>
        </select>
      </div>

      <div class="flex gap-2">
        <TheButton variant="secondary" @click="openGenerateModal">
          <CogIcon class="mr-2 h-4 w-4" />
          {{ __('Generate Payroll') }}
        </TheButton>
        <TheButton v-if="can('payroll:create')" @click="openPayrollModal()">
          {{ __('Create Payroll') }}
        </TheButton>
      </div>
    </div>

    <!-- Payrolls Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh name="payroll" :index="index" :label="__('Employee')" />
                    <TableTh name="payroll" :index="index" :label="__('Period')" sort="payroll_period" />
                    <TableTh name="payroll" :index="index" :label="__('Basic Salary')" sort="basic_salary" />
                    <TableTh name="payroll" :index="index" :label="__('Net Salary')" sort="net_salary" />
                    <TableTh name="payroll" :index="index" :label="__('Status')" sort="status" />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="payroll in index.data.data" :key="payroll.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="h-10 w-10 flex-shrink-0">
                          <UserAvatar :user="payroll.user" size="10" />
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ payroll.user.name }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ payroll.payroll_period }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Intl.NumberFormat().format(payroll.basic_salary) }} {{ payroll.currency }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">
                      {{ new Intl.NumberFormat().format(payroll.net_salary) }} {{ payroll.currency }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-gray-100 text-gray-800': payroll.status === 'draft',
                          'bg-green-100 text-green-800': payroll.status === 'approved',
                          'bg-blue-100 text-blue-800': payroll.status === 'paid',
                          'bg-purple-100 text-purple-800': payroll.status === 'sent'
                        }"
                      >
                        {{ payroll.status.charAt(0).toUpperCase() + payroll.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <div class="flex items-center justify-end gap-2">
                        <TheButton
                          v-if="payroll.status === 'draft' && can('payroll:approve')"
                          size="xs"
                          variant="success"
                          @click="approvePayroll(payroll.id)"
                        >
                          {{ __('Approve') }}
                        </TheButton>
                        <TheButton
                          v-if="payroll.status === 'approved' && can('payroll:pay')"
                          size="xs"
                          @click="markAsPaid(payroll.id)"
                        >
                          {{ __('Mark Paid') }}
                        </TheButton>
                        <EyeIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="viewPayroll(payroll.id)"
                        />
                        <PencilIcon
                          v-if="payroll.status === 'draft' && can('payroll:update')"
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="openPayrollModal(payroll)"
                        />
                        <TrashIcon
                          v-if="payroll.status === 'draft' && can('payroll:delete')"
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="index.deleteIt(payroll.id)"
                        />
                      </div>
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

    <!-- Generate Payroll Modal -->
    <ModalBase v-model="generateModal.show" size="md">
      <template #title>{{ __('Generate Payroll for Month') }}</template>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">{{ __('Month') }}</label>
          <input
            v-model="generateModal.month"
            type="month"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required
          />
        </div>

        <p class="text-sm text-gray-600">
          {{ __('This will generate draft payroll records for all active employees for the selected month.') }}
        </p>
      </div>

      <template #footer>
        <TheButton variant="secondary" @click="generateModal.show = false">
          {{ __('Cancel') }}
        </TheButton>
        <TheButton @click="generatePayroll">
          {{ __('Generate') }}
        </TheButton>
      </template>
    </ModalBase>

    <!-- Payroll Form Modal -->
    <FormModal v-model="form.show" size="2xl" @saved="index.get()">
      <FormPayroll :model-value="form.model" @close="form.show = false" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  BanknotesIcon, CheckCircleIcon, CogIcon, CurrencyDollarIcon, DocumentTextIcon,
  EyeIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon
} from '@heroicons/vue/24/outline'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import ModalBase from '@/thetheme/components/ModalBase.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import FormPayroll from './Form.vue'
import { useIndex } from '@/composables/useIndex'
import { can } from '@/helpers'

const router = useRouter()
const processing = ref(true)
const statistics = ref(null)

const index = useIndex('/api/payrolls', {
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

const generateModal = reactive({
  show: false,
  month: new Date().toISOString().slice(0, 7)
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openPayrollModal = (payroll = null) => {
  form.model = payroll
  form.show = true
}

const openGenerateModal = () => {
  generateModal.show = true
}

const generatePayroll = async () => {
  try {
    const response = await axios.post('/api/payrolls/generate', {
      month: generateModal.month
    })

    alert(response.data.message)
    generateModal.show = false
    index.get()
  } catch (error) {
    console.error('Failed to generate payroll:', error)
    alert('Failed to generate payroll')
  }
}

const approvePayroll = async (id) => {
  if (!confirm('Are you sure you want to approve this payroll?')) return

  try {
    await axios.post(`/api/payrolls/${id}/approve`)
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to approve payroll:', error)
  }
}

const markAsPaid = async (id) => {
  if (!confirm('Mark this payroll as paid?')) return

  try {
    await axios.post(`/api/payrolls/${id}/mark-as-paid`)
    index.get()
    loadStatistics()
  } catch (error) {
    console.error('Failed to mark as paid:', error)
  }
}

const viewPayroll = (id) => {
  router.push(`/payrolls/${id}`)
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('/api/payrolls/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

onMounted(async () => {
  await index.get()
  await loadStatistics()
  processing.value = false
})
</script>
