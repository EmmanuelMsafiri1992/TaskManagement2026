<template>
  <div>
    <div v-if="loading" class="flex justify-center py-12">
      <Loader size="40" color="#5850ec" />
    </div>

    <div v-else-if="lead">
      <!-- Header -->
      <div class="border-b border-gray-200 px-6 py-4">
        <div class="flex items-start justify-between">
          <div class="flex items-center">
            <div
              class="flex h-12 w-12 items-center justify-center rounded-full"
              :class="getPriorityBgColor(lead.priority)"
            >
              <span class="text-lg font-semibold" :class="getPriorityTextColor(lead.priority)">
                {{ lead.first_name.charAt(0) }}{{ lead.last_name ? lead.last_name.charAt(0) : '' }}
              </span>
            </div>
            <div class="ml-4">
              <h2 class="text-xl font-semibold text-gray-900">{{ lead.full_name }}</h2>
              <p class="text-sm text-gray-500">{{ lead.company_name || lead.email }}</p>
            </div>
          </div>
          <button class="text-gray-400 hover:text-gray-600" @click="$emit('close')">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Status & Priority Badges -->
        <div class="mt-4 flex flex-wrap gap-2">
          <span :class="getStatusClasses(lead.status)" class="inline-flex rounded-full px-3 py-1 text-sm font-medium">
            {{ lead.status_label }}
          </span>
          <span :class="getPriorityClasses(lead.priority)" class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium">
            <FireIcon v-if="lead.priority === 'hot'" class="mr-1 h-4 w-4" />
            {{ lead.priority.charAt(0).toUpperCase() + lead.priority.slice(1) }} Priority
          </span>
          <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">
            Score: {{ lead.score }}/100
          </span>
        </div>
      </div>

      <div class="max-h-[70vh] overflow-y-auto">
        <!-- Quick Actions -->
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
          <div class="flex flex-wrap gap-2">
            <button
              class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
              @click="showStatusModal = true"
            >
              <ArrowPathIcon class="mr-1.5 h-4 w-4" />
              {{ __('Update Status') }}
            </button>
            <button
              class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
              @click="showNoteModal = true"
            >
              <ChatBubbleLeftIcon class="mr-1.5 h-4 w-4" />
              {{ __('Add Note') }}
            </button>
            <button
              class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
              @click="showCallModal = true"
            >
              <PhoneIcon class="mr-1.5 h-4 w-4" />
              {{ __('Log Call') }}
            </button>
            <button
              class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
              @click="showFollowUpModal = true"
            >
              <CalendarIcon class="mr-1.5 h-4 w-4" />
              {{ __('Schedule Follow-up') }}
            </button>
            <button
              v-if="lead.status !== 'won' && lead.status !== 'lost'"
              class="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-green-700"
              @click="convertToClient"
            >
              <CheckCircleIcon class="mr-1.5 h-4 w-4" />
              {{ __('Convert to Client') }}
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
          <!-- Lead Information -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Contact Details -->
            <div class="rounded-lg border border-gray-200 bg-white p-4">
              <h3 class="mb-4 font-semibold text-gray-900">{{ __('Contact Information') }}</h3>
              <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <dt class="text-gray-500">{{ __('Email') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">
                    <a :href="'mailto:' + lead.email" class="text-indigo-600 hover:underline">{{ lead.email }}</a>
                  </dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Phone') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">
                    <a v-if="lead.phone" :href="'tel:' + lead.phone" class="text-indigo-600 hover:underline">{{ lead.phone }}</a>
                    <span v-else class="text-gray-400">-</span>
                  </dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Company') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ lead.company_name || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Job Title') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ lead.job_title || '-' }}</dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Location') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">
                    {{ [lead.city, lead.country].filter(Boolean).join(', ') || '-' }}
                  </dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Website') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">
                    <a v-if="lead.website" :href="lead.website" target="_blank" class="text-indigo-600 hover:underline">{{ lead.website }}</a>
                    <span v-else class="text-gray-400">-</span>
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Project Details -->
            <div class="rounded-lg border border-gray-200 bg-white p-4">
              <h3 class="mb-4 font-semibold text-gray-900">{{ __('Project Details') }}</h3>
              <dl class="space-y-4 text-sm">
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <dt class="text-gray-500">{{ __('Service Interest') }}</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ lead.service_interest_label }}</dd>
                  </div>
                  <div>
                    <dt class="text-gray-500">{{ __('Budget') }}</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ lead.budget_range_label || '-' }}</dd>
                  </div>
                  <div>
                    <dt class="text-gray-500">{{ __('Timeline') }}</dt>
                    <dd class="mt-1 font-medium text-gray-900">{{ lead.timeline || '-' }}</dd>
                  </div>
                </div>
                <div v-if="lead.project_description">
                  <dt class="text-gray-500">{{ __('Description') }}</dt>
                  <dd class="mt-1 whitespace-pre-wrap text-gray-900">{{ lead.project_description }}</dd>
                </div>
              </dl>
            </div>

            <!-- Activity Timeline -->
            <div class="rounded-lg border border-gray-200 bg-white p-4">
              <h3 class="mb-4 font-semibold text-gray-900">{{ __('Activity Timeline') }}</h3>
              <div v-if="lead.activities?.length" class="flow-root">
                <ul class="-mb-8">
                  <li v-for="(activity, index) in lead.activities" :key="activity.id">
                    <div class="relative pb-8">
                      <span
                        v-if="index !== lead.activities.length - 1"
                        class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200"
                      ></span>
                      <div class="relative flex space-x-3">
                        <div>
                          <span :class="getActivityIconClass(activity.type)" class="flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-white">
                            <component :is="getActivityIcon(activity.type)" class="h-4 w-4 text-white" />
                          </span>
                        </div>
                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                          <div>
                            <p class="text-sm font-medium text-gray-900">{{ activity.type_label }}</p>
                            <p v-if="activity.description" class="mt-1 text-sm text-gray-500">{{ activity.description }}</p>
                          </div>
                          <div class="whitespace-nowrap text-right text-sm text-gray-500">
                            <time>{{ formatDate(activity.created_at) }}</time>
                            <p v-if="activity.user" class="text-xs">{{ activity.user.name }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
              <p v-else class="text-center text-sm text-gray-500">{{ __('No activities yet') }}</p>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Lead Info Card -->
            <div class="rounded-lg border border-gray-200 bg-white p-4">
              <h3 class="mb-4 font-semibold text-gray-900">{{ __('Lead Info') }}</h3>
              <dl class="space-y-3 text-sm">
                <div>
                  <dt class="text-gray-500">{{ __('Source') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ lead.source }}</dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Assigned To') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ lead.assigned_user?.name || 'Unassigned' }}</dd>
                </div>
                <div>
                  <dt class="text-gray-500">{{ __('Created') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ formatDate(lead.created_at) }}</dd>
                </div>
                <div v-if="lead.last_contacted_at">
                  <dt class="text-gray-500">{{ __('Last Contacted') }}</dt>
                  <dd class="mt-1 font-medium text-gray-900">{{ formatDate(lead.last_contacted_at) }}</dd>
                </div>
                <div v-if="lead.next_follow_up_at">
                  <dt class="text-gray-500">{{ __('Next Follow-up') }}</dt>
                  <dd class="mt-1 font-medium" :class="isOverdue(lead.next_follow_up_at) ? 'text-red-600' : 'text-gray-900'">
                    {{ formatDate(lead.next_follow_up_at) }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- UTM Tracking (if available) -->
            <div v-if="lead.utm_source || lead.landing_page" class="rounded-lg border border-gray-200 bg-white p-4">
              <h3 class="mb-4 font-semibold text-gray-900">{{ __('Tracking Data') }}</h3>
              <dl class="space-y-2 text-sm">
                <div v-if="lead.utm_source">
                  <dt class="text-gray-500">UTM Source</dt>
                  <dd class="font-medium text-gray-900">{{ lead.utm_source }}</dd>
                </div>
                <div v-if="lead.utm_medium">
                  <dt class="text-gray-500">UTM Medium</dt>
                  <dd class="font-medium text-gray-900">{{ lead.utm_medium }}</dd>
                </div>
                <div v-if="lead.utm_campaign">
                  <dt class="text-gray-500">UTM Campaign</dt>
                  <dd class="font-medium text-gray-900">{{ lead.utm_campaign }}</dd>
                </div>
                <div v-if="lead.landing_page">
                  <dt class="text-gray-500">Landing Page</dt>
                  <dd class="truncate font-medium text-gray-900" :title="lead.landing_page">{{ lead.landing_page }}</dd>
                </div>
              </dl>
            </div>

            <!-- Internal Notes -->
            <div v-if="lead.internal_notes" class="rounded-lg border border-gray-200 bg-white p-4">
              <h3 class="mb-2 font-semibold text-gray-900">{{ __('Internal Notes') }}</h3>
              <p class="whitespace-pre-wrap text-sm text-gray-600">{{ lead.internal_notes }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Status Update Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-full max-w-md rounded-lg bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ __('Update Status') }}</h3>
        <select
          v-model="statusForm.status"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="new">New</option>
          <option value="contacted">Contacted</option>
          <option value="qualified">Qualified</option>
          <option value="proposal_sent">Proposal Sent</option>
          <option value="negotiation">Negotiation</option>
          <option value="won">Won</option>
          <option value="lost">Lost</option>
        </select>
        <input
          v-if="statusForm.status === 'lost'"
          v-model="statusForm.loss_reason"
          type="text"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm"
          :placeholder="__('Reason for loss')"
        />
        <div class="flex justify-end gap-2">
          <TheButton variant="secondary" @click="showStatusModal = false">{{ __('Cancel') }}</TheButton>
          <TheButton @click="updateStatus">{{ __('Update') }}</TheButton>
        </div>
      </div>
    </div>

    <!-- Add Note Modal -->
    <div v-if="showNoteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-full max-w-md rounded-lg bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ __('Add Note') }}</h3>
        <textarea
          v-model="noteForm.note"
          rows="4"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          :placeholder="__('Enter your note...')"
        ></textarea>
        <div class="flex justify-end gap-2">
          <TheButton variant="secondary" @click="showNoteModal = false">{{ __('Cancel') }}</TheButton>
          <TheButton @click="addNote">{{ __('Add Note') }}</TheButton>
        </div>
      </div>
    </div>

    <!-- Log Call Modal -->
    <div v-if="showCallModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-full max-w-md rounded-lg bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ __('Log Call') }}</h3>
        <select
          v-model="callForm.type"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="call_made">Call Made</option>
          <option value="call_received">Call Received</option>
        </select>
        <input
          v-model="callForm.outcome"
          type="text"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm"
          :placeholder="__('Call outcome')"
        />
        <textarea
          v-model="callForm.notes"
          rows="3"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm"
          :placeholder="__('Call notes...')"
        ></textarea>
        <div class="flex justify-end gap-2">
          <TheButton variant="secondary" @click="showCallModal = false">{{ __('Cancel') }}</TheButton>
          <TheButton @click="logCall">{{ __('Log Call') }}</TheButton>
        </div>
      </div>
    </div>

    <!-- Schedule Follow-up Modal -->
    <div v-if="showFollowUpModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="w-full max-w-md rounded-lg bg-white p-6">
        <h3 class="mb-4 text-lg font-semibold">{{ __('Schedule Follow-up') }}</h3>
        <input
          v-model="followUpForm.next_follow_up_at"
          type="datetime-local"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        />
        <textarea
          v-model="followUpForm.note"
          rows="3"
          class="mb-4 block w-full rounded-md border-gray-300 shadow-sm"
          :placeholder="__('Note about follow-up...')"
        ></textarea>
        <div class="flex justify-end gap-2">
          <TheButton variant="secondary" @click="showFollowUpModal = false">{{ __('Cancel') }}</TheButton>
          <TheButton @click="scheduleFollowUp">{{ __('Schedule') }}</TheButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import {
  ArrowPathIcon, CalendarIcon, ChatBubbleLeftIcon, CheckCircleIcon,
  EnvelopeIcon, FireIcon, PhoneIcon, PlusCircleIcon, XMarkIcon
} from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'

const props = defineProps({
  leadId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

const loading = ref(true)
const lead = ref(null)

// Modals
const showStatusModal = ref(false)
const showNoteModal = ref(false)
const showCallModal = ref(false)
const showFollowUpModal = ref(false)

// Forms
const statusForm = reactive({ status: '', loss_reason: '' })
const noteForm = reactive({ note: '' })
const callForm = reactive({ type: 'call_made', outcome: '', notes: '' })
const followUpForm = reactive({ next_follow_up_at: '', note: '' })

const loadLead = async () => {
  loading.value = true
  try {
    const response = await axios.get(`leads/${props.leadId}`)
    lead.value = response.data.data
    statusForm.status = lead.value.status
  } catch (error) {
    console.error('Failed to load lead:', error)
  } finally {
    loading.value = false
  }
}

const updateStatus = async () => {
  try {
    await axios.post(`leads/${props.leadId}/status`, statusForm)
    showStatusModal.value = false
    emit('updated')
    loadLead()
  } catch (error) {
    console.error('Failed to update status:', error)
  }
}

const addNote = async () => {
  try {
    await axios.post(`leads/${props.leadId}/note`, noteForm)
    showNoteModal.value = false
    noteForm.note = ''
    loadLead()
  } catch (error) {
    console.error('Failed to add note:', error)
  }
}

const logCall = async () => {
  try {
    await axios.post(`leads/${props.leadId}/log-call`, callForm)
    showCallModal.value = false
    Object.assign(callForm, { type: 'call_made', outcome: '', notes: '' })
    emit('updated')
    loadLead()
  } catch (error) {
    console.error('Failed to log call:', error)
  }
}

const scheduleFollowUp = async () => {
  try {
    await axios.post(`leads/${props.leadId}/follow-up`, followUpForm)
    showFollowUpModal.value = false
    Object.assign(followUpForm, { next_follow_up_at: '', note: '' })
    loadLead()
  } catch (error) {
    console.error('Failed to schedule follow-up:', error)
  }
}

const convertToClient = async () => {
  if (!confirm('Convert this lead to a client?')) return
  try {
    await axios.post(`leads/${props.leadId}/convert`)
    emit('updated')
    emit('close')
  } catch (error) {
    console.error('Failed to convert lead:', error)
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const isOverdue = (date) => {
  return new Date(date) < new Date()
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
  const colors = { hot: 'bg-red-100', warm: 'bg-orange-100', cold: 'bg-blue-100' }
  return colors[priority] || 'bg-gray-100'
}

const getPriorityTextColor = (priority) => {
  const colors = { hot: 'text-red-600', warm: 'text-orange-600', cold: 'text-blue-600' }
  return colors[priority] || 'text-gray-600'
}

const getActivityIcon = (type) => {
  const icons = {
    created: PlusCircleIcon,
    email_sent: EnvelopeIcon,
    call_made: PhoneIcon,
    call_received: PhoneIcon,
    note_added: ChatBubbleLeftIcon,
    status_changed: ArrowPathIcon,
    follow_up_scheduled: CalendarIcon
  }
  return icons[type] || PlusCircleIcon
}

const getActivityIconClass = (type) => {
  const classes = {
    created: 'bg-green-500',
    email_sent: 'bg-blue-500',
    call_made: 'bg-indigo-500',
    call_received: 'bg-purple-500',
    note_added: 'bg-yellow-500',
    status_changed: 'bg-gray-500',
    follow_up_scheduled: 'bg-orange-500'
  }
  return classes[type] || 'bg-gray-500'
}

onMounted(loadLead)
</script>
