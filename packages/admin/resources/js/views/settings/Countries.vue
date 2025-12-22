<template>
  <SettingsLayout>
    <template #topbar>
      <div>
        <h3 class="text-lg font-medium leading-6 text-gray-900">
          {{ __('Countries & Websites Management') }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ __('Assign countries and websites to users for marketing and AdSense reporting') }}
        </p>
      </div>
    </template>

    <Loader v-if="loading" size="40" color="#5850ec" class="mx-auto mt-8" />

    <div v-else class="space-y-6">
      <!-- User Assignments Section -->
      <div
        v-for="user in users"
        :key="user.id"
        class="rounded-lg bg-white p-6 shadow"
      >
        <div class="mb-6 flex items-center justify-between border-b pb-4">
          <div>
            <h4 class="text-lg font-medium text-gray-900">{{ user.name }}</h4>
            <p class="text-sm text-gray-500">{{ user.email }} • {{ user.role }}</p>
          </div>
          <div class="flex gap-3">
            <button
              @click="openTargetsModal(user)"
              type="button"
              class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700"
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
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
              {{ __('Set Targets') }}
            </button>
            <button
              @click="openCountryModal(user)"
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
              {{ __('Assign Countries') }}
            </button>
          </div>
        </div>

        <!-- Assigned Countries -->
        <div v-if="user.countries.length > 0" class="space-y-4">
          <div
            v-for="country in user.countries"
            :key="country.id"
            class="rounded-lg border border-gray-200 p-4"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <h5 class="text-base font-medium text-gray-900">
                    {{ country.country_name }}
                  </h5>
                  <span
                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800"
                  >
                    {{ country.country_code }}
                  </span>
                </div>
                <p v-if="country.assigned_at" class="mt-1 text-xs text-gray-500">
                  Assigned {{ formatDate(country.assigned_at) }}
                  <span v-if="country.assigned_by"> by {{ country.assigned_by }}</span>
                </p>

                <!-- Assigned Websites -->
                <div v-if="country.websites.length > 0" class="mt-3">
                  <p class="text-xs font-medium text-gray-700 mb-2">
                    Assigned Websites:
                  </p>
                  <div class="space-y-1">
                    <div
                      v-for="website in country.websites"
                      :key="website.id"
                      class="flex items-center gap-2 text-sm"
                    >
                      <svg
                        class="h-4 w-4 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                        />
                      </svg>
                      <a
                        :href="website.website_url"
                        target="_blank"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        {{ website.company_name }}
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex gap-2">
                <button
                  @click="openWebsiteModal(user, country)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm"
                  :title="__('Manage Websites')"
                >
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"
                    />
                  </svg>
                </button>
                <button
                  @click="openReassignModal(user, country)"
                  class="text-blue-600 hover:text-blue-900 text-sm"
                  :title="__('Reassign to Another User')"
                >
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                    />
                  </svg>
                </button>
                <button
                  @click="unassignCountry(user, country)"
                  class="text-red-600 hover:text-red-900 text-sm"
                  :title="__('Unassign Country')"
                >
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-6 text-gray-500">
          {{ __('No countries assigned yet') }}
        </div>
      </div>
    </div>

    <!-- Country Assignment Modal -->
    <Modal v-if="showCountryModal" @close="closeCountryModal">
      <template #title>
        {{ __('Assign Countries to') }} {{ selectedUser?.name }}
      </template>

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ __('Select Countries') }}
          </label>

          <!-- Search Box -->
          <div class="relative mb-3">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="countrySearchQuery"
              type="text"
              :placeholder="__('Search countries...')"
              class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <!-- Countries List -->
          <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md p-3">
            <div v-if="filteredCountries.length > 0" class="space-y-2">
              <label
                v-for="country in filteredCountries"
                :key="country.code"
                class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer"
              >
                <input
                  v-model="selectedCountryCodes"
                  :value="country.code"
                  type="checkbox"
                  class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                />
                <span class="ml-3 text-sm text-gray-900">{{ country.name }}</span>
                <span class="ml-2 text-xs text-gray-500">({{ country.code }})</span>
              </label>
            </div>
            <div v-else class="text-center py-4 text-gray-500 text-sm">
              {{ __('No countries found matching your search') }}
            </div>
          </div>

          <!-- Selected Count -->
          <p v-if="selectedCountryCodes.length > 0" class="mt-2 text-sm text-gray-600">
            {{ selectedCountryCodes.length }} {{ selectedCountryCodes.length === 1 ? __('country') : __('countries') }} selected
          </p>
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="closeCountryModal"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Cancel') }}
          </button>
          <button
            @click="saveCountries"
            :disabled="savingCountries"
            type="button"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          >
            {{ savingCountries ? __('Saving...') : __('Save') }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Website Assignment Modal -->
    <Modal v-if="showWebsiteModal" @close="closeWebsiteModal">
      <template #title>
        {{ __('Manage Websites for') }} {{ selectedCountry?.country_name }}
      </template>

      <div class="space-y-4">
        <Loader v-if="loadingCompanies" size="30" color="#5850ec" class="mx-auto" />

        <div v-else>
          <p class="text-sm text-gray-600 mb-3">
            {{ __('Select websites/companies from') }} {{ selectedCountry?.country_name }}
          </p>

          <!-- Search Box -->
          <div v-if="companies.length > 0" class="relative mb-3">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input
              v-model="companySearchQuery"
              type="text"
              :placeholder="__('Search companies...')"
              class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>

          <!-- Companies List -->
          <div v-if="companies.length > 0" class="max-h-96 overflow-y-auto border border-gray-200 rounded-md p-3">
            <div v-if="filteredCompanies.length > 0" class="space-y-2">
              <label
                v-for="company in filteredCompanies"
                :key="company.id"
                class="flex items-start p-2 hover:bg-gray-50 rounded cursor-pointer"
              >
                <input
                  v-model="selectedCompanyIds"
                  :value="company.id"
                  type="checkbox"
                  class="h-4 w-4 mt-0.5 rounded border-gray-300 text-indigo-600"
                />
                <div class="ml-3 flex-1">
                  <div class="text-sm font-medium text-gray-900">{{ company.name }}</div>
                  <div v-if="company.website" class="text-xs text-gray-500">{{ company.website }}</div>
                </div>
              </label>
            </div>
            <div v-else class="text-center py-4 text-gray-500 text-sm">
              {{ __('No companies found matching your search') }}
            </div>
          </div>
          <div v-else class="text-center py-6 text-gray-500">
            {{ __('No companies found with websites for this country') }}
          </div>

          <!-- Selected Count -->
          <p v-if="selectedCompanyIds.length > 0" class="mt-2 text-sm text-gray-600">
            {{ selectedCompanyIds.length }} {{ selectedCompanyIds.length === 1 ? __('company') : __('companies') }} selected
          </p>
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="closeWebsiteModal"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Cancel') }}
          </button>
          <button
            @click="saveWebsites"
            :disabled="savingWebsites || loadingCompanies"
            type="button"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
          >
            {{ savingWebsites ? __('Saving...') : __('Save') }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Reassign Country Modal -->
    <Modal v-if="showReassignModal" @close="closeReassignModal">
      <template #title>
        {{ __('Reassign Country to Another User') }}
      </template>

      <div class="space-y-4">
        <div>
          <p class="text-sm text-gray-700 mb-4">
            {{ __('Reassigning') }}: <strong>{{ reassignCountry?.country_name }}</strong>
            {{ __('from') }} <strong>{{ reassignFromUser?.name }}</strong>
          </p>

          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ __('Select New User') }}
          </label>

          <!-- Users List -->
          <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-md p-3">
            <label
              v-for="user in availableUsersForReassign"
              :key="user.id"
              class="flex items-center p-3 hover:bg-gray-50 rounded cursor-pointer border border-transparent hover:border-indigo-200"
            >
              <input
                v-model="reassignToUserId"
                :value="user.id"
                type="radio"
                class="h-4 w-4 border-gray-300 text-indigo-600"
              />
              <div class="ml-3">
                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                <div class="text-xs text-gray-500">{{ user.email }} • {{ user.role }}</div>
              </div>
            </label>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
          <button
            @click="closeReassignModal"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Cancel') }}
          </button>
          <button
            @click="performReassign"
            :disabled="!reassignToUserId || savingReassign"
            type="button"
            class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
          >
            {{ savingReassign ? __('Reassigning...') : __('Reassign Country') }}
          </button>
        </div>
      </div>
    </Modal>

    <!-- Performance Targets Modal -->
    <Modal v-if="showTargetsModal" @close="closeTargetsModal">
      <template #title>
        {{ __('Set Performance Targets for') }} {{ selectedTargetUser?.name }}
      </template>

      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          {{ __('Set daily performance targets to help users focus their marketing efforts on the most profitable countries.') }}
        </p>

        <div class="grid grid-cols-2 gap-4">
          <!-- Daily Impressions Target -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ __('Daily Impressions Target') }}
            </label>
            <input
              v-model.number="targetForm.daily_impressions_target"
              type="number"
              min="0"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="2000"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Total ad impressions per day') }}</p>
          </div>

          <!-- Daily Page Views Target -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ __('Daily Page Views Target') }}
            </label>
            <input
              v-model.number="targetForm.daily_page_views_target"
              type="number"
              min="0"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="1500"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Total page views per day') }}</p>
          </div>

          <!-- Daily Clicks Target -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ __('Daily Clicks Target') }}
            </label>
            <input
              v-model.number="targetForm.daily_clicks_target"
              type="number"
              min="0"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="20"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Minimum ad clicks per day') }}</p>
          </div>

          <!-- Minimum CPC Target -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ __('Minimum CPC Target ($)') }}
            </label>
            <input
              v-model.number="targetForm.min_cpc_target"
              type="number"
              step="0.01"
              min="0"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="0.10"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Minimum cost per click') }}</p>
          </div>

          <!-- Minimum RPM Target -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ __('Minimum RPM Target ($)') }}
            </label>
            <input
              v-model.number="targetForm.min_rpm_target"
              type="number"
              step="0.01"
              min="0"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="1.00"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Minimum revenue per thousand') }}</p>
          </div>

          <!-- Daily Earnings Target -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ __('Daily Earnings Target ($)') }}
            </label>
            <input
              v-model.number="targetForm.daily_earnings_target"
              type="number"
              step="0.01"
              min="0"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="20.00"
            />
            <p class="mt-1 text-xs text-gray-500">{{ __('Total daily earnings target') }}</p>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
          <button
            @click="closeTargetsModal"
            type="button"
            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          >
            {{ __('Cancel') }}
          </button>
          <button
            @click="saveTargets"
            :disabled="savingTargets"
            type="button"
            class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 disabled:opacity-50"
          >
            {{ savingTargets ? __('Saving...') : __('Save Targets') }}
          </button>
        </div>
      </div>
    </Modal>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, inject, computed } from 'vue'
