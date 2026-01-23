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
        <TheButton variant="secondary" @click="showTemplatePreview = true">
          <SwatchIcon class="mr-2 h-4 w-4" />
          {{ __('View Templates') }}
        </TheButton>
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

    <!-- Template Preview Modal -->
    <FormModal v-if="showTemplatePreview" size="5xl" @close="showTemplatePreview = false">
      <div>
        <div class="border-b border-gray-200 px-6 py-4">
          <h2 class="text-lg font-semibold text-gray-900">{{ __('Quotation Templates') }}</h2>
          <p class="mt-1 text-sm text-gray-500">{{ __('Preview available templates before creating a quotation') }}</p>
        </div>

        <div class="max-h-[80vh] overflow-y-auto px-6 py-4">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Style 1 - Classic -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 h-8 rounded bg-gray-100"></div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mb-1 h-2 w-full rounded bg-gray-100"></div>
                  <div class="mb-1 h-2 w-3/4 rounded bg-gray-100"></div>
                  <div class="mt-3 border-t pt-2">
                    <div class="mb-1 h-2 w-full rounded bg-indigo-100"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded bg-indigo-200"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 1 - Classic') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Traditional professional layout') }}</p>
            </div>

            <!-- Style 2 - Modern -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 flex h-10 items-center justify-between rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 px-2">
                    <div class="h-4 w-12 rounded bg-white/30"></div>
                    <div class="h-3 w-10 rounded bg-white/20"></div>
                  </div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mt-3">
                    <div class="mb-1 h-2 w-full rounded bg-indigo-50"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded bg-indigo-400"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 2 - Modern') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Clean gradient header design') }}</p>
            </div>

            <!-- Style 3 - Colorful Header -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 h-12 rounded-lg bg-gradient-to-r from-blue-600 to-cyan-500"></div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mt-3 border-t pt-2">
                    <div class="mb-1 h-2 w-full rounded bg-blue-100"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded bg-blue-300"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 3 - Colorful Header') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Bold colorful top banner') }}</p>
            </div>

            <!-- Style 4 - Bordered -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 rounded border-2 border-gray-300 p-2">
                    <div class="mb-1 h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-2 w-16 rounded bg-gray-100"></div>
                  </div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mt-3 rounded border border-gray-200 p-1">
                    <div class="mb-1 h-2 w-full rounded bg-gray-100"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded border border-gray-300 bg-gray-50"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 4 - Bordered') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Clean bordered sections') }}</p>
            </div>

            <!-- Style 5 - Info Banner -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 flex gap-1">
                    <div class="h-8 flex-1 rounded bg-indigo-100"></div>
                    <div class="h-8 flex-1 rounded bg-green-100"></div>
                    <div class="h-8 flex-1 rounded bg-blue-100"></div>
                  </div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mt-3">
                    <div class="mb-1 h-2 w-full rounded bg-gray-100"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded bg-indigo-200"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 5 - Info Banner') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Colorful info cards at top') }}</p>
            </div>

            <!-- Style 6 - Minimal -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-3 text-center">
                    <div class="mx-auto mb-1 h-3 w-24 rounded bg-gray-200"></div>
                    <div class="mx-auto h-2 w-16 rounded bg-gray-100"></div>
                  </div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-2 w-16 rounded bg-gray-100"></div>
                    <div class="h-2 w-14 rounded bg-gray-100"></div>
                  </div>
                  <div class="mt-3">
                    <div class="mb-1 h-2 w-full rounded bg-gray-50"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-2 w-14 rounded bg-gray-200"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 6 - Minimal') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Simple and clean design') }}</p>
            </div>

            <!-- Style 7 - Split Header -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 flex gap-1">
                    <div class="h-10 flex-1 rounded-l bg-indigo-600"></div>
                    <div class="flex h-10 flex-1 items-center justify-center rounded-r bg-gray-100">
                      <div class="h-3 w-12 rounded bg-gray-300"></div>
                    </div>
                  </div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mt-3">
                    <div class="mb-1 h-2 w-full rounded bg-indigo-50"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                    <div class="mb-1 h-2 w-full rounded bg-white"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded bg-indigo-300"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 7 - Split Header') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Two-tone header layout') }}</p>
            </div>

            <!-- Style 8 - Elegant -->
            <div class="group cursor-pointer rounded-lg border-2 border-gray-200 p-3 transition hover:border-indigo-500 hover:shadow-lg">
              <div class="mb-3 aspect-[3/4] overflow-hidden rounded bg-white shadow-sm">
                <div class="h-full w-full p-2">
                  <div class="mb-2 border-b-2 border-gray-800 pb-2">
                    <div class="mb-1 h-4 w-20 rounded bg-gray-800"></div>
                    <div class="h-2 w-32 rounded bg-gray-300"></div>
                  </div>
                  <div class="mb-2 flex justify-between">
                    <div class="h-3 w-20 rounded bg-gray-200"></div>
                    <div class="h-3 w-16 rounded bg-gray-200"></div>
                  </div>
                  <div class="mt-3">
                    <div class="mb-1 h-2 w-full rounded bg-gray-800"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-100"></div>
                    <div class="mb-1 h-2 w-full rounded bg-gray-100"></div>
                  </div>
                  <div class="mt-2 flex justify-end">
                    <div class="h-3 w-16 rounded bg-gray-800"></div>
                  </div>
                </div>
              </div>
              <h3 class="text-center text-sm font-medium text-gray-900">{{ __('Style 8 - Elegant') }}</h3>
              <p class="text-center text-xs text-gray-500">{{ __('Sophisticated dark accents') }}</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4">
          <TheButton variant="secondary" @click="showTemplatePreview = false">
            {{ __('Close') }}
          </TheButton>
          <TheButton @click="showTemplatePreview = false; openQuotationModal()">
            {{ __('Create Quotation') }}
          </TheButton>
        </div>
      </div>
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  CheckCircleIcon, CurrencyDollarIcon, DocumentArrowDownIcon, DocumentTextIcon,
  EyeIcon, MagnifyingGlassIcon, PaperAirplaneIcon, PencilIcon, PencilSquareIcon,
  PlusIcon, SwatchIcon, TrashIcon
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
const showTemplatePreview = ref(false)

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
