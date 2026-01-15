<template>
  <div
    v-if="isImpersonating"
    class="fixed top-0 left-0 right-0 z-[300] flex items-center justify-center gap-4 bg-yellow-500 px-4 py-2 text-sm font-medium text-yellow-900"
  >
    <span>
      {{ __('You are logged in as') }} <strong>{{ userName }}</strong>
    </span>
    <button
      type="button"
      class="rounded-md bg-yellow-600 px-3 py-1 text-xs font-semibold text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
      @click="stopImpersonating"
    >
      {{ __('Return to your account') }}
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { appData } from '@/app-data'
import { axios } from 'spack/axios'

const isImpersonating = computed(() => appData.is_impersonating)
const userName = computed(() => appData.user?.name || 'Unknown')

const stopImpersonating = async () => {
  try {
    const response = await axios.post('impersonate/stop')
    alert(response.data.message)
    // Full page redirect to get fresh session and CSRF token
    window.location.href = '/'
  } catch (error) {
    console.error('Stop impersonating error:', error)
    alert(error.response?.data?.message || 'Failed to stop impersonating')
  }
}
</script>
