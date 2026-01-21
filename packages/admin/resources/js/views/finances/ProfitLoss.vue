<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Profit & Loss Report</h1>
      <p class="text-sm text-gray-500">Analyze your financial performance based on income and expenses</p>
    </div>

    <!-- Period Selector -->
    <div class="mb-6 flex items-center gap-4">
      <select
        v-model="selectedYear"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchReport"
      >
        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
      </select>

      <select
        v-model="selectedMonth"
        class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        @change="fetchReport"
      >
        <option v-for="(month, index) in months" :key="index" :value="index + 1">{{ month }}</option>
      </select>
    </div>

    <!-- Summary Cards -->
    <div v-if="report" class="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowTrendingUpIcon class="h-6 w-6 text-green-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Income</dt>
                <dd class="text-lg font-semibold text-green-600">MK {{ formatNumber(report.summary?.total_income) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ArrowTrendingDownIcon class="h-6 w-6 text-red-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Total Expenses</dt>
                <dd class="text-lg font-semibold text-red-600">MK {{ formatNumber(report.summary?.total_expenses) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ChartBarIcon class="h-6 w-6 text-blue-400" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-gray-500">Profit Margin</dt>
                <dd class="text-lg font-semibold" :class="report.summary?.profit_margin >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ report.summary?.profit_margin?.toFixed(1) }}%
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="overflow-hidden rounded-lg shadow" :class="report.summary?.is_profit ? 'bg-gradient-to-r from-green-500 to-emerald-600' : 'bg-gradient-to-r from-red-500 to-rose-600'">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <BanknotesIcon class="h-6 w-6 text-white" />
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="truncate text-sm font-medium text-white/80">{{ report.summary?.is_profit ? 'Net Profit' : 'Net Loss' }}</dt>
                <dd class="text-lg font-bold text-white">MK {{ formatNumber(Math.abs(report.summary?.net_profit_loss)) }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparison with Previous Month -->
    <div v-if="report?.comparison" class="mb-6 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Month-over-Month Comparison</h2>
      </div>
      <div class="grid grid-cols-1 gap-4 p-6 md:grid-cols-3">
        <div class="text-center">
          <p class="text-sm text-gray-500">Income Change</p>
          <p class="text-xl font-semibold" :class="report.comparison.income_change >= 0 ? 'text-green-600' : 'text-red-600'">
            <span v-if="report.comparison.income_change >= 0">+</span>{{ report.comparison.income_change }}%
          </p>
        </div>
        <div class="text-center">
          <p class="text-sm text-gray-500">Expense Change</p>
          <p class="text-xl font-semibold" :class="report.comparison.expense_change <= 0 ? 'text-green-600' : 'text-red-600'">
            <span v-if="report.comparison.expense_change >= 0">+</span>{{ report.comparison.expense_change }}%
          </p>
        </div>
        <div class="text-center">
          <p class="text-sm text-gray-500">Profit/Loss Change</p>
          <p class="text-xl font-semibold" :class="report.comparison.profit_change >= 0 ? 'text-green-600' : 'text-red-600'">
            <span v-if="report.comparison.profit_change >= 0">+</span>{{ report.comparison.profit_change }}%
          </p>
        </div>
      </div>
    </div>

    <!-- Detailed Report -->
    <div v-if="report" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Income Breakdown by Source -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Income by Source</h2>
        </div>
        <div class="p-6">
          <dl v-if="report.income?.by_source?.length" class="space-y-4">
            <div v-for="source in report.income.by_source" :key="source.source" class="flex justify-between">
              <dt class="text-sm text-gray-500 capitalize">{{ source.source || 'Other' }}</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(source.amount) }} <span class="text-gray-400">({{ source.count }})</span></dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">Total Income</dt>
              <dd class="text-sm font-bold text-green-600">MK {{ formatNumber(report.income.total) }}</dd>
            </div>
          </dl>
          <p v-else class="text-sm text-gray-500">No income recorded for this period.</p>
        </div>
      </div>

      <!-- Expense Breakdown by Category -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Expenses by Category</h2>
        </div>
        <div class="p-6">
          <dl v-if="report.expenses?.by_category?.length" class="space-y-4">
            <div v-for="category in report.expenses.by_category" :key="category.category" class="flex justify-between">
              <dt class="text-sm text-gray-500 capitalize">{{ category.category }}</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(category.amount) }} <span class="text-gray-400">({{ category.count }})</span></dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">Total Expenses</dt>
              <dd class="text-sm font-bold text-red-600">MK {{ formatNumber(report.expenses.total) }}</dd>
            </div>
          </dl>
          <p v-else class="text-sm text-gray-500">No expenses recorded for this period.</p>
        </div>
      </div>

      <!-- Income by Category -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Income by Category</h2>
        </div>
        <div class="p-6">
          <dl v-if="report.income?.by_category?.length" class="space-y-4">
            <div v-for="category in report.income.by_category" :key="category.category" class="flex justify-between">
              <dt class="text-sm text-gray-500 capitalize">{{ category.category }}</dt>
              <dd class="text-sm font-medium text-gray-900">MK {{ formatNumber(category.amount) }} <span class="text-gray-400">({{ category.count }})</span></dd>
            </div>
          </dl>
          <p v-else class="text-sm text-gray-500">No categorized income for this period.</p>
        </div>
      </div>

      <!-- Profit Summary -->
      <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
          <h2 class="text-lg font-medium text-gray-900">Profit & Loss Summary</h2>
        </div>
        <div class="p-6">
          <dl class="space-y-4">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Total Income</dt>
              <dd class="text-sm font-medium text-green-600">MK {{ formatNumber(report.summary?.total_income) }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Total Expenses</dt>
              <dd class="text-sm font-medium text-red-600">- MK {{ formatNumber(report.summary?.total_expenses) }}</dd>
            </div>
            <div class="flex justify-between border-t pt-4">
              <dt class="text-sm font-medium text-gray-900">{{ report.summary?.is_profit ? 'Net Profit' : 'Net Loss' }}</dt>
              <dd class="text-sm font-bold" :class="report.summary?.is_profit ? 'text-green-600' : 'text-red-600'">
                MK {{ formatNumber(report.summary?.net_profit_loss) }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm font-medium text-gray-900">Profit Margin</dt>
              <dd class="text-sm font-bold" :class="report.summary?.profit_margin >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ report.summary?.profit_margin?.toFixed(1) }}%
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Weekly Breakdown -->
    <div v-if="report?.weekly_breakdown?.length" class="mt-6 overflow-hidden rounded-lg bg-white shadow">
      <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900">Weekly Breakdown</h2>
      </div>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Week</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Income</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Expenses</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Net</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-for="(week, index) in report.weekly_breakdown" :key="index" class="hover:bg-gray-50">
            <td class="whitespace-nowrap px-6 py-4">
              <div class="text-sm font-medium text-gray-900">{{ formatDate(week.week_start) }} - {{ formatDate(week.week_end) }}</div>
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-green-600">
              MK {{ formatNumber(week.income) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm text-red-600">
              MK {{ formatNumber(week.expenses) }}
            </td>
            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium" :class="week.net >= 0 ? 'text-green-600' : 'text-red-600'">
              MK {{ formatNumber(week.net) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import axios from 'axios'
import Loader from '@/thetheme/components/Loader.vue'
import {
  ArrowTrendingDownIcon,
  ArrowTrendingUpIcon,
  BanknotesIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'

const processing = ref(true)
const report = ref(null)

const currentDate = new Date()
const selectedYear = ref(currentDate.getFullYear())
const selectedMonth = ref(currentDate.getMonth() + 1)

const years = Array.from({ length: 5 }, (_, i) => currentDate.getFullYear() - i)
const months = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December'
]

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-MW', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0)
}

const formatDate = (dateStr) => {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-MW', { month: 'short', day: 'numeric' })
}

const fetchReport = async () => {
  processing.value = true
  try {
    const response = await axios.get('/api/profit-loss/monthly', {
      params: { year: selectedYear.value, month: selectedMonth.value }
    })
    report.value = response.data.data
  } catch (error) {
    console.error('Error fetching report:', error)
  } finally {
    processing.value = false
  }
}

onMounted(() => fetchReport())
</script>
