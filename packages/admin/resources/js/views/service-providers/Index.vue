<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { EllipsisVerticalIcon, PlusIcon, MagnifyingGlassIcon, EyeIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline'

interface ServiceProvider {
  id: number
  name: string
  email: string
  phone: string
  specialty: string
  qualification: string
  status: string
  agreement_signed: boolean
  hourly_rate: number | null
  total_agreed_amount: number
  total_paid: number
  balance_remaining: number
  payment_preference: string
  payment_method: string
  recording_sessions_count: number
  lesson_plans_count: number
  created_at: string
}

interface Stats {
  total: number
  active: number
  pending: number
  suspended: number
}

const router = useRouter()
const providers = ref<ServiceProvider[]>([])
const stats = ref<Stats>({ total: 0, active: 0, pending: 0, suspended: 0 })
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedProvider = ref<ServiceProvider | null>(null)
const currentStep = ref(1)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  national_id: '',
  address: '',
  specialty: '',
  qualification: '',
  bio: '',
  hourly_rate: '',
  total_agreed_amount: '700000',
  payment_preference: 'monthly',
  monthly_amount: '',
  payment_method: 'bank',
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  bank_branch: '',
  mobile_money_provider: '',
  mobile_money_number: '',
  mobile_money_name: '',
  password: ''
})

const fetchProviders = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page.toString())
    if (search.value) params.append('search', search.value)
    if (statusFilter.value) params.append('status', statusFilter.value)

    const response = await axios.get(`/api/service-providers?${params}`)
    providers.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching providers:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/service-providers/statistics')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const createProvider = async () => {
  try {
    await axios.post('/api/service-providers', formData.value)
    showCreateModal.value = false
    resetForm()
    await fetchProviders()
    await fetchStats()
  } catch (error) {
    console.error('Error creating provider:', error)
  }
}

const updateProvider = async () => {
  if (!selectedProvider.value) return
  try {
    const data = { ...formData.value }
    if (!data.password) delete (data as any).password
    await axios.put(`/api/service-providers/${selectedProvider.value.id}`, data)
    showEditModal.value = false
    resetForm()
    await fetchProviders()
  } catch (error) {
    console.error('Error updating provider:', error)
  }
}

const activateProvider = async (provider: ServiceProvider) => {
  try {
    await axios.post(`/api/service-providers/${provider.id}/activate`)
    await fetchProviders()
    await fetchStats()
  } catch (error) {
    console.error('Error activating provider:', error)
  }
}

const suspendProvider = async (provider: ServiceProvider) => {
  try {
    await axios.post(`/api/service-providers/${provider.id}/suspend`)
    await fetchProviders()
    await fetchStats()
  } catch (error) {
    console.error('Error suspending provider:', error)
  }
}

const deleteProvider = async (provider: ServiceProvider) => {
  if (!confirm('Are you sure you want to delete this service provider?')) return
  try {
    await axios.delete(`/api/service-providers/${provider.id}`)
    await fetchProviders()
    await fetchStats()
  } catch (error) {
    console.error('Error deleting provider:', error)
  }
}

const viewDetails = (provider: ServiceProvider) => {
  router.push(`/service-providers/${provider.id}`)
}

const openEditModal = (provider: ServiceProvider) => {
  selectedProvider.value = provider
  formData.value = {
    name: provider.name,
    email: provider.email,
    phone: provider.phone || '',
    national_id: (provider as any).national_id || '',
    address: (provider as any).address || '',
    specialty: provider.specialty || '',
    qualification: provider.qualification || '',
    bio: (provider as any).bio || '',
    hourly_rate: provider.hourly_rate?.toString() || '',
    total_agreed_amount: provider.total_agreed_amount?.toString() || '700000',
    payment_preference: (provider as any).payment_preference || 'monthly',
    monthly_amount: (provider as any).monthly_amount?.toString() || '',
    payment_method: (provider as any).payment_method || 'bank',
    bank_name: (provider as any).bank_name || '',
    bank_account_number: (provider as any).bank_account_number || '',
    bank_account_name: (provider as any).bank_account_name || '',
    bank_branch: (provider as any).bank_branch || '',
    mobile_money_provider: (provider as any).mobile_money_provider || '',
    mobile_money_number: (provider as any).mobile_money_number || '',
    mobile_money_name: (provider as any).mobile_money_name || '',
    password: ''
  }
  currentStep.value = 1
  showEditModal.value = true
}

