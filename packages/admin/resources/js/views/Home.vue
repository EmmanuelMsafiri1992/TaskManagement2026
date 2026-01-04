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
    </div>

    <Metrics />

    <!-- Working Hours Widget -->
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
      <WorkingHoursWidget />
    </div>

    <Charts />
  </div>
</template>

<script setup lang="ts">
  import { inject } from 'vue'
  import { useHomeStore } from 'Store/home'
  import { Loader } from 'thetheme'
  import Charts from './_Charts.vue'
  import Metrics from './_Metrics.vue'
  import WorkingHoursWidget from './layouts/WorkingHoursWidget.vue'

  const __ = inject('__')
  const home = useHomeStore()
</script>
