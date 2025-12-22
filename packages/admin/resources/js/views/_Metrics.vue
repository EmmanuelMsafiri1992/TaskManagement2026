<template>
  <section>
    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <!-- Open Tasks Card -->
      <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:border-indigo-300 hover:shadow-md">
        <dt class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100">
            <inbox-icon class="h-6 w-6 text-indigo-600" />
          </div>
          <p class="text-sm font-medium text-gray-600">
            {{ __('Open Tasks') }}
          </p>
        </dt>
        <dd class="mt-4">
          <p class="text-3xl font-bold text-gray-900">
            {{ home.metrics?.open_tasks }}
          </p>
          <div class="mt-3 flex items-center text-sm">
            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
              In Progress
            </span>
          </div>
        </dd>
      </div>

      <!-- Completed Tasks Card -->
      <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:border-green-300 hover:shadow-md">
        <dt class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100">
            <CheckCircleIcon class="h-6 w-6 text-green-600" />
          </div>
          <p class="text-sm font-medium text-gray-600">
            {{ __('Completed Tasks') }}
          </p>
        </dt>
        <dd class="mt-4">
          <p class="text-3xl font-bold text-gray-900">
            {{ home.metrics?.completed_tasks }}
          </p>
          <div class="mt-3 flex items-center text-sm">
            <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-medium text-green-700">
              Finished
            </span>
          </div>
        </dd>
      </div>

      <!-- Total Projects Card -->
      <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition-all duration-200 hover:border-purple-300 hover:shadow-md">
        <dt class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100">
            <calendar-icon class="h-6 w-6 text-purple-600" />
          </div>
          <p class="text-sm font-medium text-gray-600">
            {{ __('Total Projects') }}
          </p>
        </dt>
        <dd class="mt-4">
          <p class="text-3xl font-bold text-gray-900">
            {{ home.metrics?.total_projects }}
          </p>
          <div class="mt-3 flex items-center text-sm">
            <span class="inline-flex items-center rounded-full bg-purple-50 px-2.5 py-0.5 text-xs font-medium text-purple-700">
              Active
            </span>
          </div>
        </dd>
      </div>
    </dl>
  </section>
</template>

<script setup lang="ts">
  import { useHomeStore } from 'Store/home'
  import {
    CalendarIcon,
    CheckCircleIcon,
    InboxIcon,
  } from '@heroicons/vue/24/outline'

  const home = useHomeStore()
</script>