import SettingsLayout from './SettingsLayout.vue'
import { Loader } from 'thetheme'
import { axios } from 'spack'

const __ = inject('__')

// Data
const loading = ref(false)
const users = ref<any[]>([])
const availableCountries = ref<any[]>([])

// Country modal
const showCountryModal = ref(false)
const selectedUser = ref<any>(null)
const selectedCountryCodes = ref<string[]>([])
const savingCountries = ref(false)
const countrySearchQuery = ref('')

// Website modal
const showWebsiteModal = ref(false)
const selectedCountry = ref<any>(null)
const companies = ref<any[]>([])
const selectedCompanyIds = ref<number[]>([])
const loadingCompanies = ref(false)
const savingWebsites = ref(false)
const companySearchQuery = ref('')

// Reassign modal
const showReassignModal = ref(false)
const reassignCountry = ref<any>(null)
const reassignFromUser = ref<any>(null)
const reassignToUserId = ref<number | null>(null)
const savingReassign = ref(false)

// Targets modal
const showTargetsModal = ref(false)
const selectedTargetUser = ref<any>(null)
const savingTargets = ref(false)
const targetForm = ref({
  daily_impressions_target: 2000,
  daily_page_views_target: 1500,
  daily_clicks_target: 20,
  min_cpc_target: 0.10,
  min_rpm_target: 1.00,
  daily_earnings_target: 20.00,
})

