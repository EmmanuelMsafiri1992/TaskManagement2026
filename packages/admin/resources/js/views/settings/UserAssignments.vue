<template>
  <SettingsLayout>
    <template #topbar>
      <div>
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('User Assignment Management') }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ __('Assign job seekers and employers from V11 database to TaskHub users') }}
        </p>
      </div>
    </template>

    <Loader v-if="loading" size="40" color="#5850ec" class="mx-auto mt-8" />

    <div v-else class="space-y-6">
      <!-- Statistics Overview -->
      <div v-if="statistics" class="rounded-lg bg-white p-6 shadow">
        <h4 class="text-lg font-medium text-gray-900 mb-4">{{ __('Assignment Statistics') }}</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="border-l-4 border-blue-500 pl-4">
            <p class="text-sm text-gray-600">Job Seekers</p>
            <p class="text-2xl font-bold text-gray-900">
              {{ statistics?.job_seekers?.assigned || 0 }} / {{ statistics?.job_seekers?.total || 0 }}
            </p>
            <p class="text-xs text-gray-500">
              {{ statistics?.job_seekers?.unassigned || 0 }} unassigned
            </p>
          </div>
          <div class="border-l-4 border-green-500 pl-4">
            <p class="text-sm text-gray-600">Employers</p>
            <p class="text-2xl font-bold text-gray-900">
              {{ statistics?.employers?.assigned || 0 }} / {{ statistics?.employers?.total || 0 }}
            </p>
            <p class="text-xs text-gray-500">
              {{ statistics?.employers?.unassigned || 0 }} unassigned
            </p>
          </div>
        </div>
      </div>

      <!-- User Assignments Section -->
      <div
        v-for="user in users || []"
        :key="user.id"
        class="rounded-lg bg-white p-6 shadow"
      >
        <div class="mb-6 flex items-center justify-between border-b pb-4">
          <div>
            <h4 class="text-lg font-medium text-gray-900">{{ user.name }}</h4>
            <p class="text-sm text-gray-500">{{ user.email }} • {{ user.role }}</p>
            <div class="mt-2 flex gap-2">
              <span
                v-if="user.focus?.works_with_job_seekers"
                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800"
              >
                Job Seekers: {{ user.job_seekers_count }}
              </span>
              <span
                v-if="user.focus?.works_with_employers"
                class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
              >
                Employers: {{ user.employers_count }}
              </span>
            </div>
          </div>
          <div class="flex gap-3">
            <button
              @click="openFocusModal(user)"
              type="button"
              class="inline-flex items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white hover:bg-purple-700"
            >
              <svg
                class="mr-2 h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              {{ __('Configure Focus') }}
            </button>
            <button
              @click="openAssignModal(user)"
              type="button"
              class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
              <svg
                class="mr-2 h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 4v16m8-8H4"
                />
              </svg>
              {{ __('Assign Users') }}
            </button>
            <button
              @click="viewUserAssignments(user)"
              type="button"
              class="inline-flex items-center rounded-md bg-gray-600 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700"
            >
              <svg
                class="mr-2 h-4 w-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
              {{ __('View Assignments') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Focus Configuration Modal -->
    <Modal :open="showFocusModal" @close="showFocusModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ __('Configure User Focus') }}
        </h3>
        <p class="text-sm text-gray-600 mb-6">
          {{ __('Select which types of users this person will work with') }}
        </p>

        <div class="space-y-4">
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input
                v-model="focusForm.works_with_job_seekers"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
            </div>
            <div class="ml-3">
              <label class="font-medium text-gray-700">
                {{ __('Works with Job Seekers') }}
              </label>
              <p class="text-sm text-gray-500">
                {{ __('This user will be able to work with job seekers') }}
              </p>
            </div>
          </div>

          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input
                v-model="focusForm.works_with_employers"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
            </div>
            <div class="ml-3">
              <label class="font-medium text-gray-700">
                {{ __('Works with Employers') }}
              </label>
              <p class="text-sm text-gray-500">
                {{ __('This user will be able to work with employers') }}
              </p>
            </div>
          </div>

          <div v-if="focusForm.works_with_job_seekers" class="flex items-start pl-7">
            <div class="flex items-center h-5">
              <input
                v-model="focusForm.enable_auto_assign_job_seekers"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
            </div>
            <div class="ml-3">
              <label class="text-sm font-medium text-gray-700">
                {{ __('Enable Auto-Assignment') }}
              </label>
              <p class="text-xs text-gray-500">
                {{ __('Automatically assign new job seekers to this user') }}
              </p>
            </div>
          </div>

          <div v-if="focusForm.works_with_employers" class="flex items-start pl-7">
            <div class="flex items-center h-5">
              <input
                v-model="focusForm.enable_auto_assign_employers"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500"
              />
            </div>
            <div class="ml-3">
              <label class="text-sm font-medium text-gray-700">
                {{ __('Enable Auto-Assignment') }}
              </label>
              <p class="text-xs text-gray-500">
                {{ __('Automatically assign new employers to this user') }}
              </p>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showFocusModal = false"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Cancel') }}
          </button>
          <button
            @click="saveFocus"
            type="button"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
          >
            {{ __('Save Focus') }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Assign Users Modal -->
    <Modal :open="showAssignModal" @close="showAssignModal = false" size="xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ __('Assign Users to') }} {{ selectedUser?.name }}
        </h3>

        <!-- User Type Selection -->
        <div class="mb-4 flex gap-2">
          <button
            @click="assignUserType = 'job_seekers'"
            :class="[
              'flex-1 rounded-md px-4 py-2 text-sm font-medium',
              assignUserType === 'job_seekers'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ __('Job Seekers') }}
          </button>
          <button
            @click="assignUserType = 'employers'"
            :class="[
              'flex-1 rounded-md px-4 py-2 text-sm font-medium',
              assignUserType === 'employers'
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            {{ __('Employers') }}
          </button>
        </div>

        <!-- Search -->
        <div class="mb-4">
          <input
            v-model="searchQuery"
            @input="debounceSearch"
            type="text"
            placeholder="Search by name or email..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          />
        </div>

        <!-- Available Users List -->
        <div class="max-h-96 overflow-y-auto border rounded-md">
          <Loader v-if="loadingAvailableUsers" size="30" color="#5850ec" class="mx-auto my-8" />

          <div v-else-if="availableUsers.length === 0" class="p-8 text-center text-gray-500">
            {{ __('No available users found') }}
          </div>

          <div v-else class="divide-y">
            <div
              v-for="v11User in availableUsers"
              :key="v11User.id"
              class="p-4 hover:bg-gray-50 flex items-center justify-between"
            >
              <div>
                <p class="font-medium text-gray-900">{{ v11User.name }}</p>
                <p class="text-sm text-gray-500">{{ v11User.email }}</p>
                <p class="text-xs text-gray-400">
                  {{ __('Registered') }}: {{ formatDate(v11User.created_at) }}
                </p>
              </div>
              <div>
                <button
                  v-if="!v11User.is_assigned"
                  @click="assignToUser(v11User)"
                  type="button"
                  class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
                >
                  {{ __('Assign') }}
                </button>
                <div v-else class="text-sm">
                  <span class="text-gray-500">{{ __('Assigned to') }}:</span>
                  <span class="font-medium text-gray-900">{{ v11User.assigned_to.name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="mt-4 flex justify-between items-center">
          <button
            @click="previousPage"
            :disabled="pagination.current_page === 1"
            class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300 disabled:opacity-50"
          >
            {{ __('Previous') }}
          </button>
          <span class="text-sm text-gray-600">
            {{ __('Page') }} {{ pagination.current_page }} {{ __('of') }} {{ pagination.last_page }}
          </span>
          <button
            @click="nextPage"
            :disabled="pagination.current_page === pagination.last_page"
            class="rounded-md bg-gray-200 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-300 disabled:opacity-50"
          >
            {{ __('Next') }}
          </button>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="showAssignModal = false"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Close') }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- View Assignments Modal -->
    <Modal :open="showViewModal" @close="showViewModal = false" size="xl">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ __('Assignments for') }} {{ selectedUser?.name }}
        </h3>

        <Loader v-if="loadingAssignments" size="30" color="#5850ec" class="mx-auto my-8" />

        <div v-else-if="userAssignments.length === 0" class="p-8 text-center text-gray-500">
          {{ __('No assignments yet') }}
        </div>

        <div v-else class="space-y-4 max-h-96 overflow-y-auto">
          <div
            v-for="assignment in userAssignments"
            :key="assignment.id"
            class="rounded-lg border border-gray-200 p-4"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <h5 class="text-base font-medium text-gray-900">
                    {{ assignment.v11_user_name }}
                  </h5>
                  <span
                    :class="[
                      'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                      assignment.v11_user_type === 2
                        ? 'bg-blue-100 text-blue-800'
                        : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ assignment.user_type_name }}
                  </span>
                  <span
                    v-if="assignment.auto_assigned"
                    class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800"
                  >
                    {{ __('Auto-assigned') }}
                  </span>
                </div>
                <p class="mt-1 text-sm text-gray-600">{{ assignment.v11_user_email }}</p>
                <p class="mt-1 text-xs text-gray-500">
                  {{ __('Assigned') }}: {{ formatDate(assignment.assigned_at) }}
                  <span v-if="assignment.assigned_by"> • {{ __('By') }}: {{ assignment.assigned_by }}</span>
                </p>
                <p v-if="assignment.last_contacted_at" class="text-xs text-gray-500">
                  {{ __('Last contacted') }}: {{ formatDate(assignment.last_contacted_at) }}
                </p>
                <div v-if="assignment.notes" class="mt-2 rounded bg-yellow-50 p-2">
                  <p class="text-xs text-gray-700">{{ assignment.notes }}</p>
                </div>
              </div>
              <div class="flex flex-col gap-2">
                <a
                  v-if="assignment.task_id"
                  :href="`/projects/${assignment.task?.project_id}/tasks/${assignment.task_id}`"
                  class="rounded-md bg-indigo-100 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-200 text-center"
                >
                  {{ __('View Task') }}
                </a>
                <button
                  @click="openNotesModal(assignment)"
                  type="button"
                  class="rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 hover:bg-blue-200"
                >
                  {{ __('Add Note') }}
                </button>
                <button
                  @click="unassignUser(assignment)"
                  type="button"
                  class="rounded-md bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-200"
                >
                  {{ __('Unassign') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="showViewModal = false"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Close') }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Notes Modal -->
    <Modal :open="showNotesModal" @close="showNotesModal = false">
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ __('Add Contact Note') }}
        </h3>

        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ __('Notes') }}
            </label>
            <textarea
              v-model="notesForm.notes"
              rows="4"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              placeholder="Add notes about your contact with this user..."
            ></textarea>
          </div>

          <div class="flex items-center">
            <input
              v-model="notesForm.contacted"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label class="ml-2 text-sm text-gray-700">
              {{ __('Mark as contacted today') }}
            </label>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            @click="showNotesModal = false"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Cancel') }}
          </button>
          <button
            @click="saveNotes"
            type="button"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
          >
            {{ __('Save Note') }}
          </button>
        </div>
      </div>
    </Modal>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, inject, watch } from 'vue'
