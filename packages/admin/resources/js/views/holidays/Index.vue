<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Company Holidays') }}</h1>
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
            :placeholder="__('Search holidays...')"
            class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="index.params.year"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option :value="currentYear - 1">{{ currentYear - 1 }}</option>
          <option :value="currentYear">{{ currentYear }}</option>
          <option :value="currentYear + 1">{{ currentYear + 1 }}</option>
        </select>

        <select
          v-model="index.params.filter"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All') }}</option>
          <option value="upcoming">{{ __('Upcoming') }}</option>
          <option value="past">{{ __('Past') }}</option>
        </select>
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton
          v-if="can('holiday:create')"
          size="sm"
          @click="openHolidayModal()"
        >
          {{ __('Add Holiday') }}
        </TheButton>
      </div>
    </div>

    <!-- Holidays Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="holiday"
                      :index="index"
                      :label="__('Name')"
                      sort="name"
                    />
                    <TableTh
                      name="holiday"
                      :index="index"
                      :label="__('Date')"
                      sort="date"
                    />
                    <TableTh
                      name="holiday"
                      :index="index"
                      :label="__('Day')"
                    />
                    <TableTh
                      name="holiday"
                      :index="index"
                      :label="__('Description')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="holiday in index.data.data"
                    :key="holiday.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                      {{ holiday.name }}
                      <span v-if="holiday.is_recurring" class="ml-2 inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800">
                        {{ __('Recurring') }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(holiday.date).toLocaleDateString() }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ new Date(holiday.date).toLocaleDateString('en-US', { weekday: 'long' }) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                      {{ holiday.description || '-' }}
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <PencilIcon
                        v-if="can('holiday:update')"
                        class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="openHolidayModal(holiday)"
                      />
                      <TrashIcon
                        v-if="can('holiday:delete')"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click="index.deleteIt(holiday.id)"
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

    <!-- Holiday Form Modal -->
    <FormModal v-if="form.show" size="md" @saved="index.get()">
      <Form :model-value="form.model" @close="form.show = false" />
    </FormModal>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { MagnifyingGlassIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import Form from './Form.vue'
import { useIndex } from '@/composables/useIndex'
import { can } from '@/helpers'

const processing = ref(true)
const currentYear = new Date().getFullYear()

const index = useIndex('holidays', {
  search: '',
  year: currentYear,
  filter: '',
  sort_by: 'date',
  sort_order: 'asc',
  per_page: 50
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

const openHolidayModal = (holiday = null) => {
  form.model = holiday
  form.show = true
}

onMounted(async () => {
  await index.get()
  processing.value = false
})
</script>
