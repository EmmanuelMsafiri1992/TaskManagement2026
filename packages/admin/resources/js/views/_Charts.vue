<template>
  <section class="grid grid-cols-12 gap-6">
    <!-- Weekly Chart -->
    <div class="col-span-12 lg:col-span-6">
      <Card class="overflow-hidden rounded-lg border border-gray-200 p-6 shadow-sm transition-shadow duration-200 hover:shadow-md">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ __('Completed in the last 7 days') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
              Weekly task completion trend
            </p>
          </div>
          <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">
            7 Days
          </span>
        </div>

        <div style="height: 300px">
          <LineChart
            id="tasks-chart"
            :data="home.charts?.chart_tasks_weekly"
            tooltip-label="Tasks"
            background-color="rgba(99, 102, 241, 0.1)"
            border-color="#6366F1"
          />
        </div>
      </Card>
    </div>

    <!-- Monthly Chart -->
    <div class="col-span-12 lg:col-span-6">
      <Card class="overflow-hidden rounded-lg border border-gray-200 p-6 shadow-sm transition-shadow duration-200 hover:shadow-md">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ __('Most productive month') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
              Yearly performance overview
            </p>
          </div>
          <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-xs font-medium text-purple-700">
            12 Months
          </span>
        </div>

        <div style="height: 300px">
          <LineChart
            id="tasks-chart-yearly"
            :data="home.charts?.chart_tasks_yearly"
            tooltip-label="Tasks"
            background-color="rgba(147, 51, 234, 0.1)"
            border-color="#9333EA"
          />
        </div>
      </Card>
    </div>
  </section>
</template>

<script setup lang="ts">
  import { useHomeStore } from 'Store/home'
  import { LineChart } from 'thetheme'
  import { Card } from 'thetheme'

  const home = useHomeStore()
</script>
