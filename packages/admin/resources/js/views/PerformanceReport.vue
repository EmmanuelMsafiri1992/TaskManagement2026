<template>
  <div>
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900">Performance Report</h1>

      <!-- Period Filter -->
      <div class="flex items-center space-x-2">
        <label class="text-sm text-gray-600">Period:</label>
        <select
          v-model="period"
          @change="fetchReport"
          class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
        >
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="year">This Year</option>
          <option value="all">All Time</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-12">
      <Loader size="40" color="#5850ec" />
    </div>

    <div v-else-if="report">
      <!-- Team Summary Cards -->
      <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClipboardDocumentListIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">Total Tasks</dt>
                  <dd class="text-2xl font-semibold text-gray-900">{{ report.team_totals.total_tasks }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-6 w-6 text-green-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">Completed</dt>
                  <dd class="text-2xl font-semibold text-green-600">{{ report.team_totals.completed }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-yellow-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">Pending</dt>
                  <dd class="text-2xl font-semibold text-yellow-600">{{ report.team_totals.pending }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-6 w-6 text-red-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">Overdue</dt>
                  <dd class="text-2xl font-semibold text-red-600">{{ report.team_totals.overdue }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ChartBarIcon class="h-6 w-6 text-indigo-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="truncate text-sm font-medium text-gray-500">Team Performance</dt>
                  <dd class="text-2xl font-semibold" :class="getPerformanceClass(report.team_totals.performance)">
                    {{ report.team_totals.performance }}%
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Performance Table -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
          <h3 class="text-lg font-medium leading-6 text-gray-900">Individual Performance</h3>
          <p class="mt-1 text-sm text-gray-500">Ranked by overall task completion rate</p>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  Rank
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                  User
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  Total
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  Completed
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  Today
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  This Week
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  Pending
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  Overdue
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  On Time
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                  Performance
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(user, index) in report.users" :key="user.user_id" :class="index < 3 ? 'bg-yellow-50' : ''">
                <td class="whitespace-nowrap px-6 py-4 text-sm">
                  <span v-if="index === 0" class="text-2xl">🥇</span>
                  <span v-else-if="index === 1" class="text-2xl">🥈</span>
                  <span v-else-if="index === 2" class="text-2xl">🥉</span>
                  <span v-else class="text-gray-500">{{ index + 1 }}</span>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 flex-shrink-0">
                      <img
                        v-if="user.avatar"
                        class="h-10 w-10 rounded-full"
                        :src="user.avatar"
                        :alt="user.name"
                      />
                      <span
                        v-else
                        class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-200"
                      >
                        <span class="text-sm font-medium text-gray-600">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                      </span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-900">
                  {{ user.total_tasks }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-green-600">
                  {{ user.completed }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-600">
                  {{ user.completed_today }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-600">
                  {{ user.completed_this_week }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-yellow-600">
                  {{ user.pending }}
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                  <span :class="user.overdue > 0 ? 'font-medium text-red-600' : 'text-gray-400'">
                    {{ user.overdue }}
                  </span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center text-sm">
                  <span class="text-gray-600">{{ user.on_time_rate }}%</span>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-center">
                  <div class="flex items-center justify-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        class="h-2 rounded-full"
                        :class="getPerformanceBarClass(user.performance)"
                        :style="{ width: user.performance + '%' }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium" :class="getPerformanceClass(user.performance)">
                      {{ user.performance }}%
                    </span>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { axios } from 'spack/axios'
import Loader from '@/thetheme/components/Loader.vue'
import {
  ClipboardDocumentListIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

interface UserPerformance {
  user_id: number
  name: string
  email: string
  avatar: string | null
  total_tasks: number
  completed: number
  completed_today: number
  completed_this_week: number
  pending: number
  overdue: number
  on_time: number
  completed_late: number
  performance: number
  on_time_rate: number
}

interface TeamTotals {
  total_tasks: number
  completed: number
  pending: number
  overdue: number
  on_time: number
  performance: number
}

interface PerformanceReportData {
  users: UserPerformance[]
  team_totals: TeamTotals
  period: string
}

const loading = ref(true)
const period = ref('week')
const report = ref<PerformanceReportData | null>(null)

const fetchReport = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('performance-report', {
      params: { period: period.value }
    })
    report.value = data
  } catch (error) {
    console.error('Failed to fetch performance report:', error)
  } finally {
    loading.value = false
  }
}

const getPerformanceClass = (performance: number) => {
  if (performance >= 80) return 'text-green-600'
  if (performance >= 50) return 'text-yellow-600'
  return 'text-red-600'
}

const getPerformanceBarClass = (performance: number) => {
  if (performance >= 80) return 'bg-green-500'
  if (performance >= 50) return 'bg-yellow-500'
  return 'bg-red-500'
}

onMounted(() => {
  fetchReport()
})
</script>