// Computed properties
const filteredCountries = computed(() => {
  if (!countrySearchQuery.value) {
    return availableCountries.value
  }

  const query = countrySearchQuery.value.toLowerCase()
  return availableCountries.value.filter((country) => {
    return (
      country.name.toLowerCase().includes(query) ||
      country.code.toLowerCase().includes(query)
    )
  })
})

const filteredCompanies = computed(() => {
  if (!companySearchQuery.value) {
    return companies.value
  }

  const query = companySearchQuery.value.toLowerCase()
  return companies.value.filter((company) => {
    return (
      company.name.toLowerCase().includes(query) ||
      (company.website && company.website.toLowerCase().includes(query))
    )
  })
})

const availableUsersForReassign = computed(() => {
  if (!reassignFromUser.value) return users.value
  // Exclude the current user from reassign options
  return users.value.filter((user) => user.id !== reassignFromUser.value.id)
})

const fetchData = async () => {
  loading.value = true
  try {
    const response = await axios.get('settings/countries')
    console.log('API Response:', response.data)

    // Handle both response formats
    const data = response.data.data || response.data

    if (!data || !data.users) {
      console.error('Invalid response structure:', response.data)
      throw new Error('Invalid response from server')
    }

    users.value = data.users || []
    availableCountries.value = data.countries || []
  } catch (error) {
    console.error('Failed to fetch data:', error)
    alert('Failed to load data. Please check console for details.')
  } finally {
    loading.value = false
  }
}

