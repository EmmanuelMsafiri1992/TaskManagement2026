<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
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
                  {{ __('Total Employees') }}
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
              <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">
                  {{ __('Contracts Expiring') }}
                </dt>
                <dd class="text-lg font-semibold text-gray-900">
                  {{ statistics.contracts_expiring_soon }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-1 gap-2">
        <div class="relative w-64 rounded-md shadow-sm">
          <div class="pointer-events-none absolute inset-y-0 flex items-center ltr:left-0 ltr:pl-3 rtl:right-0 rtl:pr-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            v-model="index.params.search"
            type="search"
            :placeholder="__('Search employees...')"
            class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
            @input="handleSearch"
          />
        </div>

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
          v-model="index.params.employment_type"
          class="block rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          @change="index.get()"
        >
          <option value="">{{ __('All Types') }}</option>
          <option v-for="type in filters.employment_types" :key="type" :value="type">
            {{ __(type) }}
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
      </div>

      <div class="ltr:ml-auto rtl:mr-auto">
        <TheButton
          v-if="can('employee:create')"
          size="sm"
          @click="openEmployeeModal()"
        >
          {{ __('Add Employee') }}
        </TheButton>
      </div>
    </div>

    <!-- Employees Table -->
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
                      :label="__('Phone')"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Position')"
                      sort="position"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Department')"
                      sort="department"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Reports To')"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Type')"
                      sort="employment_type"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Status')"
                      sort="employment_status"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Salary')"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Leave Balance')"
                    />
                    <TableTh
                      name="employee"
                      :index="index"
                      :label="__('Hired')"
                      sort="employment_date"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr
                    v-for="employee in index.data.data"
                    :key="employee.id"
                    class="hover:bg-gray-50"
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
                      {{ employee.phone_number || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ employee.position || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ employee.department || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ employee.supervisor?.name || '-' }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-green-100 text-green-800': employee.employment_type === 'Permanent',
                          'bg-blue-100 text-blue-800': employee.employment_type === 'Contract',
                          'bg-yellow-100 text-yellow-800': employee.employment_type === 'Probation',
                          'bg-gray-100 text-gray-800': ['Casual', 'Internship'].includes(employee.employment_type)
                        }"
                      >
                        {{ employee.employment_type }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span
                        class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                        :class="{
                          'bg-green-100 text-green-800': employee.employment_status === 'Active',
                          'bg-yellow-100 text-yellow-800': employee.employment_status === 'On Leave',
                          'bg-red-100 text-red-800': ['Resigned', 'Terminated'].includes(employee.employment_status),
                          'bg-gray-100 text-gray-800': ['Retired', 'Deceased', 'Suspended'].includes(employee.employment_status)
                        }"
                      >
                        {{ employee.employment_status }}
                      </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <span v-if="employee.current_salary" class="font-medium">
                        {{ formatCurrency(employee.current_salary, employee.salary_currency) }}
                      </span>
                      <span v-else>-</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      <div class="text-xs">
                        <div>{{ __('Annual') }}: {{ employee.leave_balance_annual ?? 0 }}/{{ employee.annual_leave_days ?? 0 }}</div>
                        <div>{{ __('Sick') }}: {{ employee.leave_balance_sick ?? 0 }}/{{ employee.sick_leave_days ?? 0 }}</div>
                      </div>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                      {{ formatDate(employee.employment_date) }}
                    </td>
                    <td class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5">
                      <UserIcon
                        v-if="canImpersonate(employee)"
                        class="w-5 cursor-pointer text-indigo-400 hover:text-indigo-600"
                        :title="__('Login as this user')"
                        @click.stop="impersonateUser(employee)"
                      />
                      <PencilIcon
                        v-if="can('employee:update')"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.stop="openEmployeeModal(employee)"
                      />
                      <TrashIcon
                        v-if="can('employee:delete')"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.stop="index.deleteIt(employee.id)"
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
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { CheckCircleIcon, ClockIcon, ExclamationTriangleIcon, MagnifyingGlassIcon, PencilIcon, TrashIcon, UsersIcon, UserIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import TableTh from '@/thetheme/components/TableTh.vue'
import IndexPagination from '@/thetheme/components/IndexPagination.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Form from './Form.vue'
import { useIndex } from '@/composables/useIndex'
import { can } from '@/helpers'
import { appData } from '@/app-data'

const processing = ref(true)
const statistics = ref(null)
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
  sort_by: 'created_at',
  sort_order: 'desc',
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

const openEmployeeModal = (employee = null) => {
  form.model = employee
  form.show = true
}

const canImpersonate = (employee) => {
  // Only super admins can impersonate
  if (!appData.is_super_admin) return false
  // Cannot impersonate yourself
  if (employee.user_id === appData.user?.id) return false
  return true
}

const impersonateUser = async (employee) => {
  if (!confirm(`Are you sure you want to login as ${employee.user?.name || 'this user'}?`)) {
    return
  }

  try {
    const response = await axios.post(`impersonate/${employee.user_id}`)
    alert(response.data.message)
    // Full page redirect to get fresh session and CSRF token
    window.location.href = '/'
  } catch (error) {
    console.error('Impersonate error:', error)
    alert(error.response?.data?.message || 'Failed to impersonate user')
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
  console.log('[Employees] Component mounted')
  try {
    console.log('[Employees] Fetching employees...')
    await index.get()
    console.log('[Employees] Employees fetched:', index.data)

    console.log('[Employees] Fetching statistics...')
    await loadStatistics()
    console.log('[Employees] Statistics fetched:', statistics.value)

    if (index.data && index.data.filters) {
      filters.value = index.data.filters
      console.log('[Employees] Filters set:', filters.value)
    }

    processing.value = false
    console.log('[Employees] Processing complete')
  } catch (error) {
    console.error('[Employees] ERROR in onMounted:', error)
    processing.value = false
  }
})
</script>
