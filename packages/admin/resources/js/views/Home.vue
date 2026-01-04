<template>
  <Loader v-if="home.fetching" size="40" color="#5850ec" class="mx-auto mt-8" />

  <div v-else class="space-y-8">
    <!-- Dashboard Header -->
    <div class="flex items-center justify-between border-b border-gray-200 pb-5">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">
          {{ __('Dashboard') }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          Welcome back! Here's what's happening with your tasks today.
        </p>
      </div>

      <!-- Working Hours Display -->
      <div v-if="workingHours" class="text-right">
        <div class="flex items-center justify-end gap-2">
          <ClockIcon class="h-5 w-5" :class="workingHours.has_custom_hours ? 'text-indigo-500' : 'text-gray-400'" />
          <span class="text-sm font-medium text-gray-500">{{ __('Your Working Hours') }}</span>
          <span v-if="workingHours.has_custom_hours" class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-800">
            {{ __('Custom') }}
          </span>
        </div>
        <p class="mt-1 text-lg font-semibold text-gray-900">
          {{ workingHours.formatted_start }} - {{ workingHours.formatted_end }}
        </p>
        <p class="text-xs text-gray-500">
          {{ workingHours.has_custom_hours ? workingHours.effective_period : __('Standard working hours apply to you.') }}
        </p>
      </div>
    </div>

    <Metrics />

    <Charts />
  </div>
</template>

<script setup lang="ts">
  import { inject, onMounted, ref } from 'vue'
  import { useHomeStore } from 'Store/home'
  import { Loader } from 'thetheme'
  import { ClockIcon } from '@heroicons/vue/24/outline'
  import { axios } from 'spack/axios'
  import Charts from './_Charts.vue'
  import Metrics from './_Metrics.vue'

  const __ = inject('__')
  const home = useHomeStore()
  const workingHours = ref(null)

  const loadWorkingHours = async () => {
    try {
      const response = await axios.get('working-hours/my-hours')
      workingHours.value = response.data
    } catch (error) {
      console.error('Failed to load working hours:', error)
    }
  }

  onMounted(() => {
    loadWorkingHours()
  })
</script>
