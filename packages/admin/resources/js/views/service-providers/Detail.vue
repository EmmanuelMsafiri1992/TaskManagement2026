<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { ArrowLeftIcon, DocumentArrowDownIcon, PlusIcon, CheckCircleIcon, BanknotesIcon, EyeIcon } from '@heroicons/vue/24/outline'

const route = useRoute()
const router = useRouter()

interface ServiceProvider {
  id: number
  name: string
  email: string
  phone: string
  national_id: string
  address: string
  specialty: string
  qualification: string
  status: string
  agreement_signed: boolean
  agreement_signed_at: string | null
  total_agreed_amount: number
  payment_preference: string
  monthly_amount: number | null
  payment_method: string | null
  bank_name: string | null
  bank_account_number: string | null
  bank_account_name: string | null
  bank_branch: string | null
  mobile_money_provider: string | null
  mobile_money_number: string | null
  mobile_money_name: string | null
  total_paid: number
  balance_remaining: number
  payment_progress_percent: number
  created_at: string
}

interface Payment {
  id: number
  amount: number
  payment_method: string
  payment_date: string
  status: string
  month_for: string | null
  receipt_number: string
  notes: string | null
  processed_by: { name: string } | null
}

const provider = ref<ServiceProvider | null>(null)
const payments = ref<Payment[]>([])
const stats = ref<any>(null)
const loading = ref(true)
const activeTab = ref('overview')

const showPaymentModal = ref(false)
const showEditPaymentModal = ref(false)

const paymentForm = ref({
  amount: '',
  payment_method: 'bank',
  payment_date: new Date().toISOString().split('T')[0],
  status: 'completed',
  month_for: '',
  reference_number: '',
  notes: ''
})

const fetchProvider = async () => {
  loading.value = true
  try {
    const response = await axios.get(`/api/service-providers/${route.params.id}`)
    provider.value = response.data.data
    stats.value = response.data.stats
  } catch (error) {
    console.error('Error fetching provider:', error)
  } finally {
    loading.value = false
  }
}

const fetchPayments = async () => {
  try {
    const response = await axios.get(`/api/service-providers/${route.params.id}/payments`)
    payments.value = response.data.data
  } catch (error) {
    console.error('Error fetching payments:', error)
  }
}

const createPayment = async () => {
  try {
    await axios.post('/api/payments', {
      ...paymentForm.value,
      service_provider_id: provider.value?.id
    })
    showPaymentModal.value = false
    resetPaymentForm()
    await fetchProvider()
    await fetchPayments()
  } catch (error) {
    console.error('Error creating payment:', error)
  }
}

const downloadReceipt = async (payment: Payment) => {
  window.open(`/api/payments/${payment.id}/receipt`, '_blank')
}

const downloadAgreement = () => {
  window.open(`/api/service-providers/${provider.value?.id}/download-agreement`, '_blank')
}

const signAgreement = async () => {
  try {
    await axios.post(`/api/service-providers/${provider.value?.id}/sign-agreement`)
    await fetchProvider()
  } catch (error) {
    console.error('Error signing agreement:', error)
  }
}

const impersonating = ref(false)

const impersonateProvider = async () => {
  if (!confirm(`Are you sure you want to impersonate ${provider.value?.name}? You will be logged in as this service provider.`)) {
    return
  }

  impersonating.value = true
  try {
    const response = await axios.post(`/api/service-providers/${provider.value?.id}/impersonate`)
    if (response.data.success) {
      // Redirect to the service provider dashboard
      window.location.href = response.data.redirect_url
    }
  } catch (error) {
    console.error('Error impersonating provider:', error)
    alert('Failed to impersonate service provider')
  } finally {
    impersonating.value = false
  }
}

