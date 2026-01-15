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
              <ClipboardDocumentListIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Total Activities') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.total }}
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
              <CalendarIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Today') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.today }}
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
              <CalendarDaysIcon class="h-6 w-6 text-yellow-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('This Week') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.this_week }}
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
              <ChartBarIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('This Month') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.this_month }}
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
            :placeholder="__('Search changes...')"
            class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="index.params.event"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Events') }}</option>
          <option value="created">{{ __('Created') }}</option>
          <option value="updated">{{ __('Updated') }}</option>
          <option value="deleted">{{ __('Deleted') }}</option>
        </select>

        <select
          v-model="index.params.model_type"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Models') }}</option>
          <option v-for="model in modelTypes" :key="model.value" :value="model.label">
            {{ model.label }}
          </option>
        </select>

        <select
          v-model="index.params.user_id"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Users') }}</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
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
    </div>

    <!-- Audit Trail Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="audit"
                      :index="index"
                      :label="__('Date/Time')"
                      sort="created_at"
                    />
                    <TableTh
                      name="audit"
                      :index="index"
                      :label="__('User')"
                    />
                    <TableTh
                      name="audit"
                      :index="index"
                      :label="__('Event')"
                      sort="event"
                    />
                    <TableTh
                      name="audit"
                      :index="index"
                      :label="__('Model')"
                    />
                    <TableTh
                      name="audit"
                      :index="index"
                      :label="__('Changes')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="audit in index.data.data"
                    :key="audit.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                      {{ formatDateTime(audit.created_at) }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                      <div v-if="audit.user" class="flex items-center">
                        <div class="h-8 w-8 flex-shrink-0">
                          <UserAvatar :user="audit.user" size="8" />
                        </div>
                        <div class="ml-3">
                          <div class="text-sm font-medium text-gray-900">
                            {{ audit.user.name }}
                          </div>
                        </div>
                      </div>
                      <span v-else class="text-sm text-gray-400">System</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="getEventClass(audit.event)"
                      >
                        {{ audit.event.charAt(0).toUpperCase() + audit.event.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span class="inline-flex rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                        {{ getModelName(audit.auditable_type) }}
                      </span>
                      <span class="ml-1 text-gray-400">#{{ audit.auditable_id }}</span>
                    </td>
                    <td class="max-w-xs px-6 py-4 text-sm text-gray-500">
                      <div v-if="audit.event === 'created'" class="text-green-600">
                        {{ getChangeSummary(audit.new_values, 'created') }}
                      </div>
                      <div v-else-if="audit.event === 'deleted'" class="text-red-600">
                        {{ getChangeSummary(audit.old_values, 'deleted') }}
                      </div>
                      <div v-else>
                        {{ getChangeSummary(audit.new_values, 'updated') }}
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <EyeIcon
                        class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="viewAuditDetails(audit)"
                      />
                    </td>
                  </tr>
                  <tr v-if="!index.data.data || index.data.data.length === 0">
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                      {{ __('No audit trail records found') }}
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

    <!-- Audit Detail Modal -->
    <ModalBase v-if="detailModal.show" width="max-w-3xl">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ __('Audit Trail Details') }}</h3>
          <button @click="detailModal.show = false" class="text-gray-400 hover:text-gray-600">
            <XCircleIcon class="h-6 w-6" />
          </button>
        </div>

        <div v-if="detailModal.audit" class="space-y-4">
          <!-- Basic Info -->
          <div class="grid grid-cols-2 gap-4 border-b pb-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Date/Time') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(detailModal.audit.created_at) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('User') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailModal.audit.user?.name || 'System' }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Event') }}</label>
              <p class="mt-1">
                <span
                  class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                  :class="getEventClass(detailModal.audit.event)"
                >
                  {{ detailModal.audit.event.charAt(0).toUpperCase() + detailModal.audit.event.slice(1) }}
                </span>
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ __('Model') }}</label>
              <p class="mt-1 text-sm text-gray-900">
                {{ getModelName(detailModal.audit.auditable_type) }} #{{ detailModal.audit.auditable_id }}
              </p>
            </div>
            <div v-if="detailModal.audit.ip_address">
              <label class="block text-sm font-medium text-gray-700">{{ __('IP Address') }}</label>
              <p class="mt-1 text-sm text-gray-900">{{ detailModal.audit.ip_address }}</p>
            </div>
            <div v-if="detailModal.audit.url">
              <label class="block text-sm font-medium text-gray-700">{{ __('URL') }}</label>
              <p class="mt-1 text-sm text-gray-900 truncate" :title="detailModal.audit.url">
                {{ detailModal.audit.url }}
              </p>
            </div>
          </div>

          <!-- Changes Comparison -->
          <div v-if="detailModal.audit.event === 'updated'" class="space-y-4">
            <h4 class="text-sm font-semibold text-gray-700">{{ __('Changes') }}</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-red-600 mb-2">{{ __('Old Values') }}</label>
                <div class="bg-red-50 rounded-md p-3 max-h-64 overflow-auto">
                  <pre class="text-xs text-gray-800 whitespace-pre-wrap">{{ formatJson(detailModal.audit.old_values) }}</pre>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-green-600 mb-2">{{ __('New Values') }}</label>
                <div class="bg-green-50 rounded-md p-3 max-h-64 overflow-auto">
                  <pre class="text-xs text-gray-800 whitespace-pre-wrap">{{ formatJson(detailModal.audit.new_values) }}</pre>
                </div>
              </div>
            </div>
          </div>

          <!-- Created/Deleted Values -->
          <div v-else class="space-y-2">
            <h4 class="text-sm font-semibold text-gray-700">
              {{ detailModal.audit.event === 'created' ? __('Created Values') : __('Deleted Values') }}
            </h4>
            <div
              class="rounded-md p-3 max-h-64 overflow-auto"
              :class="detailModal.audit.event === 'created' ? 'bg-green-50' : 'bg-red-50'"
            >
              <pre class="text-xs text-gray-800 whitespace-pre-wrap">{{
                formatJson(detailModal.audit.event === 'created' ? detailModal.audit.new_values : detailModal.audit.old_values)
              }}</pre>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <TheButton @click="detailModal.show = false">
            {{ __('Close') }}
          </TheButton>
        </div>
      </div>
    </ModalBase>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import {
  CalendarIcon,
  CalendarDaysIcon,
  ChartBarIcon,
  ClipboardDocumentListIcon,
  EyeIcon,
  MagnifyingGlassIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import ModalBase from '@/thetheme/components/ModalBase.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import { useIndex } from '@/composables/useIndex'

const processing = ref(true)
const statistics = ref(null)
const modelTypes = ref([])
const users = ref([])

const index = useIndex('audit-trails', {
  search: '',
  event: '',
  model_type: '',
  user_id: '',
  start_date: '',
  end_date: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 20
})

const detailModal = reactive({
  show: false,
  audit: null
})

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const viewAuditDetails = (audit) => {
  detailModal.audit = audit
  detailModal.show = true
}

const getEventClass = (event) => {
  const classes = {
    created: 'bg-green-100 text-green-800',
    updated: 'bg-blue-100 text-blue-800',
    deleted: 'bg-red-100 text-red-800',
    restored: 'bg-yellow-100 text-yellow-800'
  }
  return classes[event] || 'bg-gray-100 text-gray-800'
}

const getModelName = (auditableType) => {
  if (!auditableType) return 'Unknown'
  const parts = auditableType.split('\\')
  return parts[parts.length - 1]
}

const getChangeSummary = (values, type) => {
  if (!values) return '-'
  const keys = Object.keys(values)
  if (keys.length === 0) return '-'

  if (type === 'created') {
    return `Created with ${keys.length} field(s)`
  } else if (type === 'deleted') {
    return `Deleted record`
  } else {
    if (keys.length <= 2) {
      return keys.join(', ') + ' changed'
    }
    return `${keys.length} field(s) changed`
  }
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString()
}

const formatJson = (obj) => {
  if (!obj) return '-'
  try {
    return JSON.stringify(obj, null, 2)
  } catch (e) {
    return String(obj)
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('audit-trails/statistics')
    statistics.value = response.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

const loadModelTypes = async () => {
  try {
    const response = await axios.get('audit-trails/model-types')
    modelTypes.value = response.data
  } catch (error) {
    console.error('Failed to load model types:', error)
  }
}

const loadUsers = async () => {
  try {
    const response = await axios.get('users')
    users.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

onMounted(async () => {
  await Promise.all([
    index.get(),
    loadStatistics(),
    loadModelTypes(),
    loadUsers()
  ])

  processing.value = false
})
</script>