const openCountryModal = (user: any) => {
  selectedUser.value = user
  selectedCountryCodes.value = user.countries.map((c: any) => c.country_code)
  countrySearchQuery.value = ''
  showCountryModal.value = true
}

const closeCountryModal = () => {
  showCountryModal.value = false
  selectedUser.value = null
  selectedCountryCodes.value = []
  countrySearchQuery.value = ''
}

const saveCountries = async () => {
  if (selectedCountryCodes.value.length === 0) {
    alert('Please select at least one country')
    return
  }

  savingCountries.value = true
  try {
    const countries = selectedCountryCodes.value.map((code) => {
      const country = availableCountries.value.find((c) => c.code === code)
      return {
        country_code: code,
        country_name: country?.name || code,
      }
    })

    await axios.put(`settings/countries/users/${selectedUser.value.id}/countries`, {
      countries,
    })

    alert('Countries assigned successfully! User will receive a notification.')
    await fetchData()
    closeCountryModal()
  } catch (error) {
    console.error('Failed to save countries:', error)
    alert('Failed to assign countries')
  } finally {
    savingCountries.value = false
  }
}

const openWebsiteModal = async (user: any, country: any) => {
  selectedUser.value = user
  selectedCountry.value = country
  selectedCompanyIds.value = country.websites.map((w: any) => w.company_id)
  companySearchQuery.value = ''
  showWebsiteModal.value = true

  // Fetch companies for this country
  loadingCompanies.value = true
  try {
    const response = await axios.get(`settings/countries/companies/${country.country_code}`)
    companies.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch companies:', error)
    alert('Failed to load companies')
  } finally {
    loadingCompanies.value = false
  }
}

const closeWebsiteModal = () => {
  showWebsiteModal.value = false
  selectedUser.value = null
  selectedCountry.value = null
  companies.value = []
  selectedCompanyIds.value = []
  companySearchQuery.value = ''
}

const saveWebsites = async () => {
  savingWebsites.value = true
  try {
    const websites = selectedCompanyIds.value.map((companyId) => {
      const company = companies.value.find((c) => c.id === companyId)
      return {
        company_id: companyId,
        website_url: company?.website || '',
        company_name: company?.name || '',
      }
    })

    await axios.post(
      `settings/countries/users/${selectedUser.value.id}/countries/${selectedCountry.value.id}/websites`,
      { websites }
    )

    alert('Websites assigned successfully!')
    await fetchData()
    closeWebsiteModal()
  } catch (error) {
    console.error('Failed to save websites:', error)
    alert('Failed to assign websites')
  } finally {
    savingWebsites.value = false
  }
}

const openReassignModal = (user: any, country: any) => {
  reassignFromUser.value = user
  reassignCountry.value = country
  reassignToUserId.value = null
  showReassignModal.value = true
}

