<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Quotations') }}</h1>
      <p class="mt-1 text-sm text-gray-500">{{ __('Manage your quotations and proposals') }}</p>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <DocumentTextIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.total }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <PencilSquareIcon class="h-6 w-6 text-gray-400" />
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
              <PaperAirplaneIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Sent') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.sent }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Accepted') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.accepted }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-6 w-6 text-indigo-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Value') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ formatCurrency(statistics.total_value) }}</dd>
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
            :placeholder="__('Search quotations...')"
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
          <option value="sent">{{ __('Sent') }}</option>
          <option value="accepted">{{ __('Accepted') }}</option>
          <option value="rejected">{{ __('Rejected') }}</option>
          <option value="expired">{{ __('Expired') }}</option>
        </select>
      </div>

      <div class="flex gap-2">
        <TheButton @click="openQuotationModal()">
          <PlusIcon class="mr-2 h-4 w-4" />
          {{ __('New Quotation') }}
        </TheButton>
      </div>
    </div>

    <!-- Quotations Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh name="quotation" :index="index" :label="__('Quotation #')" sort="quotation_number" />
                    <TableTh name="quotation" :index="index" :label="__('Customer')" sort="customer_name" />
                    <TableTh name="quotation" :index="index" :label="__('Date')" sort="quotation_date" />
                    <TableTh name="quotation" :index="index" :label="__('Valid Until')" sort="valid_until" />
                    <TableTh name="quotation" :index="index" :label="__('Amount')" sort="total_amount" />
                    <TableTh name="quotation" :index="index" :label="__('Status')" sort="status" />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-if="!index.data?.data?.length" class="hover:bg-gray-50">
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                      {{ __('No quotations found') }}
                    </td>
                  </tr>
                  <tr v-for="quotation in (index.data?.data || [])" :key="quotation.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="text-sm font-medium text-indigo-600">{{ quotation.quotation_number }}</div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">{{ quotation.customer_name }}</div>
                      <div class="text-sm text-gray-500">{{ quotation.customer_email }}</div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ formatDate(quotation.quotation_date) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span :class="isExpired(quotation.valid_until) ? 'text-red-500' : ''">
                        {{ formatDate(quotation.valid_until) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {{ quotation.currency }} {{ formatNumber(quotation.total_amount) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="getStatusClass(quotation.status)"
                      >
                        {{ quotation.status.charAt(0).toUpperCase() + quotation.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <div class="flex items-center justify-end gap-2">
                        <EyeIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="viewQuotation(quotation.id)"
                        />
                        <DocumentArrowDownIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-indigo-600"
                          @click="downloadPdf(quotation.id)"
                        />
                        <PencilIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="openQuotationModal(quotation)"
                        />
                        <TrashIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-red-600"
                          @click="index.deleteIt(quotation.id)"
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

    <!-- Quotation Form Modal -->
    <FormModal v-if="form.show" size="4xl" @close="form.show = false">
      <FormQuotation :model-value="form.model" @close="form.show = false" @saved="handleSaved" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  CheckCircleIcon, CurrencyDollarIcon, DocumentArrowDownIcon, DocumentTextIcon,
  EyeIcon, MagnifyingGlassIcon, PaperAirplaneIcon, PencilIcon, PencilSquareIcon,
  PlusIcon, TrashIcon
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import FormQuotation from './Form.vue'
import { useIndex } from '@/composables/useIndex'

const router = useRouter()
const processing = ref(true)
const statistics = ref(null)

const index = useIndex('quotations', {
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

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openQuotationModal = (quotation = null) => {
  form.model = quotation
  form.show = true
}

const viewQuotation = (id) => {
  router.push(`/quotations/${id}`)
}

const downloadPdf = async (id) => {
  try {
    const response = await axios.get(`quotations/${id}/pdf`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `quotation-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Failed to download PDF:', error)
  }
}

const handleSaved = () => {
  index.get()
  loadStatistics()
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('quotations/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const formatCurrency = (amount) => {
  return 'MWK ' + formatNumber(amount)
}

const isExpired = (date) => {
  if (!date) return false
  return new Date(date) < new Date()
}

const getStatusClass = (status) => {
  return {
    'bg-gray-100 text-gray-800': status === 'draft',
    'bg-blue-100 text-blue-800': status === 'sent',
    'bg-green-100 text-green-800': status === 'accepted',
    'bg-red-100 text-red-800': status === 'rejected',
    'bg-yellow-100 text-yellow-800': status === 'expired',
  }
}

onMounted(async () => {
  try {
    await index.get()
    await loadStatistics()
    processing.value = false
  } catch (error) {
    console.error('Error in onMounted:', error)
    processing.value = false
  }
})
</script>