const resetPaymentForm = () => {
  paymentForm.value = {
    amount: provider.value?.monthly_amount?.toString() || '',
    payment_method: provider.value?.payment_method || 'bank',
    payment_date: new Date().toISOString().split('T')[0],
    status: 'completed',
    month_for: '',
    reference_number: '',
    notes: ''
  }
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800'
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'suspended': return 'bg-red-100 text-red-800'
    case 'completed': return 'bg-green-100 text-green-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatCurrency = (amount: number) => {
  return 'MK ' + new Intl.NumberFormat().format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

onMounted(() => {
  fetchProvider()
  fetchPayments()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Back Button & Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button @click="router.push('/service-providers')" class="p-2 hover:bg-gray-100 rounded-lg">
          <ArrowLeftIcon class="w-5 h-5 text-gray-500" />
        </button>
        <div v-if="provider">
          <h1 class="text-2xl font-semibold text-gray-900">{{ provider.name }}</h1>
          <p class="text-sm text-gray-500">{{ provider.email }} | {{ provider.phone }}</p>
        </div>
      </div>
      <div class="flex space-x-3" v-if="provider">
        <button
          @click="impersonateProvider"
          :disabled="impersonating"
          class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 disabled:opacity-50"
        >
          <EyeIcon class="w-5 h-5 mr-2" />
          {{ impersonating ? 'Switching...' : 'View as Provider' }}
        </button>
        <button
          @click="downloadAgreement"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
        >
          <DocumentArrowDownIcon class="w-5 h-5 mr-2" />
          Download Agreement
        </button>
        <button
          v-if="!provider.agreement_signed"
          @click="signAgreement"
          class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          <CheckCircleIcon class="w-5 h-5 mr-2" />
          Mark Agreement Signed
        </button>
        <button
          @click="showPaymentModal = true; resetPaymentForm()"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Record Payment
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-gray-500">Loading...</div>

    <div v-else-if="provider">
      <!-- Payment Progress Card -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Payment Progress</h2>
          <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusClass(provider.status)]">
            {{ provider.status }}
          </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div>
            <div class="text-sm text-gray-500">Total Agreed</div>
            <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(provider.total_agreed_amount) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Total Paid</div>
            <div class="text-2xl font-bold text-green-600">{{ formatCurrency(provider.total_paid) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Balance Remaining</div>
            <div class="text-2xl font-bold text-red-600">{{ formatCurrency(provider.balance_remaining) }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Progress</div>
            <div class="flex items-center space-x-3">
              <div class="flex-1 bg-gray-200 rounded-full h-3">
                <div
                  class="bg-indigo-600 h-3 rounded-full transition-all"
                  :style="{ width: `${provider.payment_progress_percent}%` }"
                ></div>
              </div>
              <span class="text-lg font-bold text-gray-900">{{ provider.payment_progress_percent }}%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8">
          <button
            v-for="tab in ['overview', 'payments', 'sessions']"
            :key="tab"
            @click="activeTab = tab"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm capitalize',
              activeTab === tab
                ? 'border-indigo-500 text-indigo-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab }}
          </button>
        </nav>
      </div>

      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Personal Info -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold text-gray-900 mb-4">Personal Information</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-gray-500">Name</dt>
              <dd class="text-gray-900 font-medium">{{ provider.name }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Email</dt>
              <dd class="text-gray-900">{{ provider.email }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Phone</dt>
              <dd class="text-gray-900">{{ provider.phone || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">National ID</dt>
              <dd class="text-gray-900">{{ provider.national_id || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Address</dt>
              <dd class="text-gray-900">{{ provider.address || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Specialty</dt>
              <dd class="text-gray-900">{{ provider.specialty || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Qualification</dt>
              <dd class="text-gray-900">{{ provider.qualification || '-' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Payment Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold text-gray-900 mb-4">Payment Details</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-gray-500">Payment Preference</dt>
              <dd class="text-gray-900 capitalize">{{ provider.payment_preference?.replace('_', ' ') || '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Monthly Amount</dt>
              <dd class="text-gray-900">{{ provider.monthly_amount ? formatCurrency(provider.monthly_amount) : '-' }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Payment Method</dt>
              <dd class="text-gray-900 capitalize">{{ provider.payment_method?.replace('_', ' ') || '-' }}</dd>
            </div>
            <template v-if="provider.payment_method === 'bank'">
              <div class="flex justify-between">
                <dt class="text-gray-500">Bank Name</dt>
                <dd class="text-gray-900">{{ provider.bank_name || '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Account Name</dt>
                <dd class="text-gray-900">{{ provider.bank_account_name || '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Account Number</dt>
                <dd class="text-gray-900">{{ provider.bank_account_number || '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Branch</dt>
                <dd class="text-gray-900">{{ provider.bank_branch || '-' }}</dd>
              </div>
            </template>
            <template v-else-if="provider.payment_method === 'mobile_money'">
              <div class="flex justify-between">
                <dt class="text-gray-500">Provider</dt>
                <dd class="text-gray-900">{{ provider.mobile_money_provider || '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Name</dt>
                <dd class="text-gray-900">{{ provider.mobile_money_name || '-' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500">Number</dt>
                <dd class="text-gray-900">{{ provider.mobile_money_number || '-' }}</dd>
              </div>
            </template>
          </dl>
        </div>

        <!-- Agreement Status -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-semibold text-gray-900 mb-4">Agreement Status</h3>
          <div class="flex items-center space-x-3">
            <div :class="[
              'w-3 h-3 rounded-full',
              provider.agreement_signed ? 'bg-green-500' : 'bg-yellow-500'
            ]"></div>
            <span class="text-gray-900">
              {{ provider.agreement_signed ? 'Agreement Signed' : 'Agreement Pending' }}
            </span>
          </div>
          <div v-if="provider.agreement_signed_at" class="mt-2 text-sm text-gray-500">
            Signed on: {{ formatDate(provider.agreement_signed_at) }}
          </div>
        </div>

        <!-- Work Statistics -->
        <div class="bg-white rounded-lg shadow p-6" v-if="stats">
          <h3 class="font-semibold text-gray-900 mb-4">Work Statistics</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-gray-500">Total Sessions</dt>
              <dd class="text-gray-900 font-medium">{{ stats.total_sessions }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Recording Minutes</dt>
              <dd class="text-gray-900">{{ stats.total_recording_minutes }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Approved Sessions</dt>
              <dd class="text-green-600">{{ stats.approved_sessions }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Pending Sessions</dt>
              <dd class="text-yellow-600">{{ stats.pending_sessions }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-gray-500">Approved Lesson Plans</dt>
              <dd class="text-green-600">{{ stats.approved_lesson_plans }}</dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Payments Tab -->
      <div v-if="activeTab === 'payments'" class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Receipt #</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month For</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="payments.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">No payments recorded yet</td>
            </tr>
            <tr v-for="payment in payments" :key="payment.id">
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ payment.receipt_number }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(payment.payment_date) }}</td>
              <td class="px-6 py-4 text-sm text-gray-500">{{ payment.month_for || '-' }}</td>
              <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ formatCurrency(payment.amount) }}</td>
              <td class="px-6 py-4 text-sm text-gray-500 capitalize">{{ payment.payment_method.replace('_', ' ') }}</td>
              <td class="px-6 py-4">
                <span :class="['px-2 py-1 text-xs rounded-full', getStatusClass(payment.status)]">
                  {{ payment.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button
                  @click="downloadReceipt(payment)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                >
                  Download Receipt
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sessions Tab -->
      <div v-if="activeTab === 'sessions'" class="text-gray-500 text-center py-8">
        Recording sessions will be displayed here.
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showPaymentModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Record Payment for {{ provider?.name }}</h2>
          <form @submit.prevent="createPayment" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Amount (MK) *</label>
                <input v-model="paymentForm.amount" type="number" step="0.01" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Payment Date *</label>
                <input v-model="paymentForm.payment_date" type="date" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Payment Method *</label>
                <select v-model="paymentForm.payment_method" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option value="bank">Bank Transfer</option>
                  <option value="mobile_money">Mobile Money</option>
                  <option value="cash">Cash</option>
                  <option value="cheque">Cheque</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Status *</label>
                <select v-model="paymentForm.status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option value="completed">Completed</option>
                  <option value="pending">Pending</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Month For</label>
                <input v-model="paymentForm.month_for" type="text" placeholder="e.g., January 2026" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Reference Number</label>
                <input v-model="paymentForm.reference_number" type="text" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Notes</label>
              <textarea v-model="paymentForm.notes" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" @click="showPaymentModal = false" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Record Payment</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
