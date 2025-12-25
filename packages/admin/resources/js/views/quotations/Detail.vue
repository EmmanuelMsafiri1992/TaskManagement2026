<template>
  <div v-if="loading" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else-if="quotation" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <button
          class="mb-2 inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
          @click="router.push('/quotations')"
        >
          <ArrowLeftIcon class="mr-1 h-4 w-4" />
          {{ __('Back to Quotations') }}
        </button>
        <h1 class="text-2xl font-bold text-gray-900">{{ quotation.quotation_number }}</h1>
        <p class="mt-1 text-sm text-gray-500">
          {{ __('Created on') }} {{ formatDate(quotation.created_at) }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <span
          class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
          :class="getStatusClass(quotation.status)"
        >
          {{ quotation.status.charAt(0).toUpperCase() + quotation.status.slice(1) }}
        </span>

        <div class="flex gap-2">
          <TheButton variant="secondary" @click="downloadPdf">
            <DocumentArrowDownIcon class="mr-2 h-4 w-4" />
            {{ __('Download PDF') }}
          </TheButton>
          <TheButton @click="openEditModal">
            <PencilIcon class="mr-2 h-4 w-4" />
            {{ __('Edit') }}
          </TheButton>
        </div>
      </div>
    </div>

    <!-- Status Actions -->
    <div class="flex gap-2">
      <TheButton
        v-if="quotation.status === 'draft'"
        variant="secondary"
        size="sm"
        @click="changeStatus('sent')"
      >
        <PaperAirplaneIcon class="mr-1 h-4 w-4" />
        {{ __('Mark as Sent') }}
      </TheButton>
      <TheButton
        v-if="quotation.status === 'sent'"
        variant="success"
        size="sm"
        @click="changeStatus('accepted')"
      >
        <CheckIcon class="mr-1 h-4 w-4" />
        {{ __('Mark as Accepted') }}
      </TheButton>
      <TheButton
        v-if="quotation.status === 'sent'"
        variant="danger"
        size="sm"
        @click="changeStatus('rejected')"
      >
        <XMarkIcon class="mr-1 h-4 w-4" />
        {{ __('Mark as Rejected') }}
      </TheButton>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Left Column - Quotation Preview -->
      <div class="lg:col-span-2">
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-6">
            <!-- Quotation Header -->
            <div class="mb-6 flex justify-between">
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ __('QUOTATION') }}</h2>
                <p class="text-sm text-gray-500"># {{ quotation.quotation_number }}</p>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold" :style="{ color: quotation.color || '#2568ef' }">
                  {{ quotation.business_name || 'Your Company' }}
                </div>
                <p v-if="quotation.business_address" class="text-sm text-gray-500">
                  {{ quotation.business_address }}
                </p>
                <p v-if="quotation.business_phone" class="text-sm text-gray-500">
                  {{ quotation.business_phone }}
                </p>
              </div>
            </div>

            <!-- Customer & Dates -->
            <div class="mb-6 grid grid-cols-2 gap-6 border-t border-gray-200 pt-6">
              <div>
                <h3 class="mb-2 text-sm font-semibold text-gray-700">{{ __('Bill To') }}</h3>
                <p class="font-medium text-gray-900">{{ quotation.customer_name }}</p>
                <p class="text-sm text-gray-500">{{ quotation.customer_email }}</p>
                <p v-if="quotation.customer_phone" class="text-sm text-gray-500">
                  {{ quotation.customer_phone }}
                </p>
                <p v-if="quotation.customer_address" class="text-sm text-gray-500">
                  {{ quotation.customer_address }}
                </p>
              </div>
              <div class="text-right">
                <div class="mb-2">
                  <span class="text-sm text-gray-500">{{ __('Date') }}:</span>
                  <span class="ml-2 font-medium">{{ formatDate(quotation.quotation_date) }}</span>
                </div>
                <div class="mb-2">
                  <span class="text-sm text-gray-500">{{ __('Valid Until') }}:</span>
                  <span class="ml-2 font-medium" :class="isExpired ? 'text-red-500' : ''">
                    {{ formatDate(quotation.valid_until) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Items Table -->
            <div class="mb-6 overflow-hidden rounded-lg border border-gray-200">
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr :style="{ backgroundColor: quotation.color || '#2568ef' }">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">
                      {{ __('Description') }}
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-white">
                      {{ __('Qty') }}
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white">
                      {{ __('Price') }}
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white">
                      {{ __('Total') }}
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="item in quotation.items" :key="item.id">
                    <td class="px-4 py-3">
                      <div class="font-medium text-gray-900">{{ item.description }}</div>
                      <div v-if="item.details" class="text-sm text-gray-500">{{ item.details }}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-500">{{ item.quantity }}</td>
                    <td class="px-4 py-3 text-right text-gray-500">
                      {{ formatNumber(item.unit_price) }}
                    </td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                      {{ formatNumber(item.total) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Totals -->
            <div class="flex justify-end">
              <div class="w-64 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">{{ __('Subtotal') }}:</span>
                  <span class="font-medium">{{ quotation.currency }} {{ formatNumber(quotation.subtotal) }}</span>
                </div>
                <div v-if="quotation.tax_rate > 0" class="flex justify-between text-sm">
                  <span class="text-gray-500">{{ __('Tax') }} ({{ quotation.tax_rate }}%):</span>
                  <span class="font-medium">{{ quotation.currency }} {{ formatNumber(quotation.tax_amount) }}</span>
                </div>
                <div v-if="quotation.discount_amount > 0" class="flex justify-between text-sm">
                  <span class="text-gray-500">{{ __('Discount') }}:</span>
                  <span class="font-medium text-red-500">-{{ quotation.currency }} {{ formatNumber(quotation.discount_amount) }}</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
                  <span>{{ __('Total') }}:</span>
                  <span :style="{ color: quotation.color || '#2568ef' }">
                    {{ quotation.currency }} {{ formatNumber(quotation.total_amount) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Notes & Terms -->
            <div v-if="quotation.notes || quotation.terms" class="mt-6 border-t border-gray-200 pt-6">
              <div v-if="quotation.notes" class="mb-4">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">{{ __('Notes') }}</h4>
                <p class="whitespace-pre-line text-sm text-gray-500">{{ quotation.notes }}</p>
              </div>
              <div v-if="quotation.terms">
                <h4 class="mb-2 text-sm font-semibold text-gray-700">{{ __('Terms & Conditions') }}</h4>
                <p class="whitespace-pre-line text-sm text-gray-500">{{ quotation.terms }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Info -->
      <div class="space-y-6">
        <!-- Quick Info -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Quick Info') }}</h3>
          </div>
          <div class="p-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">{{ __('Created By') }}:</span>
              <span class="text-sm font-medium">{{ quotation.user?.name || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">{{ __('Template') }}:</span>
              <span class="text-sm font-medium">{{ getTemplateName(quotation.template) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">{{ __('Currency') }}:</span>
              <span class="text-sm font-medium">{{ quotation.currency }}</span>
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="border-b border-gray-200 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-900">{{ __('Timeline') }}</h3>
          </div>
          <div class="p-4">
            <div class="flow-root">
              <ul class="-mb-4">
                <li class="relative pb-4">
                  <div class="relative flex space-x-3">
                    <div>
                      <span class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 ring-4 ring-white">
                        <DocumentTextIcon class="h-4 w-4 text-indigo-600" />
                      </span>
                    </div>
                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                      <div>
                        <p class="text-sm text-gray-500">{{ __('Quotation created') }}</p>
                      </div>
                      <div class="whitespace-nowrap text-right text-sm text-gray-500">
                        {{ formatDateTime(quotation.created_at) }}
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <FormModal v-if="showEditModal" size="4xl" @saved="handleSaved">
      <FormQuotation :model-value="quotation" @close="showEditModal = false" />
    </FormModal>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  ArrowLeftIcon, CheckIcon, DocumentArrowDownIcon, DocumentTextIcon,
  PaperAirplaneIcon, PencilIcon, XMarkIcon
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import FormQuotation from './Form.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(true)
const quotation = ref(null)
const showEditModal = ref(false)

const isExpired = computed(() => {
  if (!quotation.value?.valid_until) return false
  return new Date(quotation.value.valid_until) < new Date()
})

const loadQuotation = async () => {
  try {
    const response = await axios.get(`quotations/${route.params.id}`)
    quotation.value = response.data.data
  } catch (error) {
    console.error('Failed to load quotation:', error)
  } finally {
    loading.value = false
  }
}

const downloadPdf = async () => {
  try {
    const response = await axios.get(`quotations/${quotation.value.id}/pdf`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `${quotation.value.quotation_number}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Failed to download PDF:', error)
  }
}

const changeStatus = async (status) => {
  try {
    await axios.post(`quotations/${quotation.value.id}/change-status`, { status })
    quotation.value.status = status
  } catch (error) {
    console.error('Failed to change status:', error)
  }
}

const openEditModal = () => {
  showEditModal.value = true
}

const handleSaved = () => {
  showEditModal.value = false
  loadQuotation()
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const formatDateTime = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatNumber = (num) => {
  return parseFloat(num || 0).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const getStatusClass = (status) => {
  return {
    'bg-gray-100 text-gray-800': status === 'draft',
    'bg-blue-100 text-blue-800': status === 'sent',
    'bg-green-100 text-green-800': status === 'accepted',
    'bg-red-100 text-red-800': status === 'rejected',
    'bg-yellow-100 text-yellow-800': status === 'expired',
  }[status] || 'bg-gray-100 text-gray-800'
}

const getTemplateName = (template) => {
  const names = {
    style_1: 'Classic',
    style_2: 'Modern',
    style_3: 'Colorful Header',
    style_4: 'Bordered',
    style_5: 'Info Banner',
    style_6: 'Minimal',
    style_7: 'Split Header',
    style_8: 'Elegant'
  }
  return names[template] || template
}

onMounted(() => {
  loadQuotation()
})
</script>
