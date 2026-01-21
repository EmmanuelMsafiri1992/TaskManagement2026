<script setup lang="ts">
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { CheckCircleIcon, EllipsisVerticalIcon, EyeIcon, MagnifyingGlassIcon, XCircleIcon } from '@heroicons/vue/24/outline'

interface LessonPlan {
  id: number
  service_provider: { id: number; name: string; email: string }
  topic: { id: number; name: string; subject: { id: number; name: string } } | null
  title: string
  objectives: string | null
  status: string
  duration_minutes: number | null
  feedback: string | null
  created_at: string
}

interface Stats {
  total: number
  draft: number
  submitted: number
  approved: number
  rejected: number
}

const plans = ref<LessonPlan[]>([])
const stats = ref<Stats>({ total: 0, draft: 0, submitted: 0, approved: 0, rejected: 0 })
const loading = ref(true)
const search = ref('')
const statusFilter = ref('')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const showDetailModal = ref(false)
const showRejectModal = ref(false)
const selectedPlan = ref<LessonPlan | null>(null)
const rejectForm = ref({ feedback: '' })

const fetchPlans = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page.toString())
    if (search.value) params.append('search', search.value)
    if (statusFilter.value) params.append('status', statusFilter.value)

    const response = await axios.get(`/api/lesson-plans?${params}`)
    plans.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching lesson plans:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/lesson-plans/statistics')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const viewPlan = async (plan: LessonPlan) => {
  try {
    const response = await axios.get(`/api/lesson-plans/${plan.id}`)
    selectedPlan.value = response.data.data
    showDetailModal.value = true
  } catch (error) {
    console.error('Error fetching plan details:', error)
  }
}

const approvePlan = async (plan: LessonPlan) => {
  try {
    await axios.post(`/api/lesson-plans/${plan.id}/approve`)
    await fetchPlans(pagination.value.current_page)
    await fetchStats()
  } catch (error) {
    console.error('Error approving plan:', error)
  }
}

const openRejectModal = (plan: LessonPlan) => {
  selectedPlan.value = plan
  rejectForm.value = { feedback: '' }
  showRejectModal.value = true
}

const rejectPlan = async () => {
  if (!selectedPlan.value) return
  try {
    await axios.post(`/api/lesson-plans/${selectedPlan.value.id}/reject`, rejectForm.value)
    showRejectModal.value = false
    await fetchPlans(pagination.value.current_page)
    await fetchStats()
  } catch (error) {
    console.error('Error rejecting plan:', error)
  }
}

const getStatusClass = (status: string) => {
  switch (status) {
    case 'approved': return 'bg-green-100 text-green-800'
    case 'submitted': return 'bg-yellow-100 text-yellow-800'
    case 'rejected': return 'bg-red-100 text-red-800'
    case 'draft': return 'bg-gray-100 text-gray-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

onMounted(() => {
  fetchPlans()
  fetchStats()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-semibold text-gray-900">Lesson Plans</h1>
      <p class="mt-1 text-sm text-gray-500">Review and approve lesson plans submitted by teachers</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Total</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Draft</div>
        <div class="mt-1 text-2xl font-semibold text-gray-600">{{ stats.draft }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Submitted</div>
        <div class="mt-1 text-2xl font-semibold text-yellow-600">{{ stats.submitted }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Approved</div>
        <div class="mt-1 text-2xl font-semibold text-green-600">{{ stats.approved }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Rejected</div>
        <div class="mt-1 text-2xl font-semibold text-red-600">{{ stats.rejected }}</div>
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
              placeholder="Search by title or teacher name..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
              @input="fetchPlans(1)"
            />
          </div>
        </div>
        <select
          v-model="statusFilter"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          @change="fetchPlans(1)"
        >
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="submitted">Submitted</option>
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
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teacher</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject / Topic</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">Loading...</td>
          </tr>
          <tr v-else-if="plans.length === 0">
            <td colspan="7" class="px-6 py-8 text-center text-gray-500">No lesson plans found</td>
          </tr>
          <tr v-for="plan in plans" :key="plan.id">
            <td class="px-6 py-4">
              <div class="font-medium text-gray-900">{{ plan.title }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-gray-900">{{ plan.service_provider?.name }}</div>
              <div class="text-sm text-gray-500">{{ plan.service_provider?.email }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-gray-900">{{ plan.topic?.subject?.name || '-' }}</div>
              <div class="text-sm text-gray-500">{{ plan.topic?.name || '-' }}</div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ plan.duration_minutes ? `${plan.duration_minutes} min` : '-' }}
            </td>
            <td class="px-6 py-4">
              <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(plan.status)]">
                {{ plan.status }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
              {{ formatDate(plan.created_at) }}
            </td>
            <td class="px-6 py-4 text-right">
              <Menu as="div" class="relative inline-block text-left">
                <MenuButton class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                  <EllipsisVerticalIcon class="w-5 h-5" />
                </MenuButton>
                <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                  <div class="py-1">
                    <MenuItem v-slot="{ active }">
                      <button
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                        @click="viewPlan(plan)"
                      >
                        <EyeIcon class="w-4 h-4 inline mr-2" />
                        View Details
                      </button>
                    </MenuItem>
                    <MenuItem v-if="plan.status === 'submitted'" v-slot="{ active }">
                      <button
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-green-700']"
                        @click="approvePlan(plan)"
                      >
                        <CheckCircleIcon class="w-4 h-4 inline mr-2" />
                        Approve
                      </button>
                    </MenuItem>
                    <MenuItem v-if="plan.status === 'submitted'" v-slot="{ active }">
                      <button
                        :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-red-700']"
                        @click="openRejectModal(plan)"
                      >
                        <XCircleIcon class="w-4 h-4 inline mr-2" />
                        Reject
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
            :class="[
              'px-3 py-1 rounded',
              page === pagination.current_page ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
            @click="fetchPlans(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div v-if="showDetailModal && selectedPlan" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showDetailModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ (selectedPlan as any).title }}</h2>
          <div class="space-y-4">
            <div>
              <h3 class="font-medium text-gray-700">Objectives</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).objectives || 'No objectives specified' }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700">Introduction</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).introduction || 'No introduction' }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700">Main Content</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).main_content || 'No main content' }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700">Activities</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).activities || 'No activities' }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700">Assessment</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).assessment || 'No assessment' }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700">Conclusion</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).conclusion || 'No conclusion' }}</p>
            </div>
            <div>
              <h3 class="font-medium text-gray-700">Homework</h3>
              <p class="mt-1 text-gray-600 whitespace-pre-wrap">{{ (selectedPlan as any).homework || 'No homework' }}</p>
            </div>
          </div>
          <div class="flex justify-end pt-4">
            <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200" @click="showDetailModal = false">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showRejectModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Reject Lesson Plan</h2>
          <form class="space-y-4" @submit.prevent="rejectPlan">
            <div>
              <label class="block text-sm font-medium text-gray-700">Feedback / Reason for Rejection *</label>
              <textarea v-model="rejectForm.feedback" rows="4" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
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
