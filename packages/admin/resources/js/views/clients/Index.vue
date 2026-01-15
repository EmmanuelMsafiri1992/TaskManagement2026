<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Client Management') }}</h1>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Clients') }}</dt>
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
              <CheckCircleIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Active') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.active }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Prospects') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.prospect }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Inactive') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.inactive }}</dd>
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
            :placeholder="__('Search clients...')"
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
          <option value="active">{{ __('Active') }}</option>
          <option value="inactive">{{ __('Inactive') }}</option>
          <option value="prospect">{{ __('Prospect') }}</option>
        </select>
      </div>

      <div class="flex gap-2">
        <TheButton @click="openClientModal()">
          <PlusIcon class="mr-2 h-4 w-4" />
          {{ __('Add Client') }}
        </TheButton>
      </div>
    </div>

    <!-- Clients Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh name="client" :index="index" :label="__('Name')" sort="name" />
                    <TableTh name="client" :index="index" :label="__('Company')" sort="company_name" />
                    <TableTh name="client" :index="index" :label="__('Contact')" />
                    <TableTh name="client" :index="index" :label="__('Location')" />
                    <TableTh name="client" :index="index" :label="__('Status')" sort="status" />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-if="!index.data?.data?.length" class="hover:bg-gray-50">
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                      {{ __('No clients found') }}
                    </td>
                  </tr>
                  <tr v-for="client in (index.data?.data || [])" :key="client.id" class="hover:bg-gray-50">
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="flex items-center">
                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100">
                          <span class="text-sm font-medium text-indigo-600">
                            {{ client.name.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                        <div class="ml-4">
                          <div class="text-sm font-medium text-gray-900">{{ client.name }}</div>
                          <div v-if="client.email" class="text-sm text-gray-500">{{ client.email }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <div v-if="client.company_name">{{ client.company_name }}</div>
                      <div v-if="client.business_type" class="text-xs text-gray-400">{{ client.business_type }}</div>
                      <span v-if="!client.company_name">-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <div v-if="client.phone">{{ client.phone }}</div>
                      <div v-if="client.secondary_phone" class="text-xs text-gray-400">{{ client.secondary_phone }}</div>
                      <span v-if="!client.phone">-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <div v-if="client.city || client.country">
                        {{ [client.city, client.country].filter(Boolean).join(', ') }}
                      </div>
                      <span v-else>-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-green-100 text-green-800': client.status === 'active',
                          'bg-gray-100 text-gray-800': client.status === 'inactive',
                          'bg-yellow-100 text-yellow-800': client.status === 'prospect'
                        }"
                      >
                        {{ client.status.charAt(0).toUpperCase() + client.status.slice(1) }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                      <div class="flex items-center justify-end gap-2">
                        <EyeIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="viewClient(client.id)"
                        />
                        <PencilIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="openClientModal(client)"
                        />
                        <TrashIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          @click="index.deleteIt(client.id)"
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

    <!-- Client Form Modal -->
    <FormModal v-if="form.show" size="2xl" @saved="handleSaved">
      <FormClient :model-value="form.model" @close="form.show = false" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  CheckCircleIcon, ClockIcon, EyeIcon, MagnifyingGlassIcon,
  PencilIcon, PlusIcon, TrashIcon, UsersIcon, XCircleIcon
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import FormClient from './Form.vue'
import { useIndex } from '@/composables/useIndex'

const router = useRouter()
const processing = ref(true)
const statistics = ref(null)

const index = useIndex('clients', {
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

const openClientModal = (client = null) => {
  form.model = client
  form.show = true
}

const viewClient = (id) => {
  router.push(`/clients/${id}`)
}

const handleSaved = () => {
  index.get()
  loadStatistics()
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('clients/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

onMounted(async () => {
  console.log('[Clients] Component mounted')
  try {
    console.log('[Clients] Fetching clients...')
    await index.get()
    console.log('[Clients] Clients fetched:', index.data)

    console.log('[Clients] Fetching statistics...')
    await loadStatistics()
    console.log('[Clients] Statistics fetched:', statistics.value)

    processing.value = false
    console.log('[Clients] Processing complete')
  } catch (error) {
    console.error('[Clients] ERROR in onMounted:', error)
    processing.value = false
  }
})
</script>