import SettingsLayout from './SettingsLayout.vue'
import { Loader } from 'thetheme'
import { axios } from 'spack'

const __ = inject('__') as any

const loading = ref(true)
const users = ref([])
const statistics = ref(null)

// Focus Modal
const showFocusModal = ref(false)
const selectedUser = ref(null)
const focusForm = ref({
  works_with_job_seekers: false,
  works_with_employers: false,
  enable_auto_assign_job_seekers: false,
  enable_auto_assign_employers: false
})

// Assign Modal
const showAssignModal = ref(false)
const assignUserType = ref('job_seekers')
const availableUsers = ref([])
const loadingAvailableUsers = ref(false)
const searchQuery = ref('')
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

// View Assignments Modal
const showViewModal = ref(false)
const userAssignments = ref([])
const loadingAssignments = ref(false)

// Notes Modal
const showNotesModal = ref(false)
const selectedAssignment = ref(null)
const notesForm = ref({
  notes: '',
  contacted: false
})

let searchTimeout: number | null = null

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  loading.value = true
  try {
    const usersResponse = await axios.get('settings/user-assignments/')
    const statsResponse = await axios.get('settings/user-assignments/statistics')

    users.value = usersResponse.data.data
    statistics.value = statsResponse.data.data
  } catch (error) {
    console.error('Failed to load user assignments:', error)
    alert('Failed to load user assignments.')
  } finally {
    loading.value = false
  }
}

