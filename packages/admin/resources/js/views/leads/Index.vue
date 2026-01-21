<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Lead Management') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Track and manage potential clients from emphxs.com') }}</p>
      </div>
      <div class="flex gap-2">
        <TheButton variant="secondary" @click="exportLeads">
          <ArrowDownTrayIcon class="mr-2 h-4 w-4" />
          {{ __('Export') }}
        </TheButton>
        <TheButton @click="openLeadModal()">
          <PlusIcon class="mr-2 h-4 w-4" />
          {{ __('Add Lead') }}
        </TheButton>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
      <div class="overflow-hidden rounded-lg bg-white p-4 shadow">
        <div class="text-sm font-medium text-gray-500">{{ __('Total') }}</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ statistics.total }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-blue-50 p-4 shadow">
        <div class="text-sm font-medium text-blue-600">{{ __('New') }}</div>
        <div class="mt-1 text-2xl font-semibold text-blue-700">{{ statistics.new }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-yellow-50 p-4 shadow">
        <div class="text-sm font-medium text-yellow-600">{{ __('Contacted') }}</div>
        <div class="mt-1 text-2xl font-semibold text-yellow-700">{{ statistics.contacted }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-purple-50 p-4 shadow">
        <div class="text-sm font-medium text-purple-600">{{ __('Qualified') }}</div>
        <div class="mt-1 text-2xl font-semibold text-purple-700">{{ statistics.qualified }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-indigo-50 p-4 shadow">
        <div class="text-sm font-medium text-indigo-600">{{ __('Proposal') }}</div>
        <div class="mt-1 text-2xl font-semibold text-indigo-700">{{ statistics.proposal_sent }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-orange-50 p-4 shadow">
        <div class="text-sm font-medium text-orange-600">{{ __('Negotiation') }}</div>
        <div class="mt-1 text-2xl font-semibold text-orange-700">{{ statistics.negotiation }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-green-50 p-4 shadow">
        <div class="text-sm font-medium text-green-600">{{ __('Won') }}</div>
        <div class="mt-1 text-2xl font-semibold text-green-700">{{ statistics.won }}</div>
      </div>
      <div class="overflow-hidden rounded-lg bg-red-50 p-4 shadow">
        <div class="text-sm font-medium text-red-600">{{ __('Lost') }}</div>
        <div class="mt-1 text-2xl font-semibold text-red-700">{{ statistics.lost }}</div>
      </div>
    </div>

    <!-- Hot Leads & Conversion Stats -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-red-500 to-orange-500 p-4 text-white shadow">
        <div class="flex items-center">
          <FireIcon class="h-8 w-8" />
          <div class="ml-3">
            <div class="text-sm font-medium opacity-90">{{ __('Hot Leads') }}</div>
            <div class="text-2xl font-bold">{{ statistics.hot }}</div>
          </div>
        </div>
      </div>
      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-500 to-indigo-500 p-4 text-white shadow">
        <div class="flex items-center">
          <ClockIcon class="h-8 w-8" />
          <div class="ml-3">
            <div class="text-sm font-medium opacity-90">{{ __('Needs Follow-up') }}</div>
            <div class="text-2xl font-bold">{{ statistics.needs_follow_up }}</div>
          </div>
        </div>
      </div>
      <div class="overflow-hidden rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 p-4 text-white shadow">
        <div class="flex items-center">
          <ChartBarIcon class="h-8 w-8" />
          <div class="ml-3">
            <div class="text-sm font-medium opacity-90">{{ __('Conversion Rate') }}</div>
            <div class="text-2xl font-bold">{{ statistics.conversion_rate }}%</div>
          </div>
        </div>
      </div>
    </div>

    <!-- View Toggle & Filters -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 flex-wrap gap-2">
        <div class="relative w-64 rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 flex items-center ltr:left-0 ltr:pl-3 rtl:right-0 rtl:pr-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="index.params.search"
            type="search"
            :placeholder="__('Search leads...')"
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
          <option value="new">{{ __('New') }}</option>
          <option value="contacted">{{ __('Contacted') }}</option>
          <option value="qualified">{{ __('Qualified') }}</option>
          <option value="proposal_sent">{{ __('Proposal Sent') }}</option>
          <option value="negotiation">{{ __('Negotiation') }}</option>
          <option value="won">{{ __('Won') }}</option>
          <option value="lost">{{ __('Lost') }}</option>
        </select>

        <select
          v-model="index.params.priority"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Priorities') }}</option>
          <option value="hot">{{ __('Hot') }}</option>
          <option value="warm">{{ __('Warm') }}</option>
          <option value="cold">{{ __('Cold') }}</option>
        </select>

        <select
          v-model="index.params.source"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Sources') }}</option>
          <option v-for="(label, key) in options?.sources" :key="key" :value="key">{{ label }}</option>
        </select>
      </div>

      <div class="flex gap-2">
        <button
          :class="[viewMode === 'table' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700']"
          class="rounded-md p-2"
          @click="viewMode = 'table'"
        >
          <TableCellsIcon class="h-5 w-5" />
        </button>
        <button
          :class="[viewMode === 'pipeline' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700']"
          class="rounded-md p-2"
          @click="viewMode = 'pipeline'; loadPipeline()"
        >
          <ViewColumnsIcon class="h-5 w-5" />
        </button>
      </div>
    </div>

    <!-- Table View -->
    <section v-if="viewMode === 'table'">
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh name="lead" :index="index" :label="__('Lead')" sort="first_name" />
                    <TableTh name="lead" :index="index" :label="__('Service')" sort="service_interest" />
                    <TableTh name="lead" :index="index" :label="__('Budget')" />
                    <TableTh name="lead" :index="index" :label="__('Status')" sort="status" />
                    <TableTh name="lead" :index="index" :label="__('Priority')" sort="priority" />
                    <TableTh name="lead" :index="index" :label="__('Score')" sort="score" />
                    <TableTh name="lead" :index="index" :label="__('Source')" sort="source" />
                    <TableTh name="lead" :index="index" :label="__('Created')" sort="created_at" />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-if="!index.data?.data?.length" class="hover:bg-gray-50">
                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                      {{ __('No leads found') }}
                    </td>
                  </tr>
                  <tr v-for="lead in (index.data?.data || [])" :key="lead.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full" :class="getPriorityBgColor(lead.priority)">
                          <span class="text-sm font-medium" :class="getPriorityTextColor(lead.priority)">
                            {{ lead.first_name.charAt(0).toUpperCase() }}{{ lead.last_name ? lead.last_name.charAt(0).toUpperCase() : '' }}
                          </span>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ lead.full_name }}</div>
                          <div class="text-sm text-gray-500">{{ lead.email }}</div>
                          <div v-if="lead.company_name" class="text-xs text-gray-400">{{ lead.company_name }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ options?.service_interests?.[lead.service_interest] || lead.service_interest }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ options?.budget_ranges?.[lead.budget_range] || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <span :class="getStatusClasses(lead.status)" class="inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                        {{ lead.status_label }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <span :class="getPriorityClasses(lead.priority)" class="inline-flex items-center rounded-full px-2 text-xs font-semibold leading-5">
                        <FireIcon v-if="lead.priority === 'hot'" class="mr-1 h-3 w-3" />
                        {{ lead.priority.charAt(0).toUpperCase() + lead.priority.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <div class="flex items-center">
                        <div class="h-2 w-16 rounded-full bg-gray-200">
                          <div :style="{ width: lead.score + '%' }" class="h-2 rounded-full bg-indigo-600"></div>
                        </div>
                        <span class="ml-2 text-gray-600">{{ lead.score }}</span>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ options?.sources?.[lead.source] || lead.source }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ formatDate(lead.created_at) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <div class="flex items-center justify-end gap-2">
                        <EyeIcon class="w-5 cursor-pointer text-gray-400 hover:text-gray-800" @click="viewLead(lead.id)" />
                        <PencilIcon class="w-5 cursor-pointer text-gray-400 hover:text-gray-800" @click="openLeadModal(lead)" />
                        <ArrowPathIcon v-if="lead.status !== 'won'" class="w-5 cursor-pointer text-gray-400 hover:text-green-600" title="Convert to Client" @click="convertLead(lead.id)" />
                        <TrashIcon class="w-5 cursor-pointer text-gray-400 hover:text-red-600" @click="index.deleteIt(lead.id)" />
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

    <!-- Pipeline View (Kanban) -->
    <section v-if="viewMode === 'pipeline'" class="overflow-x-auto">
      <div class="flex min-w-max gap-4 pb-4">
        <div v-for="(leads, status) in pipeline" :key="status" class="w-72 flex-shrink-0">
          <div class="rounded-lg bg-gray-100 p-3">
            <div class="mb-3 flex items-center justify-between">
              <h3 class="font-semibold text-gray-700">{{ getStatusLabel(status) }}</h3>
              <span class="rounded-full bg-gray-200 px-2 py-1 text-xs font-medium text-gray-600">
                {{ leads.length }}
              </span>
            </div>
            <div class="space-y-2">
              <div
                v-for="lead in leads"
                :key="lead.id"
                class="cursor-pointer rounded-lg bg-white p-3 shadow hover:shadow-md"
                @click="viewLead(lead.id)"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ lead.full_name }}</div>
                    <div class="text-sm text-gray-500">{{ lead.company_name || lead.email }}</div>
                  </div>
                  <span :class="getPriorityClasses(lead.priority)" class="rounded px-1.5 py-0.5 text-xs font-medium">
                    {{ lead.priority.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-400">
                  <span>{{ options?.service_interests?.[lead.service_interest] }}</span>
                  <span>Score: {{ lead.score }}</span>
                </div>
              </div>
              <div v-if="!leads.length" class="py-4 text-center text-sm text-gray-400">
                {{ __('No leads') }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lead Form Modal -->
    <FormModal v-if="form.show" size="3xl" @saved="handleSaved">
      <FormLead :model-value="form.model" :options="options" @close="form.show = false" />
    </FormModal>

    <!-- Lead Detail Modal -->
    <FormModal v-if="detail.show" size="4xl">
      <DetailLead :lead-id="detail.id" @close="detail.show = false" @updated="handleSaved" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  ArrowDownTrayIcon, ArrowPathIcon, ChartBarIcon, ClockIcon,
  EyeIcon, FireIcon, MagnifyingGlassIcon, PencilIcon, PlusIcon,
  TableCellsIcon, TrashIcon, ViewColumnsIcon
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import FormLead from './Form.vue'
import DetailLead from './Detail.vue'
import { useIndex } from '@/composables/useIndex'

const router = useRouter()
const processing = ref(true)
const statistics = ref(null)
const options = ref(null)
const viewMode = ref('table')
const pipeline = ref({})

const index = useIndex('leads', {
  search: '',
  status: '',
  priority: '',
  source: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 15
})

const form = reactive({
  show: false,
  model: null
})

const detail = reactive({
  show: false,
  id: null
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const openLeadModal = (lead = null) => {
  form.model = lead
  form.show = true
}

const viewLead = (id) => {
  detail.id = id
  detail.show = true
}

const handleSaved = () => {
  index.get()
  loadStatistics()
  if (viewMode.value === 'pipeline') {
    loadPipeline()
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('leads/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadOptions = async () => {
  try {
    const response = await axios.get('leads/options')
    options.value = response.data.data
  } catch (error) {
    console.error('Failed to load options:', error)
  }
}

const loadPipeline = async () => {
  try {
    const response = await axios.get('leads/pipeline')
    pipeline.value = response.data.data
  } catch (error) {
    console.error('Failed to load pipeline:', error)
  }
}

const exportLeads = async () => {
  try {
    const params = new URLSearchParams(index.params).toString()
    window.open(`/admin/api/leads/export?${params}`, '_blank')
  } catch (error) {
    console.error('Failed to export:', error)
  }
}

const convertLead = async (id) => {
  if (!confirm('Convert this lead to a client?')) return
  try {
    await axios.post(`leads/${id}/convert`)
    handleSaved()
  } catch (error) {
    console.error('Failed to convert lead:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const getStatusClasses = (status) => {
  const classes = {
    new: 'bg-blue-100 text-blue-800',
    contacted: 'bg-yellow-100 text-yellow-800',
    qualified: 'bg-purple-100 text-purple-800',
    proposal_sent: 'bg-indigo-100 text-indigo-800',
    negotiation: 'bg-orange-100 text-orange-800',
    won: 'bg-green-100 text-green-800',
    lost: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getPriorityClasses = (priority) => {
  const classes = {
    hot: 'bg-red-100 text-red-800',
    warm: 'bg-orange-100 text-orange-800',
    cold: 'bg-blue-100 text-blue-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getPriorityBgColor = (priority) => {
  const classes = {
    hot: 'bg-red-100',
    warm: 'bg-orange-100',
    cold: 'bg-blue-100'
  }
  return classes[priority] || 'bg-gray-100'
}

const getPriorityTextColor = (priority) => {
  const classes = {
    hot: 'text-red-600',
    warm: 'text-orange-600',
    cold: 'text-blue-600'
  }
  return classes[priority] || 'text-gray-600'
}

const getStatusLabel = (status) => {
  const labels = {
    new: 'New Leads',
    contacted: 'Contacted',
    qualified: 'Qualified',
    proposal_sent: 'Proposal Sent',
    negotiation: 'Negotiation'
  }
  return labels[status] || status
}

onMounted(async () => {
  try {
    await Promise.all([
      index.get(),
      loadStatistics(),
      loadOptions()
    ])
    processing.value = false
  } catch (error) {
    console.error('ERROR in onMounted:', error)
    processing.value = false
  }
})
</script>
