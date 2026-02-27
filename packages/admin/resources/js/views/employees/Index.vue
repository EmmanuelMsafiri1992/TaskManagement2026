<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else class="flex">
    <!-- Main Content -->
    <div :class="['flex-1 transition-all duration-300', selectedEmployee ? 'mr-96' : '']">
      <!-- Statistics Cards -->
      <div v-if="statistics" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <UsersIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">
                    {{ __('Total Team Members') }}
                  </dt>
                  <dd class="text-lg font-semibold text-gray-900">
                    {{ statistics.total_employees }}
                  </dd>
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
                  <dt class="truncate text-sm font-medium text-gray-500">
                    {{ __('Active') }}
                  </dt>
                  <dd class="text-lg font-semibold text-gray-900">
                    {{ statistics.active_employees }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-yellow-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">
                    {{ __('On Probation') }}
                  </dt>
                  <dd class="text-lg font-semibold text-gray-900">
                    {{ statistics.on_probation }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-6 w-6 text-orange-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">
                    {{ __('Without HR Details') }}
                  </dt>
                  <dd class="text-lg font-semibold text-gray-900">
                    {{ statistics.without_records }}
                  </dd>
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
              :placeholder="__('Search team members...')"
              class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
              @input="handleSearch"
            />
          </div>

          <select
            v-model="index.params.has_employee_record"
            class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="index.get()"
          >
            <option value="">{{ __('All Members') }}</option>
            <option value="yes">{{ __('With HR Details') }}</option>
            <option value="no">{{ __('Without HR Details') }}</option>
          </select>

          <select
            v-model="index.params.employment_status"
            class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="index.get()"
          >
            <option value="">{{ __('All Statuses') }}</option>
            <option v-for="status in filters.employment_statuses" :key="status" :value="status">
              {{ __(status) }}
            </option>
          </select>

          <select
            v-model="index.params.department"
            class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="index.get()"
          >
            <option value="">{{ __('All Departments') }}</option>
            <option v-for="dept in filters.departments" :key="dept" :value="dept">
              {{ dept }}
            </option>
          </select>

          <select
            v-model="index.params.archived"
            class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="index.get()"
          >
            <option value="">{{ __('Active Members') }}</option>
            <option value="true">{{ __('Archived Members') }}</option>
          </select>
        </div>

        <div class="flex gap-2 ltr:ml-auto rtl:mr-auto">
          <TheButton
            v-if="can('user:create')"
            size="sm"
            variant="secondary"
            @click="openInviteModal()"
          >
            {{ __('Invite Member') }}
          </TheButton>
        </div>
      </div>

      <!-- Team Members Table -->
      <section>
        <div class="flex flex-col">
          <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
              <div class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead>
                    <tr>
                      <TableTh
                        name="employee"
                        :index="index"
                        :label="__('Name')"
                        sort="name"
                      />
                      <TableTh
                        name="employee"
                        :index="index"
                        :label="__('Role')"
                      />
                      <TableTh
                        name="employee"
                        :index="index"
                        :label="__('Position')"
                      />
                      <TableTh
                        name="employee"
                        :index="index"
                        :label="__('Department')"
                      />
                      <TableTh
                        name="employee"
                        :index="index"
                        :label="__('Status')"
                      />
                      <th class="bg-gray-50 px-6 py-3"></th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200 bg-white">
                    <tr
                      v-for="employee in index.data.data"
                      :key="employee.id"
                      class="cursor-pointer transition-colors"
                      :class="selectedEmployee?.id === employee.id ? 'bg-indigo-50' : 'hover:bg-gray-50'"
                      @click="selectEmployee(employee)"
                    >
                      <td class="whitespace-nowrap px-6 py-4">
                        <div class="flex items-center">
                          <div class="h-10 w-10 flex-shrink-0">
                            <UserAvatar :user="employee.user" size="10" />
                          </div>
                          <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">
                              {{ employee.user.name }}
                            </div>
                            <div class="text-sm text-gray-500">
                              {{ employee.user.email }}
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                        <div class="flex flex-wrap gap-1">
                          <span
                            v-for="role in employee.roles"
                            :key="role"
                            class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700"
                          >
                            {{ role }}
                          </span>
                          <span v-if="!employee.roles?.length" class="text-gray-400">-</span>
                        </div>
                      </td>
                      <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                        {{ employee.position || '-' }}
                      </td>
                      <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                        {{ employee.department || '-' }}
                      </td>
                      <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                        <span
                          v-if="employee.is_archived"
                          class="inline-flex rounded-full bg-gray-200 px-2 text-xs font-semibold leading-5 text-gray-600"
                        >
                          {{ __('Archived') }}
                        </span>
                        <span
                          v-else-if="employee.has_employee_record"
                          class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                          :class="{
                            'bg-green-100 text-green-800': employee.employment_status === 'Active',
                            'bg-yellow-100 text-yellow-800': employee.employment_status === 'On Leave',
                            'bg-red-100 text-red-800': ['Resigned', 'Terminated'].includes(employee.employment_status),
                            'bg-gray-100 text-gray-800': ['Retired', 'Deceased', 'Suspended', 'Not Set'].includes(employee.employment_status)
                          }"
                        >
                          {{ employee.employment_status }}
                        </span>
                        <span
                          v-else
                          class="inline-flex rounded-full bg-orange-100 px-2 text-xs font-semibold leading-5 text-orange-800"
                        >
                          {{ __('No HR Details') }}
                        </span>
                      </td>
                      <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                        <EyeIcon
                          class="w-5 cursor-pointer text-indigo-400 hover:text-indigo-600"
                          :title="__('View Details')"
                          @click.stop="selectEmployee(employee)"
                        />
                        <PencilIcon
                          v-if="(can('employee:update') || can('user:update')) && !employee.is_archived"
                          class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                          :title="__('Edit')"
                          @click.stop="openEmployeeModal(employee)"
                        />
                        <ArchiveBoxIcon
                          v-if="can('user:delete') && !employee.is_archived && employee.id !== 1"
                          class="ml-2 w-5 cursor-pointer text-orange-400 hover:text-orange-600"
                          :title="__('Archive')"
                          @click.stop="archiveEmployee(employee)"
                        />
                        <ArchiveBoxXMarkIcon
                          v-if="can('user:delete') && employee.is_archived"
                          class="ml-2 w-5 cursor-pointer text-green-400 hover:text-green-600"
                          :title="__('Restore')"
                          @click.stop="unarchiveEmployee(employee)"
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

      <!-- Employee Form Modal -->
      <FormModal v-if="form.show" size="2xl" @saved="index.get()" @close="form.show = false">
        <Form :model-value="form.model" @close="form.show = false" />
      </FormModal>
    </div>

    <!-- Slide-out Side Panel -->
    <transition
      enter-active-class="transition-transform duration-300 ease-out"
      enter-from-class="translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300 ease-in"
      leave-from-class="translate-x-0"
      leave-to-class="translate-x-full"
    >
      <div
        v-if="selectedEmployee"
        class="fixed right-0 top-0 h-full w-96 bg-white shadow-2xl overflow-y-auto z-40 border-l border-gray-200"
        style="top: 64px; height: calc(100vh - 64px);"
      >
        <!-- Panel Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
          <h2 class="text-lg font-semibold text-gray-900">{{ __('Team Member Details') }}</h2>
          <button
            class="p-1 rounded-md hover:bg-gray-100 text-gray-400 hover:text-gray-600"
            @click="selectedEmployee = null"
          >
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <!-- Panel Content -->
        <div class="p-6 space-y-6">
          <!-- Employee Header -->
          <div class="text-center pb-4 border-b border-gray-100">
            <div class="flex justify-center mb-3">
              <UserAvatar :user="selectedEmployee.user" size="20" />
            </div>
            <h3 class="text-xl font-bold text-gray-900">{{ selectedEmployee.user.name }}</h3>
            <p class="text-sm text-gray-500">{{ selectedEmployee.user.email }}</p>
            <div class="flex flex-wrap justify-center gap-2 mt-3">
              <span
                v-for="role in selectedEmployee.roles"
                :key="role"
                class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800"
              >
                {{ role }}
              </span>
            </div>
            <div class="flex justify-center gap-2 mt-3">
              <span
                v-if="selectedEmployee.has_employee_record"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-800': selectedEmployee.employment_status === 'Active',
                  'bg-yellow-100 text-yellow-800': selectedEmployee.employment_status === 'On Leave',
                  'bg-red-100 text-red-800': ['Resigned', 'Terminated'].includes(selectedEmployee.employment_status),
                  'bg-gray-100 text-gray-800': ['Retired', 'Deceased', 'Suspended', 'Not Set'].includes(selectedEmployee.employment_status)
                }"
              >
                {{ selectedEmployee.employment_status }}
              </span>
              <span
                v-if="selectedEmployee.has_employee_record && selectedEmployee.employment_type !== 'Not Set'"
                class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                :class="{
                  'bg-green-100 text-green-800': selectedEmployee.employment_type === 'Permanent',
                  'bg-blue-100 text-blue-800': selectedEmployee.employment_type === 'Contract',
                  'bg-yellow-100 text-yellow-800': selectedEmployee.employment_type === 'Probation',
                  'bg-gray-100 text-gray-800': ['Casual', 'Internship'].includes(selectedEmployee.employment_type)
                }"
              >
                {{ selectedEmployee.employment_type }}
              </span>
            </div>
          </div>

          <!-- No HR Details Warning -->
          <div v-if="!selectedEmployee.has_employee_record" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
            <div class="flex">
              <ExclamationTriangleIcon class="h-5 w-5 text-orange-400" />
              <div class="ml-3">
                <h3 class="text-sm font-medium text-orange-800">{{ __('No HR Details') }}</h3>
                <p class="mt-1 text-sm text-orange-700">
                  {{ __('This team member does not have HR details set up yet. Click "Edit" to add employment information.') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Employment Information -->
          <div v-if="selectedEmployee.has_employee_record">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3 flex items-center">
              <BriefcaseIcon class="h-4 w-4 mr-2 text-gray-400" />
              {{ __('Employment Information') }}
            </h4>
            <dl class="space-y-2">
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Position') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.position || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Department') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.department || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Reports To') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.supervisor?.name || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Employment Date') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ formatDate(selectedEmployee.employment_date) }}</dd>
              </div>
              <div v-if="selectedEmployee.employment_type === 'Contract'" class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Contract Period') }}</dt>
                <dd class="text-sm font-medium text-gray-900">
                  {{ formatDate(selectedEmployee.contract_start_date) }} - {{ formatDate(selectedEmployee.contract_end_date) }}
                </dd>
              </div>
            </dl>
          </div>

          <!-- Personal Information -->
          <div v-if="selectedEmployee.has_employee_record">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3 flex items-center">
              <UserIcon class="h-4 w-4 mr-2 text-gray-400" />
              {{ __('Personal Information') }}
            </h4>
            <dl class="space-y-2">
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Phone') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.phone_number || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('National ID') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.national_id || '-' }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Date of Birth') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ formatDate(selectedEmployee.date_of_birth) }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Gender') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.gender || '-' }}</dd>
              </div>
            </dl>
          </div>

          <!-- Financial Information -->
          <div v-if="selectedEmployee.has_employee_record && selectedEmployee.current_salary">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3 flex items-center">
              <BanknotesIcon class="h-4 w-4 mr-2 text-gray-400" />
              {{ __('Financial Information') }}
            </h4>
            <dl class="space-y-2">
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Salary') }}</dt>
                <dd class="text-sm font-bold text-green-600">{{ formatCurrency(selectedEmployee.current_salary, selectedEmployee.salary_currency) }}</dd>
              </div>
              <div class="flex justify-between py-2 border-b border-gray-50">
                <dt class="text-sm text-gray-500">{{ __('Tax ID (TIN)') }}</dt>
                <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.tax_identification_number || '-' }}</dd>
              </div>
            </dl>
          </div>

          <!-- Leave Balances -->
          <div v-if="selectedEmployee.has_employee_record">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3 flex items-center">
              <CalendarDaysIcon class="h-4 w-4 mr-2 text-gray-400" />
              {{ __('Leave Balances') }}
            </h4>
            <div class="grid grid-cols-2 gap-3">
              <div class="bg-blue-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ selectedEmployee.leave_balance_annual ?? 0 }}</p>
                <p class="text-xs text-gray-500">{{ __('Annual Leave') }}</p>
                <p class="text-xs text-gray-400">{{ __('of') }} {{ selectedEmployee.annual_leave_days ?? 0 }} {{ __('days') }}</p>
              </div>
              <div class="bg-amber-50 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-amber-600">{{ selectedEmployee.leave_balance_sick ?? 0 }}</p>
                <p class="text-xs text-gray-500">{{ __('Sick Leave') }}</p>
                <p class="text-xs text-gray-400">{{ __('of') }} {{ selectedEmployee.sick_leave_days ?? 0 }} {{ __('days') }}</p>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-4 border-t border-gray-200 space-y-2">
            <button
              v-if="can('employee:update') || can('user:update')"
              class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
              @click="openEmployeeModal(selectedEmployee)"
            >
              <PencilIcon class="h-4 w-4" />
              {{ selectedEmployee.has_employee_record ? __('Edit Details') : __('Add HR Details') }}
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Overlay when panel is open -->
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="selectedEmployee"
        class="fixed inset-0 bg-black bg-opacity-20 z-30"
        style="top: 64px;"
        @click="selectedEmployee = null"
      ></div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ArchiveBoxIcon, ArchiveBoxXMarkIcon, BanknotesIcon, BriefcaseIcon, CalendarDaysIcon, CheckCircleIcon, ClockIcon, ExclamationTriangleIcon, EyeIcon, MagnifyingGlassIcon, PencilIcon, UserIcon, UsersIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import { useModalsStore } from 'spack'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Form from './Form.vue'