const closeReassignModal = () => {
  showReassignModal.value = false
  reassignFromUser.value = null
  reassignCountry.value = null
  reassignToUserId.value = null
}

const performReassign = async () => {
  if (!reassignToUserId.value || !reassignFromUser.value || !reassignCountry.value) {
    return
  }

  savingReassign.value = true
  try {
    // Remove country from current user
    const currentUserCountries = reassignFromUser.value.countries
      .filter((c: any) => c.country_code !== reassignCountry.value.country_code)
      .map((c: any) => ({
        country_code: c.country_code,
        country_name: c.country_name,
      }))

    await axios.put(`settings/countries/users/${reassignFromUser.value.id}/countries`, {
      countries: currentUserCountries,
    })

    // Add country to new user
    const newUser = users.value.find((u) => u.id === reassignToUserId.value)
    if (newUser) {
      const newUserCountries = [
        ...newUser.countries.map((c: any) => ({
          country_code: c.country_code,
          country_name: c.country_name,
        })),
        {
          country_code: reassignCountry.value.country_code,
          country_name: reassignCountry.value.country_name,
        },
      ]

      await axios.put(`settings/countries/users/${reassignToUserId.value}/countries`, {
        countries: newUserCountries,
      })
    }

    alert(`Country "${reassignCountry.value.country_name}" reassigned successfully!`)
    await fetchData()
    closeReassignModal()
  } catch (error) {
    console.error('Failed to reassign country:', error)
    alert('Failed to reassign country')
  } finally {
    savingReassign.value = false
  }
}

const unassignCountry = async (user: any, country: any) => {
  if (!confirm(`Are you sure you want to unassign "${country.country_name}" from ${user.name}?`)) {
    return
  }

  try {
    // Remove this country from user's countries
    const remainingCountries = user.countries
      .filter((c: any) => c.country_code !== country.country_code)
      .map((c: any) => ({
        country_code: c.country_code,
        country_name: c.country_name,
      }))

    await axios.put(`settings/countries/users/${user.id}/countries`, {
      countries: remainingCountries,
    })

    alert('Country unassigned successfully!')
    await fetchData()
  } catch (error) {
    console.error('Failed to unassign country:', error)
    alert('Failed to unassign country')
  }
}

const openTargetsModal = async (user: any) => {
  selectedTargetUser.value = user
  showTargetsModal.value = true

  // Fetch current targets for this user
  try {
    const response = await axios.get(`settings/countries/users/${user.id}/targets`)
    if (response.data.data) {
      targetForm.value = { ...response.data.data }
    }
  } catch (error) {
    console.error('Failed to fetch targets:', error)
  }
}

const closeTargetsModal = () => {
  showTargetsModal.value = false
  selectedTargetUser.value = null
  targetForm.value = {
    daily_impressions_target: 2000,
    daily_page_views_target: 1500,
    daily_clicks_target: 20,
    min_cpc_target: 0.10,
    min_rpm_target: 1.00,
    daily_earnings_target: 20.00,
  }
}

const saveTargets = async () => {
  if (!selectedTargetUser.value) return

  savingTargets.value = true
  try {
    await axios.put(`settings/countries/users/${selectedTargetUser.value.id}/targets`, targetForm.value)
    alert('Performance targets updated successfully!')
    closeTargetsModal()
  } catch (error) {
    console.error('Failed to save targets:', error)
    alert('Failed to save performance targets')
  } finally {
    savingTargets.value = false
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString()
}

onMounted(() => {
  fetchData()
})
</script>

<script lang="ts">
// Modal component
import { defineComponent, h } from 'vue'

const Modal = defineComponent({
  name: 'Modal',
  emits: ['close'],
  setup(props, { slots, emit }) {
    return () =>
      h('div', { class: 'fixed inset-0 z-50 overflow-y-auto' }, [
        h('div', { class: 'flex min-h-screen items-center justify-center px-4' }, [
          h('div', {
            class: 'fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity',
            onClick: () => emit('close'),
          }),
          h('div', { class: 'relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl' }, [
            h('h3', { class: 'text-lg font-medium text-gray-900 mb-4' }, slots.title?.()),
            h('div', {}, slots.default?.()),
          ]),
        ]),
      ])
  },
})
</script>