const resetForm = () => {
  formData.value = {
    name: '',
    email: '',
    phone: '',
    national_id: '',
    address: '',
    specialty: '',
    qualification: '',
    bio: '',
    hourly_rate: '',
    total_agreed_amount: '700000',
    payment_preference: 'monthly',
    monthly_amount: '',
    payment_method: 'bank',
    bank_name: '',
    bank_account_number: '',
    bank_account_name: '',
    bank_branch: '',
    mobile_money_provider: '',
    mobile_money_number: '',
    mobile_money_name: '',
    password: ''
  }
  selectedProvider.value = null
  currentStep.value = 1
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800'
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'suspended': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('en-MW', { style: 'decimal' }).format(value || 0)
}

const getPaymentProgress = (provider: ServiceProvider) => {
  if (!provider.total_agreed_amount) return 0
  return Math.round((provider.total_paid / provider.total_agreed_amount) * 100)
}

onMounted(() => {
  fetchProviders()
  fetchStats()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Service Providers (Teachers)</h1>
        <p class="mt-1 text-sm text-gray-500">Manage teachers and content providers</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Add Teacher
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Total Teachers</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Active</div>
        <div class="mt-1 text-2xl font-semibold text-green-600">{{ stats.active }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Pending Approval</div>
        <div class="mt-1 text-2xl font-semibold text-yellow-600">{{ stats.pending }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Suspended</div>
        <div class="mt-1 text-2xl font-semibold text-red-600">{{ stats.suspended }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-64">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="search"
              @input="fetchProviders(1)"
              type="text"
              placeholder="Search by name, email, phone or ID..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
        </div>
        <select
          v-model="statusFilter"
          @change="fetchProviders(1)"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="pending">Pending</option>
          <option value="suspended">Suspended</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sessions</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Progress</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Loading...</td>
          </tr>
          <tr v-else-if="providers.length === 0">
            <td colspan="6" class="px-6 py-8 text-center text-gray-500">No teachers found</td>
          </tr>
          <tr v-for="provider in providers" :key="provider.id" class="hover:bg-gray-50 cursor-pointer" @click="viewDetails(provider)">
            <td class="px-6 py-4">
              <div class="font-medium text-gray-900">{{ provider.name }}</div>
              <div class="text-sm text-gray-500">{{ provider.email }}</div>
              <div class="text-sm text-gray-500">{{ provider.phone }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-gray-900">{{ provider.specialty || '-' }}</div>
              <div class="text-sm text-gray-500">{{ provider.qualification || '-' }}</div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              <div>{{ provider.recording_sessions_count || 0 }} recordings</div>
              <div>{{ provider.lesson_plans_count || 0 }} lesson plans</div>
            </td>
            <td class="px-6 py-4" @click.stop>
              <div class="w-full max-w-xs">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>MK {{ formatCurrency(provider.total_paid || 0) }}</span>
                  <span>MK {{ formatCurrency(provider.total_agreed_amount || 700000) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-indigo-600 h-2 rounded-full transition-all"
                    :style="{ width: getPaymentProgress(provider) + '%' }"
                  ></div>
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ getPaymentProgress(provider) }}% paid | Balance: MK {{ formatCurrency(provider.balance_remaining || (provider.total_agreed_amount || 700000)) }}
                </div>
              </div>
            </td>
            <td class="px-6 py-4" @click.stop>
              <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(provider.status)]">
                {{ provider.status }}
              </span>
              <div v-if="provider.agreement_signed" class="mt-1 text-xs text-green-600">Agreement Signed</div>
              <div v-else class="mt-1 text-xs text-yellow-600">Agreement Pending</div>
            </td>
            <td class="px-6 py-4 text-right" @click.stop>
              <Menu as="div" class="relative inline-block text-left">
                <MenuButton class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                  <EllipsisVerticalIcon class="w-5 h-5" />
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="viewDetails(provider)"
                        :class="[active ? 'bg-gray-100' : '', 'flex items-center w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        <EyeIcon class="w-4 h-4 mr-2" />
                        View Details
                      </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="openEditModal(provider)"
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        Edit
                      </button>
                    </MenuItem>
                    <MenuItem v-if="provider.status !== 'active'" v-slot="{ active }">
                      <button
                        @click="activateProvider(provider)"
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-green-700']"
                      >
                        Activate
                      </button>
                    </MenuItem>
                    <MenuItem v-if="provider.status === 'active'" v-slot="{ active }">
                      <button
                        @click="suspendProvider(provider)"
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-yellow-700']"
                      >
                        Suspend
                      </button>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <button
                        @click="deleteProvider(provider)"
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-red-700']"
                      >
                        Delete
                      </button>
                    </MenuItem>
                  </div>
                </MenuItems>
              </Menu>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
          Showing {{ (pagination.current_page - 1) * pagination.per_page + 1 }} to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of {{ pagination.total }}
        </div>
        <div class="flex space-x-2">
          <button
            v-for="page in pagination.last_page"
            :key="page"
            @click="fetchProviders(page)"
            :class="[
              'px-3 py-1 rounded',
              page === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showCreateModal = false; showEditModal = false; resetForm()"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ showEditModal ? 'Edit Teacher' : 'Add Teacher' }}
          </h2>

          <!-- Steps Navigation -->
          <div class="mb-6">
            <div class="flex items-center justify-between">
              <button
                @click="currentStep = 1"
                :class="['flex items-center', currentStep >= 1 ? 'text-indigo-600' : 'text-gray-400']"
              >
                <span :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mr-2', currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200']">1</span>
                <span class="hidden sm:inline">Personal Info</span>
              </button>
              <div class="flex-1 h-1 mx-4 bg-gray-200">
                <div :class="['h-1 transition-all', currentStep >= 2 ? 'bg-indigo-600' : 'bg-gray-200']" :style="{ width: currentStep >= 2 ? '100%' : '0%' }"></div>
              </div>
              <button
                @click="currentStep = 2"
                :class="['flex items-center', currentStep >= 2 ? 'text-indigo-600' : 'text-gray-400']"
              >
                <span :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mr-2', currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200']">2</span>
                <span class="hidden sm:inline">Payment Details</span>
              </button>
            </div>
          </div>

          <form @submit.prevent="showEditModal ? updateProvider() : createProvider()">
            <!-- Step 1: Personal Information -->
            <div v-show="currentStep === 1" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                  <input v-model="formData.name" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Email *</label>
                  <input v-model="formData.email" type="email" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Phone</label>
                  <input v-model="formData.phone" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">National ID</label>
                  <input v-model="formData.national_id" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <input v-model="formData.address" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Subject/Specialty</label>
                  <input v-model="formData.specialty" type="text" placeholder="e.g., Mathematics, English" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Qualification</label>
                  <input v-model="formData.qualification" type="text" placeholder="e.g., Bachelor's Degree" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Bio/About</label>
                <textarea v-model="formData.bio" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Brief description about the teacher..."></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">{{ showEditModal ? 'New Password (leave blank to keep current)' : 'Password *' }}</label>
                <input v-model="formData.password" type="password" :required="!showEditModal" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
              </div>
            </div>

            <!-- Step 2: Payment Information -->
            <div v-show="currentStep === 2" class="space-y-4">
              <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                <h3 class="font-medium text-indigo-800 mb-2">Contract Payment Details</h3>
                <p class="text-sm text-indigo-600">Configure the payment terms for this teacher's contract.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700">Total Agreed Amount (MK) *</label>
                  <input v-model="formData.total_agreed_amount" type="number" step="0.01" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                  <p class="text-xs text-gray-500 mt-1">Default: MK 700,000</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Hourly Rate (MK)</label>
                  <input v-model="formData.hourly_rate" type="number" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Preference *</label>
                <div class="grid grid-cols-2 gap-4">
                  <label :class="['border rounded-lg p-4 cursor-pointer transition-all', formData.payment_preference === 'monthly' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400']">
                    <input type="radio" v-model="formData.payment_preference" value="monthly" class="sr-only" />
                    <div class="font-medium">Monthly Payments</div>
                    <div class="text-sm text-gray-500">Paid each month during contract</div>
                  </label>
                  <label :class="['border rounded-lg p-4 cursor-pointer transition-all', formData.payment_preference === 'lump_sum' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400']">
                    <input type="radio" v-model="formData.payment_preference" value="lump_sum" class="sr-only" />
                    <div class="font-medium">Lump Sum</div>
                    <div class="text-sm text-gray-500">Full payment after completion</div>
                  </label>
                </div>
              </div>

              <div v-if="formData.payment_preference === 'monthly'">
                <label class="block text-sm font-medium text-gray-700">Monthly Payment Amount (MK)</label>
                <input v-model="formData.monthly_amount" type="number" step="0.01" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 100000" />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                <div class="grid grid-cols-2 gap-4">
                  <label :class="['border rounded-lg p-4 cursor-pointer transition-all', formData.payment_method === 'bank' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400']">
                    <input type="radio" v-model="formData.payment_method" value="bank" class="sr-only" />
                    <div class="font-medium">Bank Transfer</div>
                    <div class="text-sm text-gray-500">Direct bank deposit</div>
                  </label>
                  <label :class="['border rounded-lg p-4 cursor-pointer transition-all', formData.payment_method === 'mobile_money' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400']">
                    <input type="radio" v-model="formData.payment_method" value="mobile_money" class="sr-only" />
                    <div class="font-medium">Mobile Money</div>
                    <div class="text-sm text-gray-500">Airtel Money / TNM Mpamba</div>
                  </label>
                </div>
              </div>

              <!-- Bank Details -->
              <div v-if="formData.payment_method === 'bank'" class="bg-gray-50 rounded-lg p-4 space-y-4">
                <h4 class="font-medium text-gray-900">Bank Account Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                    <select v-model="formData.bank_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                      <option value="">Select Bank</option>
                      <option value="National Bank of Malawi">National Bank of Malawi</option>
                      <option value="Standard Bank">Standard Bank</option>
                      <option value="FDH Bank">FDH Bank</option>
                      <option value="NBS Bank">NBS Bank</option>
                      <option value="Ecobank">Ecobank</option>
                      <option value="First Capital Bank">First Capital Bank</option>
                      <option value="CDH Bank">CDH Bank</option>
                      <option value="MyBucks">MyBucks</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Branch</label>
                    <input v-model="formData.bank_branch" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Account Name</label>
                    <input v-model="formData.bank_account_name" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Account Number</label>
                    <input v-model="formData.bank_account_number" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                  </div>
                </div>
              </div>

              <!-- Mobile Money Details -->
              <div v-if="formData.payment_method === 'mobile_money'" class="bg-gray-50 rounded-lg p-4 space-y-4">
                <h4 class="font-medium text-gray-900">Mobile Money Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Provider</label>
                    <select v-model="formData.mobile_money_provider" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                      <option value="">Select Provider</option>
                      <option value="Airtel Money">Airtel Money</option>
                      <option value="TNM Mpamba">TNM Mpamba</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input v-model="formData.mobile_money_number" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 0999123456" />
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Registered Name</label>
                    <input v-model="formData.mobile_money_name" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between pt-6 border-t mt-6">
              <div>
                <button
                  v-if="currentStep > 1"
                  type="button"
                  @click="currentStep--"
                  class="px-4 py-2 text-gray-700 hover:text-gray-900"
                >
                  Back
                </button>
              </div>
              <div class="flex space-x-3">
                <button type="button" @click="showCreateModal = false; showEditModal = false; resetForm()" class="px-4 py-2 text-gray-700 hover:text-gray-900">
                  Cancel
                </button>
                <button
                  v-if="currentStep < 2"
                  type="button"
                  @click="currentStep++"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                >
                  Next
                </button>
                <button
                  v-else
                  type="submit"
                  class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                >
                  {{ showEditModal ? 'Update' : 'Create' }} Teacher
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