const openFocusModal = (user: any) => {
  selectedUser.value = user
  focusForm.value = {
    works_with_job_seekers: user.focus?.works_with_job_seekers || false,
    works_with_employers: user.focus?.works_with_employers || false,
    enable_auto_assign_job_seekers: user.focus?.enable_auto_assign_job_seekers || false,
    enable_auto_assign_employers: user.focus?.enable_auto_assign_employers || false
  }
  showFocusModal.value = true
}

const saveFocus = async () => {
  try {
    await axios.put(`settings/user-assignments/users/${selectedUser.value.id}/focus`, focusForm.value)
    alert('User focus updated successfully!')
    showFocusModal.value = false
    await loadData()
  } catch (error) {
    console.error('Failed to save focus:', error)
    alert('Failed to save user focus')
  }
}

const openAssignModal = async (user: any) => {
  selectedUser.value = user
  assignUserType.value = 'job_seekers'
  searchQuery.value = ''
  pagination.value.current_page = 1
  showAssignModal.value = true
  await loadAvailableUsers()
}

const loadAvailableUsers = async () => {
  loadingAvailableUsers.value = true
  try {
    const response = await axios.get('settings/user-assignments/available-users', {
      params: {
        type: assignUserType.value,
        page: pagination.value.current_page,
        per_page: pagination.value.per_page,
        search: searchQuery.value
      }
    })

    availableUsers.value = response.data.data
    pagination.value = response.data.meta
  } catch (error) {
    console.error('Failed to load available users:', error)
    alert('Failed to load available users')
  } finally {
    loadingAvailableUsers.value = false
  }
}

const debounceSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadAvailableUsers()
  }, 500)
}

const assignToUser = async (v11User: any) => {
  try {
    await axios.post('settings/user-assignments/assign', {
      taskhub_user_id: selectedUser.value.id,
      v11_user_id: v11User.id,
      v11_user_type: v11User.user_type
    })

    alert('User assigned successfully!')
    await loadAvailableUsers()
    await loadData()
  } catch (error: any) {
    console.error('Failed to assign user:', error)
    alert(error.response?.data?.message || 'Failed to assign user')
  }
}

const viewUserAssignments = async (user: any) => {
  selectedUser.value = user
  showViewModal.value = true
  loadingAssignments.value = true

  try {
    const response = await axios.get(`settings/user-assignments/users/${user.id}/assignments`)
    userAssignments.value = response.data.data
  } catch (error) {
    console.error('Failed to load assignments:', error)
    alert('Failed to load user assignments')
  } finally {
    loadingAssignments.value = false
  }
}

const unassignUser = async (assignment: any) => {
  if (!confirm('Are you sure you want to unassign this user?')) {
    return
  }

  try {
    await axios.delete(`settings/user-assignments/assignments/${assignment.id}`)
    alert('User unassigned successfully!')
    await viewUserAssignments(selectedUser.value)
    await loadData()
  } catch (error) {
    console.error('Failed to unassign user:', error)
    alert('Failed to unassign user')
  }
}

const openNotesModal = (assignment: any) => {
  selectedAssignment.value = assignment
  notesForm.value = {
    notes: assignment.notes || '',
    contacted: false
  }
  showNotesModal.value = true
}

const saveNotes = async () => {
  try {
    await axios.put(`settings/user-assignments/assignments/${selectedAssignment.value.id}/notes`, notesForm.value)
    alert('Notes saved successfully!')
    showNotesModal.value = false
    await viewUserAssignments(selectedUser.value)
  } catch (error) {
    console.error('Failed to save notes:', error)
    alert('Failed to save notes')
  }
}

const previousPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--
    loadAvailableUsers()
  }
}

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++
    loadAvailableUsers()
  }
}

const formatDate = (date: string) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Watch for user type change
watch(assignUserType, () => {
  pagination.value.current_page = 1
  loadAvailableUsers()
})
</script>

<script lang="ts">
// Modal component
import { defineComponent, h } from 'vue'

const Modal = defineComponent({
  name: 'Modal',
  props: {
    open: Boolean,
    size: {
      type: String,
      default: 'lg'
    }
  },
  emits: ['close'],
  setup(props, { slots, emit }) {
    return () => {
      if (!props.open) return null

      const sizeClass = props.size === 'xl' ? 'max-w-4xl' : 'max-w-2xl'

      return h('div', { class: 'fixed inset-0 z-50 overflow-y-auto' }, [
        h('div', { class: 'flex min-h-screen items-center justify-center px-4' }, [
          h('div', {
            class: 'fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity',
            onClick: () => emit('close'),
          }),
          h('div', { class: `relative w-full ${sizeClass} rounded-lg bg-white shadow-xl` }, [
            slots.default?.()
          ]),
        ]),
      ])
    }
  },
})
</script>
