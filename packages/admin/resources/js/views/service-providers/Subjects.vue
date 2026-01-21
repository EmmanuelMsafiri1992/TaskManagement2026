<script setup lang="ts">
import { onMounted, ref } from 'vue'
import axios from 'axios'
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { ChevronDownIcon, ChevronRightIcon, EllipsisVerticalIcon, MagnifyingGlassIcon, PlusIcon } from '@heroicons/vue/24/outline'

interface Topic {
  id: number
  name: string
  term: number
  week: number
  description: string | null
  estimated_hours: number | null
  is_active: boolean
}

interface Subject {
  id: number
  name: string
  code: string
  form: string
  description: string | null
  is_active: boolean
  topics_count: number
  topics?: Topic[]
}

interface Stats {
  total_subjects: number
  active_subjects: number
  total_topics: number
  subjects_by_form: {
    form_1: number
    form_2: number
    form_3: number
    form_4: number
  }
}

const subjects = ref<Subject[]>([])
const stats = ref<Stats>({ total_subjects: 0, active_subjects: 0, total_topics: 0, subjects_by_form: { form_1: 0, form_2: 0, form_3: 0, form_4: 0 } })
const loading = ref(true)
const search = ref('')
const formFilter = ref('')
const expandedSubjects = ref<number[]>([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showTopicModal = ref(false)
const selectedSubject = ref<Subject | null>(null)
const selectedTopic = ref<Topic | null>(null)

const subjectForm = ref({
  name: '',
  code: '',
  form: '1',
  description: '',
  is_active: true
})

const topicForm = ref({
  name: '',
  term: 1,
  week: 1,
  description: '',
  estimated_hours: 4,
  is_active: true
})

const fetchSubjects = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page.toString())
    if (search.value) params.append('search', search.value)
    if (formFilter.value) params.append('form', formFilter.value)

    const response = await axios.get(`/api/subjects?${params}`)
    subjects.value = response.data.data
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      per_page: response.data.per_page,
      total: response.data.total
    }
  } catch (error) {
    console.error('Error fetching subjects:', error)
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    const response = await axios.get('/api/subjects/statistics')
    stats.value = response.data
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

const fetchTopics = async (subject: Subject) => {
  try {
    const response = await axios.get(`/api/subjects/${subject.id}/topics`)
    subject.topics = response.data
  } catch (error) {
    console.error('Error fetching topics:', error)
  }
}

const toggleSubject = async (subject: Subject) => {
  const index = expandedSubjects.value.indexOf(subject.id)
  if (index === -1) {
    expandedSubjects.value.push(subject.id)
    if (!subject.topics) {
      await fetchTopics(subject)
    }
  } else {
    expandedSubjects.value.splice(index, 1)
  }
}

const isExpanded = (subjectId: number) => expandedSubjects.value.includes(subjectId)

const createSubject = async () => {
  try {
    await axios.post('/api/subjects', subjectForm.value)
    showCreateModal.value = false
    resetSubjectForm()
    await fetchSubjects()
    await fetchStats()
  } catch (error) {
    console.error('Error creating subject:', error)
  }
}

const updateSubject = async () => {
  if (!selectedSubject.value) return
  try {
    await axios.put(`/api/subjects/${selectedSubject.value.id}`, subjectForm.value)
    showEditModal.value = false
    resetSubjectForm()
    await fetchSubjects()
  } catch (error) {
    console.error('Error updating subject:', error)
  }
}

const deleteSubject = async (subject: Subject) => {
  if (!confirm('Are you sure you want to delete this subject?')) return
  try {
    await axios.delete(`/api/subjects/${subject.id}`)
    await fetchSubjects()
    await fetchStats()
  } catch (error) {
    console.error('Error deleting subject:', error)
  }
}

const openEditModal = (subject: Subject) => {
  selectedSubject.value = subject
  subjectForm.value = {
    name: subject.name,
    code: subject.code,
    form: subject.form,
    description: subject.description || '',
    is_active: subject.is_active
  }
  showEditModal.value = true
}

const openAddTopicModal = (subject: Subject) => {
  selectedSubject.value = subject
  resetTopicForm()
  showTopicModal.value = true
}

const createTopic = async () => {
  if (!selectedSubject.value) return
  try {
    await axios.post(`/api/subjects/${selectedSubject.value.id}/topics`, topicForm.value)
    showTopicModal.value = false
    await fetchTopics(selectedSubject.value)
    await fetchStats()
  } catch (error) {
    console.error('Error creating topic:', error)
  }
}

const deleteTopic = async (subject: Subject, topic: Topic) => {
  if (!confirm('Are you sure you want to delete this topic?')) return
  try {
    await axios.delete(`/api/subjects/${subject.id}/topics/${topic.id}`)
    await fetchTopics(subject)
    await fetchStats()
  } catch (error) {
    console.error('Error deleting topic:', error)
  }
}

const resetSubjectForm = () => {
  subjectForm.value = {
    name: '',
    code: '',
    form: '1',
    description: '',
    is_active: true
  }
  selectedSubject.value = null
}

const resetTopicForm = () => {
  topicForm.value = {
    name: '',
    term: 1,
    week: 1,
    description: '',
    estimated_hours: 4,
    is_active: true
  }
}

onMounted(() => {
  fetchSubjects()
  fetchStats()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Subjects & Topics</h1>
        <p class="mt-1 text-sm text-gray-500">Manage the syllabus subjects and topics</p>
      </div>
      <button
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
        @click="showCreateModal = true"
      >
        <PlusIcon class="w-5 h-5 mr-2" />
        Add Subject
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Total Subjects</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.total_subjects }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Active</div>
        <div class="mt-1 text-2xl font-semibold text-green-600">{{ stats.active_subjects }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Total Topics</div>
        <div class="mt-1 text-2xl font-semibold text-indigo-600">{{ stats.total_topics }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Form 1</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.subjects_by_form?.form_1 || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Form 2</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.subjects_by_form?.form_2 || 0 }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm font-medium text-gray-500">Form 3 & 4</div>
        <div class="mt-1 text-2xl font-semibold text-gray-900">{{ (stats.subjects_by_form?.form_3 || 0) + (stats.subjects_by_form?.form_4 || 0) }}</div>
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
              placeholder="Search by name or code..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
              @input="fetchSubjects(1)"
            />
          </div>
        </div>
        <select
          v-model="formFilter"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
          @change="fetchSubjects(1)"
        >
          <option value="">All Forms</option>
          <option value="1">Form 1</option>
          <option value="2">Form 2</option>
          <option value="3">Form 3</option>
          <option value="4">Form 4</option>
        </select>
      </div>
    </div>

    <!-- Subjects List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="px-6 py-8 text-center text-gray-500">Loading...</div>
      <div v-else-if="subjects.length === 0" class="px-6 py-8 text-center text-gray-500">No subjects found</div>
      <div v-else class="divide-y divide-gray-200">
        <div v-for="subject in subjects" :key="subject.id">
          <!-- Subject Row -->
          <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 cursor-pointer" @click="toggleSubject(subject)">
            <div class="flex items-center space-x-4">
              <component :is="isExpanded(subject.id) ? ChevronDownIcon : ChevronRightIcon" class="w-5 h-5 text-gray-400" />
              <div>
                <div class="flex items-center space-x-2">
                  <span class="font-medium text-gray-900">{{ subject.name }}</span>
                  <span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">{{ subject.code }}</span>
                  <span class="px-2 py-0.5 text-xs rounded bg-indigo-100 text-indigo-600">Form {{ subject.form }}</span>
                  <span v-if="!subject.is_active" class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-600">Inactive</span>
                </div>
                <div class="text-sm text-gray-500">{{ subject.topics_count }} topics</div>
              </div>
            </div>
            <Menu as="div" class="relative" @click.stop>
              <MenuButton class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                <EllipsisVerticalIcon class="w-5 h-5" />
              </MenuButton>
              <MenuItems class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                <div class="py-1">
                  <MenuItem v-slot="{ active }">
                    <button
                      :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                      @click="openAddTopicModal(subject)"
                    >
                      Add Topic
                    </button>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <button
                      :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-gray-700']"
                      @click="openEditModal(subject)"
                    >
                      Edit Subject
                    </button>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <button
                      :class="[active ? 'bg-gray-100' : '', 'block w-full text-left px-4 py-2 text-sm text-red-700']"
                      @click="deleteSubject(subject)"
                    >
                      Delete
                    </button>
                  </MenuItem>
                </div>
              </MenuItems>
            </Menu>
          </div>

          <!-- Topics (Expanded) -->
          <div v-if="isExpanded(subject.id) && subject.topics" class="bg-gray-50 px-6 py-4 pl-16">
            <div v-if="subject.topics.length === 0" class="text-sm text-gray-500">No topics added yet</div>
            <div v-else class="space-y-2">
              <div v-for="topic in subject.topics" :key="topic.id" class="flex items-center justify-between bg-white rounded-lg p-3 shadow-sm">
                <div>
                  <div class="font-medium text-gray-900">{{ topic.name }}</div>
                  <div class="text-sm text-gray-500">
                    Term {{ topic.term }}, Week {{ topic.week }}
                    <span v-if="topic.estimated_hours"> | {{ topic.estimated_hours }}h</span>
                  </div>
                </div>
                <button class="text-red-600 hover:text-red-800 text-sm" @click="deleteTopic(subject, topic)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

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
            @click="fetchSubjects(page)"
          >
            {{ page }}
          </button>
        </div>
      </div>
    </div>

    <!-- Create Subject Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showCreateModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Subject</h2>
          <form class="space-y-4" @submit.prevent="createSubject">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input v-model="subjectForm.name" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Code *</label>
                <input v-model="subjectForm.code" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="e.g., MATH_F1" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Form *</label>
                <select v-model="subjectForm.form" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option value="1">Form 1</option>
                  <option value="2">Form 2</option>
                  <option value="3">Form 3</option>
                  <option value="4">Form 4</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="subjectForm.description" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex items-center">
              <input v-model="subjectForm.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 rounded" />
              <label class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" class="px-4 py-2 text-gray-700 hover:text-gray-900" @click="showCreateModal = false; resetSubjectForm()">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Subject Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showEditModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Edit Subject</h2>
          <form class="space-y-4" @submit.prevent="updateSubject">
            <div>
              <label class="block text-sm font-medium text-gray-700">Name *</label>
              <input v-model="subjectForm.name" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Code *</label>
                <input v-model="subjectForm.code" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Form *</label>
                <select v-model="subjectForm.form" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option value="1">Form 1</option>
                  <option value="2">Form 2</option>
                  <option value="3">Form 3</option>
                  <option value="4">Form 4</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="subjectForm.description" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex items-center">
              <input v-model="subjectForm.is_active" type="checkbox" class="h-4 w-4 text-indigo-600 rounded" />
              <label class="ml-2 text-sm text-gray-700">Active</label>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" class="px-4 py-2 text-gray-700 hover:text-gray-900" @click="showEditModal = false; resetSubjectForm()">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add Topic Modal -->
    <div v-if="showTopicModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black opacity-50" @click="showTopicModal = false"></div>
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Topic to {{ selectedSubject?.name }}</h2>
          <form class="space-y-4" @submit.prevent="createTopic">
            <div>
              <label class="block text-sm font-medium text-gray-700">Topic Name *</label>
              <input v-model="topicForm.name" type="text" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
            </div>
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Term *</label>
                <select v-model="topicForm.term" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg">
                  <option :value="1">Term 1</option>
                  <option :value="2">Term 2</option>
                  <option :value="3">Term 3</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Week *</label>
                <input v-model="topicForm.week" type="number" min="1" max="15" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Est. Hours</label>
                <input v-model="topicForm.estimated_hours" type="number" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea v-model="topicForm.description" rows="2" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
            </div>
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" class="px-4 py-2 text-gray-700 hover:text-gray-900" @click="showTopicModal = false">Cancel</button>
              <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Add Topic</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>
