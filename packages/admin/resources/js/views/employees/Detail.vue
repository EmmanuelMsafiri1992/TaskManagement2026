<template>
  <div v-if="loading" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else-if="employee" class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <button
          @click="$router.back()"
          class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
        >
          ← {{ __('Back') }}
        </button>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ employee.user.name }}
        </h1>
      </div>
      <div class="flex space-x-2">
        <TheButton
          v-if="can('employee:update')"
          variant="secondary"
          @click="openEditModal"
        >
          {{ __('Edit') }}
        </TheButton>
      </div>
    </div>

    <!-- Status Badges -->
    <div class="flex space-x-2">
      <span
        class="inline-flex rounded-full px-3 py-1 text-sm font-semibold"
        :class="{
          'bg-green-100 text-green-800': employee.employment_status === 'Active',
          'bg-red-100 text-red-800': ['Resigned', 'Terminated'].includes(employee.employment_status),
          'bg-gray-100 text-gray-800': !['Active', 'Resigned', 'Terminated'].includes(employee.employment_status)
        }"
      >
        {{ employee.employment_status }}
      </span>
      <span
        class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-sm font-semibold text-blue-800"
      >
        {{ employee.employment_type }}
      </span>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <!-- Left Column - Main Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ __('Personal Information') }}
            </h3>
          </div>
          <div class="border-t border-gray-200">
            <dl>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Email') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.user.email }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('National ID') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.national_id || '-' }}
                </dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.phone_number || '-' }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Address') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.physical_address || '-' }}
                </dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Date of Birth') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.date_of_birth ? new Date(employee.date_of_birth).toLocaleDateString() : '-' }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Gender') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.gender || '-' }}
                </dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Marital Status') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.marital_status || '-' }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Employment Information -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ __('Employment Information') }}
            </h3>
          </div>
          <div class="border-t border-gray-200">
            <dl>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Position') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.position || '-' }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Department') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.department || '-' }}
                </dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Reports To') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.supervisor?.name || '-' }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Employment Date') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.employment_date ? new Date(employee.employment_date).toLocaleDateString() : '-' }}
                </dd>
              </div>
              <div v-if="employee.contract_start_date" class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Contract Period') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ new Date(employee.contract_start_date).toLocaleDateString() }} -
                  {{ employee.contract_end_date ? new Date(employee.contract_end_date).toLocaleDateString() : 'Ongoing' }}
                </dd>
              </div>
              <div v-if="employee.probation_end_date" class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Probation End Date') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ new Date(employee.probation_end_date).toLocaleDateString() }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

        <!-- Next of Kin -->
        <div v-if="employee.next_of_kin_name" class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ __('Next of Kin') }}
            </h3>
          </div>
          <div class="border-t border-gray-200">
            <dl>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.next_of_kin_name }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Relationship') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.next_of_kin_relationship || '-' }}
                </dd>
              </div>
              <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Phone') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.next_of_kin_phone || '-' }}
                </dd>
              </div>
              <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">{{ __('Address') }}</dt>
                <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                  {{ employee.next_of_kin_address || '-' }}
                </dd>
              </div>
            </dl>
          </div>
        </div>
      </div>

      <!-- Right Column - Stats & Quick Info -->
      <div class="space-y-6">
        <!-- Leave Balance Card -->
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="mb-4 text-base font-medium text-gray-900">
              {{ __('Leave Balance') }}
            </h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">{{ __('Annual Leave') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                  {{ employee.leave_balance_annual }} / {{ employee.annual_leave_days }} {{ __('days') }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">{{ __('Sick Leave') }}</span>
                <span class="text-sm font-semibold text-gray-900">
                  {{ employee.leave_balance_sick }} / {{ employee.sick_leave_days }} {{ __('days') }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Salary Card -->
        <div v-if="employee.current_salary" class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="mb-2 text-base font-medium text-gray-900">
              {{ __('Current Salary') }}
            </h3>
            <p class="text-2xl font-bold text-gray-900">
              {{ new Intl.NumberFormat().format(employee.current_salary) }} {{ employee.salary_currency }}
            </p>
          </div>
        </div>

        <!-- Tax & Pension -->
        <div v-if="employee.tax_identification_number || employee.pension_number" class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="mb-4 text-base font-medium text-gray-900">
              {{ __('Tax & Pension') }}
            </h3>
            <div class="space-y-3">
              <div v-if="employee.tax_identification_number">
                <span class="text-sm text-gray-500">{{ __('TIN') }}</span>
                <p class="text-sm font-semibold text-gray-900">
                  {{ employee.tax_identification_number }}
                </p>
              </div>
              <div v-if="employee.pension_number">
                <span class="text-sm text-gray-500">{{ __('Pension Number') }}</span>
                <p class="text-sm font-semibold text-gray-900">
                  {{ employee.pension_number }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="employee.notes" class="overflow-hidden bg-white shadow sm:rounded-lg">
          <div class="px-4 py-5 sm:p-6">
            <h3 class="mb-2 text-base font-medium text-gray-900">
              {{ __('Notes') }}
            </h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">
              {{ employee.notes }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Modal -->
    <FormModal v-model="editModalOpen" size="2xl" @saved="loadEmployee">
      <Form :model-value="employee" @close="editModalOpen = false" />
    </FormModal>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import TheButton from '@/thetheme/components/TheButton.vue'
import FormModal from '@/thetheme/components/FormModal.vue'
import Form from './Form.vue'
import { can } from '@/helpers'

const route = useRoute()
const loading = ref(true)
const employee = ref(null)
const editModalOpen = ref(false)

const loadEmployee = async () => {
  try {
    loading.value = true
    const response = await axios.get(`employees/${route.params.id}`)
    employee.value = response.data.data
  } catch (error) {
    console.error('Failed to load employee:', error)
  } finally {
    loading.value = false
  }
}

const openEditModal = () => {
  editModalOpen.value = true
}

onMounted(() => {
  loadEmployee()
})
</script>
