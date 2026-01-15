<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">{{ __('Activity Monitoring Settings') }}</h1>
      <p class="mt-1 text-sm text-gray-500">
        {{ __('Configure activity tracking behavior, exception URLs, and thresholds') }}
      </p>
    </div>

    <form @submit.prevent="saveSettings" class="space-y-6">
      <!-- Enable/Disable Toggle -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">{{ __('Activity Monitoring') }}</h3>
              <p class="mt-1 text-sm text-gray-500">
                {{ __('Enable or disable activity monitoring for all employees') }}
              </p>
            </div>
            <button
              type="button"
              role="switch"
              :aria-checked="settings.enabled"
              :class="[
                settings.enabled ? 'bg-indigo-600' : 'bg-gray-200',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
              ]"
              @click="settings.enabled = !settings.enabled"
            >
              <span
                :class="[
                  settings.enabled ? 'translate-x-5' : 'translate-x-0',
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                ]"
              />
            </button>
          </div>
        </div>
      </div>

      <!-- Exception URLs -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Exception URLs (Company Pages)') }}</h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ __('Activity tracking will be skipped when users are browsing these domains. This is useful for company websites where employees may spend extended time.') }}
          </p>

          <div class="space-y-3">
            <div v-for="(url, index) in settings.exception_urls" :key="index" class="flex gap-2">
              <input
                v-model="settings.exception_urls[index]"
                type="url"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                :placeholder="__('https://example.com')"
              />
              <button
                type="button"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                @click="removeExceptionUrl(index)"
              >
                <TrashIcon class="h-4 w-4 text-red-500" />
              </button>
            </div>

            <button
              type="button"
              class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
              @click="addExceptionUrl"
            >
              <PlusIcon class="mr-2 h-4 w-4" />
              {{ __('Add URL') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Thresholds -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Thresholds') }}</h3>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">
                {{ __('Same Page Threshold (minutes)') }}
              </label>
              <p class="mt-1 text-xs text-gray-500">
                {{ __('Alert if user stays on the same page for this duration') }}
              </p>
              <input
                v-model.number="settings.same_page_threshold"
                type="number"
                min="5"
                max="120"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">
                {{ __('Inactivity Threshold (minutes)') }}
              </label>
              <p class="mt-1 text-xs text-gray-500">
                {{ __('Alert if no mouse/keyboard activity for this duration') }}
              </p>
              <input
                v-model.number="settings.inactivity_threshold"
                type="number"
                min="5"
                max="120"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">
                {{ __('Heartbeat Interval (seconds)') }}
              </label>
              <p class="mt-1 text-xs text-gray-500">
                {{ __('How often to send activity updates to the server') }}
              </p>
              <input
                v-model.number="settings.heartbeat_interval"
                type="number"
                min="30"
                max="300"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Lunch Time -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Lunch Break (Skip Tracking)') }}</h3>
          <p class="text-sm text-gray-500 mb-4">
            {{ __('Activity tracking will be paused during this time period') }}
          </p>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-gray-700">
                {{ __('Start Time') }}
              </label>
              <input
                v-model="settings.lunch_start"
                type="time"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700">
                {{ __('End Time') }}
              </label>
              <input
                v-model="settings.lunch_end"
                type="time"
                class="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="saving"
          class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
        >
          <span v-if="saving" class="mr-2">
            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
          </span>
          {{ saving ? __('Saving...') : __('Save Settings') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { inject, onMounted, reactive, ref } from 'vue'
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'

const __ = inject('__')

const processing = ref(true)
const saving = ref(false)

const settings = reactive({
  enabled: true,
  exception_urls: [
    'https://nyasajob.com',
    'https://studyseco.com',
    'https://malawirents.com',
    'https://emphxs.com',
    'https://zoswa.com',
  ],
  same_page_threshold: 15,
  inactivity_threshold: 10,
  heartbeat_interval: 60,
  lunch_start: '12:00',
  lunch_end: '13:00',
})

const loadSettings = async () => {
  try {
    const response = await axios.get('activity/settings')
    Object.assign(settings, response.data)

    // Ensure exception_urls is an array
    if (!Array.isArray(settings.exception_urls)) {
      settings.exception_urls = []
    }
  } catch (error) {
    console.error('Failed to load settings:', error)
  } finally {
    processing.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  try {
    // Filter out empty URLs
    const cleanedSettings = {
      ...settings,
      exception_urls: settings.exception_urls.filter((url) => url && url.trim() !== ''),
    }

    await axios.post('activity/settings', cleanedSettings)
    alert(__('Settings saved successfully'))
  } catch (error) {
    console.error('Failed to save settings:', error)
    alert(__('Failed to save settings'))
  } finally {
    saving.value = false
  }
}

const addExceptionUrl = () => {
  settings.exception_urls.push('')
}

const removeExceptionUrl = (index) => {
  settings.exception_urls.splice(index, 1)
}

onMounted(() => {
  loadSettings()
})
</script>
