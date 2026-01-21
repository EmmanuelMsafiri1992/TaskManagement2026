<script setup lang="ts">
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { CheckCircleIcon, EllipsisVerticalIcon, MagnifyingGlassIcon, XCircleIcon } from '@heroicons/vue/24/outline'

interface RecordingSession {
  id: number
  service_provider: { id: number; name: string; email: string }
  subject: { id: number; name: string; code: string } | null
  topic: { id: number; name: string } | null
  status: string
  clock_in: string | null
  clock_out: string | null
  recording_minutes: number
  total_minutes: number
  quality_rating: number | null
  admin_notes: string | null
  video_file: string | null
  created_at: string
}

interface Stats {
  total_sessions: number
  pending_review: number
  approved: number
  rejected: number
  in_progress: number
  total_recording_minutes: number
}

const sessions = ref<RecordingSession[]>([])
const stats = ref<Stats>({ total_sessions: 0, pending_review: 0, approved: 0, rejected: 0, in_progress: 0, total_recording_minutes: 0 })
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const showApproveModal = ref(false)
const showRejectModal = ref(false)
const selectedSession = ref<RecordingSession | null>(null)
const approveForm = ref({ quality_rating: 5, admin_notes: '' })
const rejectForm = ref({ admin_notes: '' })

const fetchSessions = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page.toString())
    if (search.value) params.append('search', search.value)
    if (statusFilter.value) params.append('status', statusFilter.value)

    const response = await axios.get(`/api/recording-sessions?${params}`)
    sessions.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching sessions:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/recording-sessions/statistics')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const openApproveModal = (session: RecordingSession) => {
  selectedSession.value = session
  approveForm.value = { quality_rating: 5, admin_notes: '' }
  showApproveModal.value = true
}

const openRejectModal = (session: RecordingSession) => {
  selectedSession.value = session
  rejectForm.value = { admin_notes: '' }
  showRejectModal.value = true
}

const approveSession = async () => {
  if (!selectedSession.value) return
  try {
    await axios.post(`/api/recording-sessions/${selectedSession.value.id}/approve`, approveForm.value)
    showApproveModal.value = false
    await fetchSessions(pagination.value.current_page)
    await fetchStats()
  } catch (error) {
    console.error('Error approving session:', error)
  }
}

const rejectSession = async () => {
  if (!selectedSession.value) return
  try {
    await axios.post(`/api/recording-sessions/${selectedSession.value.id}/reject`, rejectForm.value)
    showRejectModal.value = false
    await fetchSessions(pagination.value.current_page)
    await fetchStats()
  } catch (error) {
    console.error('Error rejecting session:', error)
  }
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'approved': return 'bg-green-100 text-green-800'
    case 'pending_review': return 'bg-yellow-100 text-yellow-800'
    case 'rejected': return 'bg-red-100 text-red-800'
    case 'in_progress': return 'bg-blue-100 text-blue-800'
    case 'completed': return 'bg-purple-100 text-purple-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatMinutes = (minutes: number) => {
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return hours > 0 ? `${hours}h ${mins}m` : `${mins}m`
}

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleString()
}

onMounted(() => {
  fetchSessions()
  fetchStats()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Recording Sessions</h1>
      <p class="mt-1 text-sm text-gray-500">Track and manage all recording sessions by teachers</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Total</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_sessions }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Pending Review</div>
        <div class="mt-1 text-2xl font-semibold text-yellow-600">{{ stats.pending_review }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Approved</div>
        <div class="mt-1 text-2xl font-semibold text-green-600">{{ stats.approved }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Rejected</div>
        <div class="mt-1 text-2xl font-semibold text-red-600">{{ stats.rejected }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">In Progress</div>
        <div class="mt-1 text-2xl font-semibold text-blue-600">{{ stats.in_progress }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Total Recording</div>
        <div class="mt-1 text-2xl font-semibold text-indigo-600">{{ formatMinutes(stats.total_recording_minutes) }}</div>
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
              type="text"
              placeholder="Search by teacher name..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
              @input="fetchSessions(1)"
            />
          </div>
        </div>
        <select
          v-model="statusFilter"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          @change="fetchSessions(1)"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="in_progress">In Progress</option>
          <option value="completed">Completed</option>
          <option value="pending_review">Pending Review</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject / Topic</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recording</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rating</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading...</td>
          </tr>
          <tr v-else-if="sessions.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No recording sessions found</td>
          </tr>
          <tr v-for="session in sessions" :key="session.id">
            <td class="px-6 py-4">
              <div class="font-medium text-gray-900">{{ session.service_provider?.name }}</div>
              <div class="text-sm text-gray-500">{{ session.service_provider?.email }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-gray-900">{{ session.subject?.name || '-' }}</div>
              <div class="text-sm text-gray-500">{{ session.topic?.name || '-' }}</div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              <div>In: {{ formatDate(session.clock_in) }}</div>
              <div>Out: {{ formatDate(session.clock_out) }}</div>
            </td>
            <td class="px-6 py-4 text-sm">
              <div class="text-gray-900">{{ formatMinutes(session.recording_minutes) }}</div>
              <div class="text-gray-500">Total: {{ formatMinutes(session.total_minutes) }}</div>
            </td>
            <td class="px-6 py-4">
              <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(session.status)]">
                {{ session.status.replace('_', ' ') }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ session.quality_rating ? `${session.quality_rating}/5` : '-' }}
            </td>
            <td class="px-6 py-4 text-right">
              <Menu as="div" class="relative inline-block text-left">
                <MenuButton class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                  <EllipsisVerticalIcon class="w-5 h-5" />
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                  <div class="py-1">
                    <MenuItem v-if="session.status === 'pending_review' || session.status === 'completed'" v-slot="{ active }">
                      <button
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-green-700']"
                        @click="openApproveModal(session)"
                      >
                        <CheckCircleIcon class="w-4 h-4 inline mr-2" />
                        Approve
                      </button>
                    </MenuItem>
                    <MenuItem v-if="session.status === 'pending_review' || session.status === 'completed'" v-slot="{ active }">
                      <button
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-red-700']"
                        @click="openRejectModal(session)"
                      >
                        <XCircleIcon class="w-4 h-4 inline mr-2" />
                        Reject
                      </button>
                    </MenuItem>
                    <MenuItem v-if="session.video_file" v-slot="{ active }">
                      <a
                        :href="session.video_file"
                        target="_blank"
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                      >
                        View Video
                      </a>
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
            :class="[
              'px-3 py-1 rounded',
              page === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
            @click="fetchSessions(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showApproveModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Approve Recording Session</h2>
          <form class="space-y-4" @submit.prevent="approveSession">
            <div>
              <label class="block text-sm font-medium text-gray-700">Quality Rating (1-5)</label>
              <select v-model="approveForm.quality_rating" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option :value="1">1 - Poor</option>
                <option :value="2">2 - Fair</option>
                <option :value="3">3 - Good</option>
                <option :value="4">4 - Very Good</option>
                <option :value="5">5 - Excellent</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Notes (optional)</label>
              <textarea v-model="approveForm.admin_notes" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" class="px-4 py-2 text-gray-700 hover:text-gray-900" @click="showApproveModal = false">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Approve</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Reject Recording Session</h2>
          <form class="space-y-4" @submit.prevent="rejectSession">
            <div>
              <label class="block text-sm font-medium text-gray-700">Reason for Rejection *</label>
              <textarea v-model="rejectForm.admin_notes" rows="3" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" class="px-4 py-2 text-gray-700 hover:text-gray-900" @click="showRejectModal = false">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Reject</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