import InvitationForm from '../users/InvitationForm.vue'
import { useIndex } from '@/composables/useIndex'
import { can } from '@/helpers'

const processing = ref(true)
const statistics = ref(null)
const selectedEmployee = ref(null)
const filters = ref({
  departments: [],
  employment_types: [],
  employment_statuses: []
})

const index = useIndex('employees', {
  search: '',
  employment_status: '',
  employment_type: '',
  department: '',
  has_employee_record: '',
  archived: '',
  sort_by: 'name',
  sort_order: 'asc',
  per_page: 15
})

const form = reactive({
  show: false,
  model: null
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
    minimumFractionDigits: 2
  }).format(amount)
}

let searchTimeout = null
const handleSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    index.get()
  }, 300)
}

const selectEmployee = (employee) => {
  selectedEmployee.value = employee
}

const openEmployeeModal = (employee = null) => {
  form.model = employee
  form.show = true
}

const openInviteModal = () => {
  useModalsStore().add(InvitationForm)
}

const archiveEmployee = async (employee) => {
  if (!confirm(__('Are you sure you want to archive this team member? They will no longer appear in the active list.'))) {
    return
  }
  try {
    await axios.post(`users/${employee.id}/archive`)
    await index.get()
    await loadStatistics()
    selectedEmployee.value = null
  } catch (error) {
    console.error('Failed to archive employee:', error)
    alert(__('Failed to archive team member'))
  }
}

const unarchiveEmployee = async (employee) => {
  if (!confirm(__('Are you sure you want to restore this team member?'))) {
    return
  }
  try {
    await axios.post(`users/${employee.id}/unarchive`)
    await index.get()
    await loadStatistics()
    selectedEmployee.value = null
  } catch (error) {
    console.error('Failed to unarchive employee:', error)
    alert(__('Failed to restore team member'))
  }
}

const loadStatistics = async () => {
  try {
    const response = await axios.get('employees/statistics')
    statistics.value = response.data.data
  } catch (error) {
    console.error('Failed to load statistics:', error)
  }
}

onMounted(async () => {
  try {
    await index.get()

    await loadStatistics()

    if (index.data && index.data.filters) {
      filters.value = index.data.filters
    }

    processing.value = false
  } catch (error) {
    console.error('Error in onMounted:', error)
    processing.value = false
  }
})
</script>
