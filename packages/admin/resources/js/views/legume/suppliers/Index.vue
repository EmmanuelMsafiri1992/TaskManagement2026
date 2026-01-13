<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Statistics Cards -->
    <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Suppliers</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.total_suppliers }}</dd>
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
                <dt class="truncate text-sm font-medium text-gray-500">Active Suppliers</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ statistics.active_suppliers }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-6 w-6 text-purple-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Supplied</dt>
                <dd class="text-lg font-semibold text-gray-900">MK {{ formatNumber(statistics.total_supplied) }}</dd>
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
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Search suppliers..."
            class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @input="handleSearch"
          />
        </div>

        <select
          v-model="filterStatus"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchSuppliers"
        >
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>

        <select
          v-model="filterDistrict"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="fetchSuppliers"
        >
          <option value="">All Districts</option>
          <option v-for="district in statistics?.districts || []" :key="district" :value="district">{{ district }}</option>
        </select>
      </div>

      <div class="ml-auto">
        <button
          type="button"
          class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
          @click="openModal()"
        >
          <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
          Add Supplier
        </button>
      </div>
    </div>

    <!-- Suppliers Table -->
    <div class="overflow-hidden rounded-lg bg-white shadow">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Supplier</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Contact</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Location</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total Supplied</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Purchases</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="font-medium text-gray-900">{{ supplier.name }}</div>
              <div v-if="supplier.national_id" class="text-sm text-gray-500">ID: {{ supplier.national_id }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm text-gray-900">{{ supplier.phone }}</div>
              <div v-if="supplier.email" class="text-sm text-gray-500">{{ supplier.email }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              <div v-if="supplier.village">{{ supplier.village }}</div>
              <div v-if="supplier.district">{{ supplier.district }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
              MK {{ formatNumber(supplier.total_supplied) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
              {{ supplier.purchases_count || 0 }}
            </td>
            <td class="whitespace-nowrap px-6 py-4">
              <span :class="supplier.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">
                {{ supplier.status }}
              </span>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
              <router-link :to="`/legume/suppliers/${supplier.id}`" class="text-indigo-600 hover:text-indigo-900 mr-3">View</router-link>
              <button @click="openModal(supplier)" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
              <button @click="confirmDelete(supplier)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
          <tr v-if="suppliers.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No suppliers found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Supplier Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="closeModal"></div>

        <div class="relative w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
          <h3 class="mb-4 text-lg font-medium text-gray-900">
            {{ editingSupplier ? 'Edit Supplier' : 'Add Supplier' }}
          </h3>

          <form @submit.prevent="saveSupplier">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Phone</label>
                  <input v-model="form.phone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Secondary Phone</label>
                  <input v-model="form.secondary_phone" type="tel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Email</label>
                  <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">National ID</label>
                  <input v-model="form.national_id" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">District</label>
                  <input v-model="form.district" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="e.g., Lilongwe" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Village</label>
                  <input v-model="form.village" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea v-model="form.address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button type="button" @click="closeModal" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Loader from '@/components/Loader.vue'
import {
  UsersIcon,
  CheckCircleIcon,
  BanknotesIcon,
  MagnifyingGlassIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const suppliers = ref([])
const statistics = ref(null)
const searchQuery = ref('')
const filterStatus = ref('')
const filterDistrict = ref('')
const showModal = ref(false)
const editingSupplier = ref(null)
const saving = ref(false)

const form = ref({
  name: '',
  phone: '',
  secondary_phone: '',
  email: '',
  national_id: '',
  address: '',
  district: '',
  village: '',
  notes: '',
  status: 'active'
})

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchSuppliers(), 300)
}

const fetchSuppliers = async () => {
  try {
    const params = {}
    if (searchQuery.value) params.search = searchQuery.value
    if (filterStatus.value) params.status = filterStatus.value
    if (filterDistrict.value) params.district = filterDistrict.value

    const [suppliersRes, statsRes] = await Promise.all([
      axios.get('/api/legume/suppliers', { params }),
      axios.get('/api/legume/suppliers/statistics')
    ])

    suppliers.value = suppliersRes.data.data
    statistics.value = statsRes.data.data
  } catch (error) {
    console.error('Error fetching suppliers:', error)
  } finally {
    processing.value = false
  }
}

const openModal = (supplier = null) => {
  editingSupplier.value = supplier
  if (supplier) {
    form.value = { ...supplier }
  } else {
    form.value = {
      name: '',
      phone: '',
      secondary_phone: '',
      email: '',
      national_id: '',
      address: '',
      district: '',
      village: '',
      notes: '',
      status: 'active'
    }
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingSupplier.value = null
}

const saveSupplier = async () => {
  saving.value = true
  try {
    if (editingSupplier.value) {
      await axios.put(`/api/legume/suppliers/${editingSupplier.value.id}`, form.value)
    } else {
      await axios.post('/api/legume/suppliers', form.value)
    }
    closeModal()
    fetchSuppliers()
  } catch (error) {
    console.error('Error saving supplier:', error)
    alert(error.response?.data?.message || 'Error saving supplier')
  } finally {
    saving.value = false
  }
}

const confirmDelete = async (supplier) => {
  if (confirm(`Are you sure you want to delete "${supplier.name}"?`)) {
    try {
      await axios.delete(`/api/legume/suppliers/${supplier.id}`)
      fetchSuppliers()
    } catch (error) {
      alert(error.response?.data?.message || 'Error deleting supplier')
    }
  }
}

onMounted(() => fetchSuppliers())
</script>
