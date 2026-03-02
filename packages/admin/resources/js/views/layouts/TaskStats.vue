<template>
  <div v-if="stats" class="hidden lg:flex items-center space-x-4 px-4">
    <!-- Total Completed -->
    <div class="flex items-center space-x-1.5 text-sm">
      <CheckBadgeIcon class="h-4 w-4 text-emerald-600" />
      <span class="text-gray-700 font-medium">{{ stats.total_completed }}</span>
      <span class="text-gray-400 text-xs">completed</span>
    </div>

    <div class="h-4 w-px bg-gray-200"></div>

    <!-- Completed Today -->
    <div class="flex items-center space-x-1.5 text-sm">
      <CheckCircleIcon class="h-4 w-4 text-green-500" />
      <span class="text-gray-600">{{ stats.completed_today }}</span>
      <span class="text-gray-400 text-xs">today</span>
    </div>

    <div class="h-4 w-px bg-gray-200"></div>

    <!-- New Tasks -->
    <div class="flex items-center space-x-1.5 text-sm">
      <PlusCircleIcon class="h-4 w-4 text-blue-500" />
      <span class="text-gray-600">{{ stats.new_this_week }}</span>
      <span class="text-gray-400 text-xs">new</span>
    </div>

    <div class="h-4 w-px bg-gray-200"></div>

    <!-- Overdue -->
    <div class="flex items-center space-x-1.5 text-sm">
      <ExclamationCircleIcon
        class="h-4 w-4"
        :class="stats.overdue > 0 ? 'text-red-500' : 'text-gray-300'"
      />
      <span :class="stats.overdue > 0 ? 'text-red-600 font-medium' : 'text-gray-600'">
        {{ stats.overdue }}
      </span>
      <span class="text-gray-400 text-xs">overdue</span>
    </div>

    <div class="h-4 w-px bg-gray-200"></div>

    <!-- Performance -->
    <div class="flex items-center space-x-1.5 text-sm">
      <ChartBarIcon class="h-4 w-4 text-indigo-500" />
      <span
        class="font-medium"
        :class="{
          'text-green-600': stats.weekly_performance >= 80,
          'text-yellow-600': stats.weekly_performance >= 50 && stats.weekly_performance < 80,
          'text-red-600': stats.weekly_performance < 50
        }"
      >
        {{ stats.weekly_performance }}%
      </span>
      <span class="text-gray-400 text-xs">weekly</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { axios } from 'spack/axios'
import {
  CheckBadgeIcon,
  CheckCircleIcon,
  PlusCircleIcon,
  ExclamationCircleIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

interface TaskStats {
  completed_today: number
  completed_this_week: number
  total_completed: number
  new_today: number
  new_this_week: number
  overdue: number
  pending: number
  total: number
  performance: number
  weekly_performance: number
}

const stats = ref<TaskStats | null>(null)

const fetchStats = async () => {
  try {
    const { data } = await axios.get('tasks-stats')
    stats.value = data
  } catch (error) {
    console.error('Failed to fetch task stats:', error)
  }
}

// Fetch stats on mount and refresh every 2 minutes
onMounted(() => {
  fetchStats()
  setInterval(fetchStats, 120000)
})
</script>
