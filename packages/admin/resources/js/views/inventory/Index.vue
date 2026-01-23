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
              <CubeIcon class="h-6 w-6 text-gray-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Items') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.total_items }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Available') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.available }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UserIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('In Use') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.in_use }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Value') }}</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ formatCurrency(statistics.total_value) }}</dd>
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
            :placeholder="__('Search inventory...')"
            class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="index.params.category"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Categories') }}</option>
          <option v-for="cat in filters.categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <select
          v-model="index.params.status"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Statuses') }}</option>
          <option v-for="status in filters.statuses" :key="status" :value="status">
            {{ status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ') }}
          </option>
        </select>

        <select
          v-model="index.params.condition"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Conditions') }}</option>
          <option v-for="cond in filters.conditions" :key="cond" :value="cond">
            {{ cond.charAt(0).toUpperCase() + cond.slice(1) }}
          </option>
        </select>
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton size="sm" @click="openModal()">
          {{ __('Add Item') }}
        </TheButton>
      </div>
    </div>

    <!-- Inventory Table -->
    <section>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh name="inventory" :index="index" :label="__('Item')" sort="item_name" />
                    <TableTh name="inventory" :index="index" :label="__('Category')" sort="category" />
                    <TableTh name="inventory" :index="index" :label="__('Qty')" sort="quantity" />
                    <TableTh name="inventory" :index="index" :label="__('Condition')" sort="condition" />
                    <TableTh name="inventory" :index="index" :label="__('Status')" sort="status" />
                    <TableTh name="inventory" :index="index" :label="__('Assigned To')" />
                    <TableTh name="inventory" :index="index" :label="__('Location')" sort="location" />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="item in index.data.data"
                    :key="item.id"
                    class="cursor-pointer hover:bg-gray-50"
                    @click="selectItem(item)"
                  >
                    <td class="whitespace-nowrap px-6 py-4">
                      <div class="text-sm font-medium text-gray-900">{{ item.item_name }}</div>
                      <div class="text-sm text-gray-500">{{ item.item_code }}</div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ item.category || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 font-medium">
                      {{ item.quantity }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-green-100 text-green-800': item.condition === 'excellent',
                          'bg-blue-100 text-blue-800': item.condition === 'good',
                          'bg-yellow-100 text-yellow-800': item.condition === 'fair',
                          'bg-orange-100 text-orange-800': item.condition === 'poor',
                          'bg-red-100 text-red-800': item.condition === 'damaged',
                        }"
                      >
                        {{ item.condition }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-green-100 text-green-800': item.status === 'available',
                          'bg-blue-100 text-blue-800': item.status === 'in_use',
                          'bg-yellow-100 text-yellow-800': item.status === 'maintenance',
                          'bg-gray-100 text-gray-800': item.status === 'retired',
                        }"
                      >
                        {{ item.status.replace('_', ' ') }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <div v-if="item.assigned_user" class="flex items-center">
                        <UserAvatar :user="item.assigned_user" size="6" />
                        <span class="ml-2">{{ item.assigned_user.name }}</span>
                      </div>
                      <span v-else>-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ item.location || '-' }}
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <PencilIcon
                        class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.stop="openModal(item)"
                      />
                      <TrashIcon
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-red-600"
                        @click.stop="deleteItem(item)"
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

    <!-- Item Detail Slide-out -->
    <transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="selectedItem"
        class="fixed right-0 top-0 h-full w-96 bg-white shadow-2xl overflow-y-auto z-40 border-l border-gray-200"
        style="top: 64px; height: calc(100vh - 64px);"
      >
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
          <h2 class="text-lg font-semibold text-gray-900">{{ __('Item Details') }}</h2>
          <button class="p-1 rounded-md hover:bg-gray-100 text-gray-400 hover:text-gray-600" @click="selectedItem = null">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="p-6 space-y-6">
          <div class="text-center pb-4 border-b border-gray-100">
            <div class="flex justify-center mb-3">
              <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                <CubeIcon class="h-8 w-8 text-indigo-600" />
              </div>
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ selectedItem.item_name }}</h3>
            <p class="text-sm text-gray-500">{{ selectedItem.item_code }}</p>
            <div class="flex justify-center gap-2 mt-3">
              <span
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-800': selectedItem.status === 'available',
                  'bg-blue-100 text-blue-800': selectedItem.status === 'in_use',
                  'bg-yellow-100 text-yellow-800': selectedItem.status === 'maintenance',
                  'bg-gray-100 text-gray-800': selectedItem.status === 'retired',
                }"
              >
                {{ selectedItem.status.replace('_', ' ') }}
              </span>
              <span
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-800': selectedItem.condition === 'excellent',
                  'bg-blue-100 text-blue-800': selectedItem.condition === 'good',
                  'bg-yellow-100 text-yellow-800': selectedItem.condition === 'fair',
                  'bg-orange-100 text-orange-800': selectedItem.condition === 'poor',
                  'bg-red-100 text-red-800': selectedItem.condition === 'damaged',
                }"
              >
                {{ selectedItem.condition }}
              </span>
            </div>
          </div>

          <div>
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">{{ __('Details') }}</h4>
            <dl class="space-y-2">
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Category') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedItem.category || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Quantity') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedItem.quantity }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Location') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedItem.location || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Assigned To') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedItem.assigned_user?.name || '-' }}</dd>
              </div>
            </dl>
          </div>

          <div v-if="selectedItem.purchase_price">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">{{ __('Purchase Info') }}</h4>
            <dl class="space-y-2">
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Purchase Price') }}</dt>
                <dd class="text-sm font-bold text-green-600">{{ formatCurrency(selectedItem.purchase_price, selectedItem.currency) }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Purchase Date') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ formatDate(selectedItem.purchase_date) }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Supplier') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedItem.supplier || '-' }}</dd>
              </div>
            </dl>
          </div>

          <div v-if="selectedItem.description || selectedItem.notes">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">{{ __('Notes') }}</h4>
            <p v-if="selectedItem.description" class="text-sm text-gray-700 mb-2">{{ selectedItem.description }}</p>
            <p v-if="selectedItem.notes" class="text-sm text-gray-500 italic">{{ selectedItem.notes }}</p>
          </div>

          <div class="pt-4 border-t border-gray-200">
            <button
              class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
              @click="openModal(selectedItem)"
            >
              <PencilIcon class="h-4 w-4" />
              {{ __('Edit Item') }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Overlay -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="selectedItem"
        class="fixed inset-0 bg-black bg-opacity-20 z-30"
        style="top: 64px;"
        @click="selectedItem = null"
      ></div>
    </transition>

    <!-- Form Modal -->
    <FormModal v-if="form.show" size="xl" @close="form.show = false">
      <Form :model-value="form.model" @close="form.show = false" @saved="onSaved" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { CheckCircleIcon, CubeIcon, CurrencyDollarIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon, UserIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Form from './Form.vue'
import { useIndex } from '@/composables/useIndex'

const processing = ref(true)
const statistics = ref(null)
const selectedItem = ref(null)
const filters = ref({
  categories: [],
  locations: [],
  statuses: [],
  conditions: [],
})

const index = useIndex('inventory', {
  search: '',
  category: '',
  status: '',
  condition: '',
  location: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  per_page: 15,
})

const form = reactive({
  show: false,
  model: null,
})

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const formatCurrency = (amount, currency = 'MWK') => {
  if (!amount) return '-'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency,
    minimumFractionDigits: 2,
  }).format(amount)
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const selectItem = (item) => {
  selectedItem.value = item
}

const openModal = (item = null) => {
  form.model = item
  form.show = true
}

const onSaved = () => {
  form.show = false
  index.get()
  loadStatistics()
}

const deleteItem = async (item) => {
  if (!confirm(`Delete "${item.item_name}"?`)) return

  try {
    await axios.delete(`inventory/${item.id}`)
    index.get()
    loadStatistics()
    if (selectedItem.value?.id === item.id) {
      selectedItem.value = null
    }
  } catch (error) {
    console.error('Failed to delete item:', error)
    alert('Failed to delete item')
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('inventory-statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

onMounted(async () => {
  try {
    await index.get()
    await loadStatistics()

    if (index.data?.filters) {
      filters.value = index.data.filters
    }

    processing.value = false
  } catch (error) {
    console.error('Error in onMounted:', error)
    processing.value = false
  }
})
</script>
