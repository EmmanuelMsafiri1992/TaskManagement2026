<template>
  <SettingsLayout>
    <FormBase
      name="setting-adsense"
      uri="settings/adsense"
      :title="__('AdSense Configuration')"
      save-only
    >
      <div class="space-y-6">
        <!-- Client ID -->
        <FieldText
          name="adsense_client_id"
          :label="__('OAuth Client ID')"
          placeholder="xxxxx.apps.googleusercontent.com"
          inline
          required
        />

        <!-- Client Secret -->
        <FieldText
          name="adsense_client_secret"
          :label="__('OAuth Client Secret')"
          placeholder="GOCSPX-xxxxx"
          type="password"
          inline
          required
        />

        <!-- Account ID -->
        <FieldText
          name="adsense_account_id"
          :label="__('AdSense Account ID')"
          placeholder="pub-XXXXXXXXXXXXXXXX"
          inline
        />

        <!-- Divider -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
            {{ __('Google Analytics Configuration') }}
          </h3>
        </div>

        <!-- GA4 Property ID -->
        <FieldText
          name="ga4_property_id"
          :label="__('GA4 Property ID')"
          placeholder="123456789"
          inline
          :help="__('Find this in Google Analytics Admin > Property Settings')"
        />

        <!-- OAuth Connection -->
        <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Google Account') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <div v-if="!form.data.adsense_configured" class="space-y-3">
              <button
                type="button"
                @click="connectWithGoogle"
                :disabled="connecting"
                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
              >
                <svg v-if="connecting" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="mr-2 h-5 w-5" viewBox="0 0 48 48">
                  <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                  <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                  <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                  <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                {{ connecting ? __('Connecting...') : __('Connect with Google') }}
              </button>
              <p class="text-sm text-gray-500">
                {{ __('Click to authorize TaskHub to access your AdSense data') }}
              </p>
            </div>
            <div v-else class="flex items-center space-x-4">
              <div class="flex items-center space-x-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-100">
                  <svg class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </span>
                <span class="text-sm font-medium text-gray-700">{{ __('Connected') }}</span>
              </div>
              <button
                type="button"
                @click="disconnectGoogle"
                :disabled="disconnecting"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
              >
                {{ disconnecting ? __('Disconnecting...') : __('Disconnect') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Test AdSense Connection Button -->
        <div v-if="form.data.adsense_configured" class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Test AdSense Connection') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <button
              type="button"
              @click="testConnection"
              :disabled="testing"
              class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
            >
              <svg v-if="testing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ testing ? __('Testing...') : __('Test AdSense API') }}
            </button>
            <p v-if="testResult" :class="['mt-2 text-sm', testResult.success ? 'text-green-600' : 'text-red-600']">
              {{ testResult.message }}
            </p>
          </div>
        </div>

        <!-- Test Analytics Connection Button -->
        <div v-if="form.data.ga4_property_id" class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:border-t sm:border-gray-200 sm:pt-5">
          <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
            {{ __('Test Analytics Connection') }}
          </label>
          <div class="mt-1 sm:col-span-2 sm:mt-0">
            <button
              type="button"
              @click="testAnalyticsConnection"
              :disabled="testingAnalytics"
              class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
            >
              <svg v-if="testingAnalytics" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h-3a2 2 0 00-2 2v9a2 2 0 002 2h9a2 2 0 002-2v-3m-3-3l-6 6m0 0l6-6m-6 6h6V7" />
              </svg>
              {{ testingAnalytics ? __('Testing...') : __('Test Analytics API') }}
            </button>
            <p v-if="analyticsTestResult" :class="['mt-2 text-sm', analyticsTestResult.success ? 'text-green-600' : 'text-red-600']">
              {{ analyticsTestResult.message }}
            </p>
          </div>
        </div>

        <!-- Configuration Status -->
        <div v-if="form.data.adsense_configured" class="rounded-md bg-green-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-green-800">
                {{ __('AdSense is configured') }}
              </h3>
              <div class="mt-2 text-sm text-green-700">
                <p>{{ __('Your AdSense integration is active and ready to use.') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Instructions -->
        <div class="rounded-md bg-blue-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <h3 class="text-sm font-medium text-blue-800">Setup Instructions</h3>
              <div class="mt-2 text-sm text-blue-700">
                <p class="mb-3 font-medium">{{ __('AdSense Setup:') }}</p>
                <ol class="list-decimal list-inside space-y-1 ml-2 mb-4">
                  <li>{{ __('Create OAuth 2.0 credentials in Google Cloud Console') }}</li>
                  <li>{{ __('Add your credentials to the .env file') }}</li>
                  <li>{{ __('Click "Connect with Google" to authorize') }}</li>
                  <li>{{ __('Enter your AdSense Account ID (e.g., pub-XXXXXXXXXXXXXXXX)') }}</li>
                </ol>
                <p class="mb-3 font-medium">{{ __('Google Analytics Setup:') }}</p>
                <ol class="list-decimal list-inside space-y-1 ml-2">
                  <li>{{ __('Service account credentials are already configured at storage/app/credentials/google-analytics.json') }}</li>
                  <li>{{ __('Go to Google Analytics Admin > Property Access Management') }}</li>
                  <li>{{ __('Add service account: taskhub-service-account@taskhub-adsense-integration.iam.gserviceaccount.com') }}</li>
                  <li>{{ __('Grant "Viewer" role') }}</li>
                  <li>{{ __('Enter your GA4 Property ID from Google Analytics Admin > Property Settings') }}</li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      </div>
    </FormBase>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useFlashStore, useFormStore } from 'spack'
import SettingsLayout from './SettingsLayout.vue'
import { FormBase, useFieldText } from 'thetheme'
import axios from 'axios'

const form = useFormStore('setting-adsense')()
const FieldText = useFieldText<any>()

const connecting = ref(false)
const disconnecting = ref(false)
const testing = ref(false)
const testResult = ref(null)
const testingAnalytics = ref(false)
const analyticsTestResult = ref(null)

const connectWithGoogle = async () => {
  connecting.value = true

  try {
    const response = await axios.get('/api/adsense/auth/url')
    const authUrl = response.data.auth_url

    // Open OAuth URL in the same window
    window.location.href = authUrl
  } catch (error: any) {
    useFlashStore().error(error.response?.data?.message || 'Failed to initiate Google connection')
    connecting.value = false
  }
}

const disconnectGoogle = async () => {
  if (!confirm('Are you sure you want to disconnect your Google AdSense account?')) {
    return
  }

  disconnecting.value = true

  try {
    await axios.post('/api/adsense/disconnect')
    useFlashStore().success('AdSense disconnected successfully')
    setTimeout(() => {
      location.reload()
    }, 1000)
  } catch (error: any) {
    useFlashStore().error(error.response?.data?.message || 'Failed to disconnect AdSense')
    disconnecting.value = false
  }
}

const testConnection = async () => {
  testing.value = true
  testResult.value = null

  try {
    const response = await axios.get('/api/adsense/test-connection')
    testResult.value = {
      success: true,
      message: response.data.message || 'Connection successful!'
    }
    useFlashStore().success('AdSense API connection successful!')
  } catch (error: any) {
    testResult.value = {
      success: false,
      message: error.response?.data?.message || 'Connection failed. Please check your credentials.'
    }
    useFlashStore().error(testResult.value.message)
  } finally {
    testing.value = false
  }
}

const testAnalyticsConnection = async () => {
  testingAnalytics.value = true
  analyticsTestResult.value = null

  try {
    const response = await axios.get('/api/adsense/test-analytics-connection')
    analyticsTestResult.value = {
      success: true,
      message: response.data.message || 'Analytics connection successful!'
    }
    useFlashStore().success('Google Analytics API connection successful!')
  } catch (error: any) {
    analyticsTestResult.value = {
      success: false,
      message: error.response?.data?.message || 'Analytics connection failed. Please check your GA4 Property ID and service account permissions.'
    }
    useFlashStore().error(analyticsTestResult.value.message)
  } finally {
    testingAnalytics.value = false
  }
}

form.onSuccess(() => {
  useFlashStore().success('AdSense Settings Saved Successfully!')
  setTimeout(() => {
    location.reload()
  }, 1000)
})
</script>
