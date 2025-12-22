<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-200 pb-5">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">
          {{ __('AdSense Reports') }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          View your AdSense performance metrics by country
        </p>
        <div v-if="userAssignments && userAssignments.has_assignments" class="mt-2 flex flex-wrap gap-2">
          <span v-if="userAssignments.countries.length > 0" class="text-xs text-gray-600">
            <strong>{{ __('Your Countries') }}:</strong>
            <span v-for="(country, index) in userAssignments.countries" :key="country.country_code">
              {{ getCountryFlag(country.country_code) }} {{ country.country_name }}{{ index < userAssignments.countries.length - 1 ? ', ' : '' }}
            </span>
          </span>
          <span v-if="allUserWebsites.length > 0" class="text-xs text-gray-600 ml-4">
            <strong>{{ __('Your Websites') }}:</strong>
            <span v-for="(website, index) in allUserWebsites" :key="website.id">
              {{ website.website_url }}{{ index < allUserWebsites.length - 1 ? ', ' : '' }}
            </span>
          </span>
        </div>
      </div>

      <button
        @click="syncData"
        :disabled="syncing"
        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
      >
        <svg v-if="syncing" class="mr-2 h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ syncing ? __('Syncing...') : __('Sync Data') }}
      </button>
    </div>

    <!-- Performance Targets Summary -->
    <div v-if="userTargets && summary" class="rounded-lg bg-white border-2 border-indigo-200 p-6 shadow-md">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
          📊 {{ __('Your Performance vs Targets') }}
        </h3>
        <span class="text-xs text-gray-500">{{ __('For selected date range') }}</span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Impressions Performance -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-600">{{ __('Impressions') }}</span>
            <span
              :class="getStatusColor(getPerformanceStatus(summary.total_impressions, userTargets.daily_impressions_target))"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
            >
              {{ Math.round(getPerformancePercentage(summary.total_impressions, userTargets.daily_impressions_target)) }}%
            </span>
          </div>
          <div class="text-2xl font-bold text-gray-900">{{ formatNumberShort(summary.total_impressions) }}</div>
          <div class="text-xs text-gray-500 mt-1">Target: {{ formatNumberShort(userTargets.daily_impressions_target) }}/day</div>
          <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
            <div
              :class="{
                'bg-green-500': getPerformanceStatus(summary.total_impressions, userTargets.daily_impressions_target) === 'excellent',
                'bg-yellow-500': getPerformanceStatus(summary.total_impressions, userTargets.daily_impressions_target) === 'good',
                'bg-red-500': getPerformanceStatus(summary.total_impressions, userTargets.daily_impressions_target) === 'poor'
              }"
              class="h-2 rounded-full transition-all duration-500"
              :style="{ width: getPerformancePercentage(summary.total_impressions, userTargets.daily_impressions_target) + '%' }"
            ></div>
          </div>
        </div>

        <!-- Clicks Performance -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-600">{{ __('Clicks') }}</span>
            <span
              :class="getStatusColor(getPerformanceStatus(summary.total_clicks, userTargets.daily_clicks_target))"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
            >
              {{ Math.round(getPerformancePercentage(summary.total_clicks, userTargets.daily_clicks_target)) }}%
            </span>
          </div>
          <div class="text-2xl font-bold text-gray-900">{{ formatNumberShort(summary.total_clicks) }}</div>
          <div class="text-xs text-gray-500 mt-1">Target: {{ formatNumberShort(userTargets.daily_clicks_target) }}/day</div>
          <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
            <div
              :class="{
                'bg-green-500': getPerformanceStatus(summary.total_clicks, userTargets.daily_clicks_target) === 'excellent',
                'bg-yellow-500': getPerformanceStatus(summary.total_clicks, userTargets.daily_clicks_target) === 'good',
                'bg-red-500': getPerformanceStatus(summary.total_clicks, userTargets.daily_clicks_target) === 'poor'
              }"
              class="h-2 rounded-full transition-all duration-500"
              :style="{ width: getPerformancePercentage(summary.total_clicks, userTargets.daily_clicks_target) + '%' }"
            ></div>
          </div>
        </div>

        <!-- CPC Performance -->
        <div class="bg-gray-50 rounded-lg p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-600">{{ __('Average CPC') }}</span>
            <span
              :class="{
                'bg-green-100 text-green-800': summary.avg_cpc >= userTargets.min_cpc_target,
                'bg-red-100 text-red-800': summary.avg_cpc < userTargets.min_cpc_target
              }"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
            >
              {{ summary.avg_cpc >= userTargets.min_cpc_target ? '✓ Good' : '✗ Low' }}
            </span>
          </div>
          <div :class="{
            'text-green-600': summary.avg_cpc >= userTargets.min_cpc_target,
            'text-red-600': summary.avg_cpc < userTargets.min_cpc_target
          }" class="text-2xl font-bold">
            ${{ formatMoney(summary.avg_cpc) }}
          </div>
          <div class="text-xs text-gray-500 mt-1">Min Target: ${{ formatMoney(userTargets.min_cpc_target) }}</div>
        </div>
      </div>

      <!-- Focus Recommendation -->
      <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start">
          <svg class="h-5 w-5 text-blue-600 mt-0.5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <p class="text-sm font-medium text-blue-900">{{ __('Marketing Focus Tip') }}</p>
            <p class="text-sm text-blue-700 mt-1">
              {{ __('Check the country performance table below. Countries marked in') }}
              <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 mx-1">{{ __('red') }}</span>
              {{ __('need more marketing effort, while') }}
              <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mx-1">{{ __('green') }}</span>
              {{ __('countries are performing well.') }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Performance Highlight -->
    <div v-if="!loading && todayWebsites && todayWebsites.length > 0" class="rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 p-6 shadow-lg">
      <div>
        <h3 class="text-xl font-semibold text-white mb-4">
          {{ __('Today\'s Performance') }} ({{ formatDate(todayDate) }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="website in todayWebsites" :key="website.domain" class="bg-white bg-opacity-10 backdrop-blur-sm rounded-lg p-4 hover:bg-opacity-20 transition-all">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-white opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                </svg>
                <h4 class="text-white font-medium text-sm truncate">{{ website.domain }}</h4>
              </div>
            </div>
            <div class="space-y-2">
              <!-- Earnings - Admin Only -->
              <div v-if="isAdmin" class="flex justify-between items-center">
                <span class="text-white text-xs opacity-75">{{ __('Earnings') }}</span>
                <span class="text-white text-lg font-bold">${{ formatMoney(website.total_earnings) }}</span>
              </div>
              <!-- Stats Grid -->
              <div :class="isAdmin ? 'grid grid-cols-4 gap-2 pt-2 border-t border-white border-opacity-20' : 'grid grid-cols-4 gap-2'">
                <div>
                  <p class="text-white text-xs opacity-75">{{ __('Page Views') }}</p>
                  <p class="text-white text-sm font-semibold">{{ formatNumberShort(website.total_page_views) }}</p>
                </div>
                <div>
                  <p class="text-white text-xs opacity-75">{{ __('Impr') }}</p>
                  <p class="text-white text-sm font-semibold">{{ formatNumberShort(website.total_impressions) }}</p>
                </div>
                <div>
                  <p class="text-white text-xs opacity-75">{{ __('Clicks') }}</p>
                  <p class="text-white text-sm font-semibold">{{ formatNumberShort(website.total_clicks) }}</p>
                </div>
                <div>
                  <p class="text-white text-xs opacity-75">{{ __('CPC') }}</p>
                  <p class="text-white text-sm font-semibold">${{ formatMoney(website.avg_cpc) }}</p>
                </div>
              </div>
              <!-- RPM - Admin Only -->
              <div v-if="isAdmin" class="grid grid-cols-1 gap-2 pt-2">
                <div>
                  <p class="text-white text-xs opacity-75">{{ __('RPM') }}</p>
                  <p class="text-white text-sm font-semibold">${{ formatMoney(website.avg_page_rpm) }}</p>
                </div>
              </div>
              <!-- Traffic Sources from Analytics -->
              <div class="mt-3 pt-2 border-t border-white border-opacity-20">
                <p class="text-white text-xs opacity-75 mb-1">{{ __('Traffic Sources') }}</p>
                <div v-if="website.trafficSources && website.trafficSources.length > 0" class="flex flex-wrap gap-1">
                  <span
                    v-for="source in website.trafficSources"
                    :key="source.source + source.medium"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-white bg-opacity-20 text-white"
                    :title="formatNumber(source.sessions) + ' sessions, ' + formatNumber(source.page_views) + ' page views'"
                  >
                    {{ getTrafficSourceIcon(source.source) }} {{ source.source }}
                  </span>
                </div>
                <div v-else class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-white opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-white text-xs opacity-50">{{ __('No traffic data available. Grant service account access in GA4.') }}</span>
                </div>
              </div>

              <!-- Top 5 Countries -->
              <div v-if="website.topCountries && website.topCountries.length > 0" class="mt-3 pt-2 border-t border-white border-opacity-20">
                <p class="text-white text-xs opacity-75 mb-2">{{ __('Top 5 Countries') }}</p>
                <div class="space-y-1.5">
                  <div
                    v-for="country in website.topCountries"
                    :key="country.country_code"
                    class="flex items-center justify-between text-white text-xs"
                  >
                    <div class="flex items-center gap-1.5 flex-1 min-w-0">
                      <span class="text-sm">{{ getCountryFlag(country.country_code) }}</span>
                      <span class="opacity-90 truncate">{{ country.country_name }}</span>
                    </div>
                    <div class="flex gap-2 text-[10px] opacity-75 flex-shrink-0">
                      <span class="whitespace-nowrap">{{ formatNumberShort(country.page_views) }} Views</span>
                      <span class="whitespace-nowrap">{{ formatNumberShort(country.impressions) }} Impr</span>
                      <span class="whitespace-nowrap">{{ formatNumberShort(country.clicks) }} Clicks</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Total Summary - Admin Only -->
        <div v-if="isAdmin && todayWebsites.length > 1" class="mt-4 pt-4 border-t border-white border-opacity-30">
          <div class="flex items-center justify-between">
            <span class="text-white text-sm font-medium opacity-90">{{ __('Total Earnings Today') }}</span>
            <span class="text-white text-2xl font-bold">${{ formatMoney(todayTotalEarnings) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Date Range Picker -->
    <div class="rounded-lg bg-white p-4 shadow">
      <div class="flex items-center space-x-4">
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700">{{ __('Start Date') }}</label>
          <input
            v-model="startDate"
            type="date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="fetchReports"
          />
        </div>
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700">{{ __('End Date') }}</label>
          <input
            v-model="endDate"
            type="date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            @change="fetchReports"
          />
        </div>
        <div class="pt-6">
          <button
            @click="fetchReports"
            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
          >
            {{ __('Apply') }}
          </button>
        </div>
      </div>

      <!-- Latest Data Info -->
      <div v-if="latestDate" class="mt-3 text-sm text-gray-500">
        {{ __('Latest available data: ') }} {{ latestDate }}
      </div>
    </div>

    <!-- Loading State -->
    <Loader v-if="loading" size="40" color="#5850ec" class="mx-auto mt-8" />

    <!-- Charts Section -->
    <div v-if="!loading && dailyTrendData.length > 0" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <!-- Earnings Trend Chart -->
      <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ __('Earnings Trend') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
              Daily earnings over selected period
            </p>
          </div>
        </div>
        <div style="height: 300px">
          <LineChart
            id="earnings-chart"
            :data="earningsChartData"
            tooltip-label="Earnings ($)"
            background-color="rgba(99, 102, 241, 0.1)"
            border-color="#6366F1"
          />
        </div>
      </div>

      <!-- Impressions vs Clicks Chart -->
      <div class="overflow-hidden rounded-lg bg-white p-6 shadow">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ __('Impressions & Clicks') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
              Daily performance comparison
            </p>
          </div>
        </div>
        <div style="height: 300px">
          <BarChart
            id="impressions-clicks-chart"
            :data="impressionsClicksChartData"
            :labels="chartLabels"
          />
        </div>
      </div>

      <!-- Top Countries Pie Chart -->
      <div v-if="reports.length > 0" class="overflow-hidden rounded-lg bg-white p-6 shadow lg:col-span-2">
        <div class="mb-4 flex items-center justify-between border-b border-gray-100 pb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              {{ __('Top 10 Countries by Earnings') }}
            </h3>
            <p class="mt-1 text-xs text-gray-500">
              Revenue distribution by country
            </p>
          </div>
        </div>
        <div style="height: 400px">
          <DoughnutChart
            id="countries-chart"
            :data="topCountriesEarnings"
            :labels="topCountriesLabels"
          />
        </div>
      </div>
    </div>

    <!-- Summary Metrics -->
    <div v-else-if="summary" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Total Impressions -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Impressions') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          {{ formatNumber(summary.total_impressions) }}
        </dd>
      </div>

      <!-- Total Clicks -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Clicks') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          {{ formatNumber(summary.total_clicks) }}
        </dd>
      </div>

      <!-- Page Views -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Page Views') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          {{ formatNumber(summary.total_page_views) }}
        </dd>
      </div>

      <!-- Total Earnings -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Total Earnings') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">
          ${{ formatMoney(summary.total_earnings) }}
        </dd>
      </div>

      <!-- Average CPC -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Avg CPC') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          ${{ formatMoney(summary.avg_cpc) }}
        </dd>
      </div>

      <!-- Page RPM -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Page RPM') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          ${{ formatMoney(summary.avg_page_rpm) }}
        </dd>
      </div>

      <!-- Page CTR -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Page CTR') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          {{ formatPercent(summary.avg_page_ctr) }}%
        </dd>
      </div>

      <!-- Countries -->
      <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500">{{ __('Countries') }}</dt>
        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
          {{ summary.total_countries }}
        </dd>
      </div>
    </div>

    <!-- Website Performance Section -->
    <div v-if="!loading && websiteReports.length > 0" class="overflow-hidden rounded-lg bg-white shadow">
      <div class="px-4 py-5 sm:px-6 border-b border-gray-200 cursor-pointer hover:bg-gray-50" @click="showWebsitePerformance = !showWebsitePerformance">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-md font-medium text-gray-900">{{ __('Performance by Website') }}</h4>
            <p class="mt-1 text-sm text-gray-500">{{ __('See how each website is performing') }}</p>
          </div>
          <svg :class="['h-5 w-5 text-gray-500 transition-transform', showWebsitePerformance ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>
      <div v-show="showWebsitePerformance" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Website') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Countries') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Impressions') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Clicks') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Page Views') }}</th>
              <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('CPC') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="website in websiteReports" :key="website.domain" class="hover:bg-gray-50">
              <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                <div class="flex items-center">
                  <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                  </svg>
                  {{ website.domain }}
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                {{ website.countries_count }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                {{ formatNumber(website.total_impressions) }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                {{ formatNumber(website.total_clicks) }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                {{ formatNumber(website.total_page_views) }}
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                ${{ formatMoney(website.avg_cpc) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Country-wise Data Table -->
    <div v-if="!loading && reports.length > 0" class="overflow-hidden rounded-lg bg-white shadow">
      <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div class="flex items-center cursor-pointer" @click="showCountryPerformance = !showCountryPerformance">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              {{ __('Performance by Country') }}
            </h3>
            <svg :class="['ml-2 h-5 w-5 text-gray-500 transition-transform', showCountryPerformance ? 'rotate-180' : '']" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
          <div v-show="showCountryPerformance" class="flex items-center space-x-4">
            <!-- Search Box -->
            <div class="relative">
              <input
                v-model="searchQuery"
                @input="debounceSearch"
                type="text"
                :placeholder="__('Search countries...')"
                class="block w-64 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10"
              />
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
            <!-- Per Page Selector -->
            <select
              v-model="perPage"
              @change="fetchReports"
              class="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option :value="10">10 per page</option>
              <option :value="25">25 per page</option>
              <option :value="50">50 per page</option>
              <option :value="100">100 per page</option>
            </select>
          </div>
        </div>
      </div>
      <div v-show="showCountryPerformance" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                {{ __('Country') }}
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                {{ __('Impressions') }}
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                {{ __('Clicks') }}
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                {{ __('Page Views') }}
              </th>
              <th scope="col" class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                {{ __('CPC') }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="report in reports" :key="report.country_code" class="hover:bg-gray-50">
              <td class="whitespace-nowrap px-6 py-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <span class="text-2xl mr-2">{{ getCountryFlag(report.country_code) }}</span>
                    <div class="text-sm font-medium text-gray-900">
                      {{ report.country_name || report.country_code }}
                    </div>
                  </div>
                  <!-- Performance Badge -->
                  <span
                    v-if="userTargets"
                    :class="getStatusColor(getPerformanceStatus(report.total_impressions, userTargets.daily_impressions_target))"
                    class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ getStatusText(getPerformanceStatus(report.total_impressions, userTargets.daily_impressions_target)) }}
                  </span>
                </div>
                <!-- Performance Progress Bar (Impressions) -->
                <div v-if="userTargets && userTargets.daily_impressions_target" class="mt-2">
                  <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>{{ formatNumber(report.total_impressions) }} / {{ formatNumber(userTargets.daily_impressions_target) }}</span>
                    <span>{{ Math.round(getPerformancePercentage(report.total_impressions, userTargets.daily_impressions_target)) }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div
                      :class="{
                        'bg-green-500': getPerformanceStatus(report.total_impressions, userTargets.daily_impressions_target) === 'excellent',
                        'bg-yellow-500': getPerformanceStatus(report.total_impressions, userTargets.daily_impressions_target) === 'good',
                        'bg-red-500': getPerformanceStatus(report.total_impressions, userTargets.daily_impressions_target) === 'poor'
                      }"
                      class="h-1.5 rounded-full transition-all duration-500"
                      :style="{ width: getPerformancePercentage(report.total_impressions, userTargets.daily_impressions_target) + '%' }"
                    ></div>
                  </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                <div class="text-gray-900">{{ formatNumber(report.total_impressions) }}</div>
                <div v-if="userTargets" class="text-xs text-gray-500 mt-1">
                  Target: {{ formatNumber(userTargets.daily_impressions_target) }}
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                <div class="text-gray-900">{{ formatNumber(report.total_clicks) }}</div>
                <div v-if="userTargets" class="text-xs text-gray-500 mt-1">
                  Target: {{ formatNumber(userTargets.daily_clicks_target) }}
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                <div class="text-gray-900">{{ formatNumber(report.total_page_views) }}</div>
                <div v-if="userTargets" class="text-xs text-gray-500 mt-1">
                  Target: {{ formatNumber(userTargets.daily_page_views_target) }}
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                <div :class="{
                  'text-green-600 font-semibold': userTargets && report.avg_cpc >= userTargets.min_cpc_target,
                  'text-red-600': userTargets && report.avg_cpc < userTargets.min_cpc_target,
                  'text-gray-900': !userTargets
                }">
                  ${{ formatMoney(report.avg_cpc) }}
                </div>
                <div v-if="userTargets" class="text-xs text-gray-500 mt-1">
                  Min: ${{ formatMoney(userTargets.min_cpc_target) }}
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1 && showCountryPerformance" class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex items-center justify-between">
          <div class="flex flex-1 justify-between sm:hidden">
            <button
              @click="goToPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ __('Previous') }}
            </button>
            <button
              @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ __('Next') }}
            </button>
          </div>
          <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                {{ __('Showing') }}
                <span class="font-medium">{{ pagination.from }}</span>
                {{ __('to') }}
                <span class="font-medium">{{ pagination.to }}</span>
                {{ __('of') }}
                <span class="font-medium">{{ pagination.total_countries }}</span>
                {{ __('countries') }}
              </p>
            </div>
            <div>
              <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <!-- Previous Button -->
                <button
                  @click="goToPage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">{{ __('Previous') }}</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>

                <!-- Page Numbers -->
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="goToPage(page)"
                  :class="[
                    page === pagination.current_page
                      ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center border px-4 py-2 text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>

                <!-- Next Button -->
                <button
                  @click="goToPage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">{{ __('Next') }}</span>
                  <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading && reports.length === 0" class="rounded-lg bg-white p-12 text-center shadow">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No data available') }}</h3>
      <p class="mt-1 text-sm text-gray-500">
        {{ __('Try syncing data or selecting a different date range.') }}
      </p>
      <div class="mt-6">
        <button
          @click="syncData"
          type="button"
          class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ __('Sync Data') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, inject, computed } from 'vue'
import { Loader, LineChart, BarChart, DoughnutChart } from 'thetheme'
import axios from 'axios'

const __ = inject('__')

const loading = ref(false)
const syncing = ref(false)
const reports = ref([])
const websiteReports = ref([])
const summary = ref(null)
const latestDate = ref(null)
const dailyTrendData = ref([])
const userAssignments = ref(null)
const todayWebsites = ref([])
const todayDate = ref(null)
const todayTotalEarnings = ref(0)
const isAdmin = ref(false)
const userTargets = ref(null)

// Computed: Flatten all websites from all countries
const allUserWebsites = computed(() => {
  if (!userAssignments.value || !userAssignments.value.countries) {
    return []
  }

  const websites = []
  userAssignments.value.countries.forEach(country => {
    if (country.websites && Array.isArray(country.websites)) {
      country.websites.forEach(website => {
        websites.push({
          ...website,
          country_code: country.country_code,
          country_name: country.country_name
        })
      })
    }
  })

  return websites
})

// Helper: Calculate performance status
const getPerformanceStatus = (actual, target) => {
  if (!target || target === 0) return 'none'
  const percentage = (actual / target) * 100
  if (percentage >= 100) return 'excellent' // Green
  if (percentage >= 70) return 'good' // Yellow
  return 'poor' // Red
}

// Helper: Calculate performance percentage
const getPerformancePercentage = (actual, target) => {
  if (!target || target === 0) return 0
  return Math.min(((actual / target) * 100), 100)
}

// Helper: Get status color
const getStatusColor = (status) => {
  switch (status) {
    case 'excellent': return 'bg-green-100 text-green-800'
    case 'good': return 'bg-yellow-100 text-yellow-800'
    case 'poor': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

// Helper: Get status badge text
const getStatusText = (status) => {
  switch (status) {
    case 'excellent': return '✓ On Target'
    case 'good': return '⚠ Close'
    case 'poor': return '✗ Below Target'
    default: return 'No Target'
  }
}

// Collapse state for sections
const showWebsitePerformance = ref(true)
const showCountryPerformance = ref(true)

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 10,
  last_page: 1,
  total_countries: 0,
  from: 0,
  to: 0,
})
const perPage = ref(10)

// Search
const searchQuery = ref('')
let searchTimeout: any = null

// Date range - default to last 7 days
const today = new Date()
const sevenDaysAgo = new Date(today)
sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7)

const startDate = ref(sevenDaysAgo.toISOString().split('T')[0])
const endDate = ref(today.toISOString().split('T')[0])

// Format numbers with commas
const formatNumber = (num) => {
  if (!num) return '0'
  return new Intl.NumberFormat('en-US').format(num)
}

// Format numbers in short form (K, M)
const formatNumberShort = (num) => {
  if (!num) return '0'
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

// Format money to 2 decimal places
const formatMoney = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toFixed(2)
}

// Format percentage to 2 decimal places
const formatPercent = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toFixed(2)
}

// Format date for display
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

// Get country flag emoji
const getCountryFlag = (countryCode) => {
  if (!countryCode || countryCode === 'XX') return '🌍'
  const codePoints = countryCode
    .toUpperCase()
    .split('')
    .map(char => 127397 + char.charCodeAt(0))
  return String.fromCodePoint(...codePoints)
}

// Get traffic source icon
const getTrafficSourceIcon = (source) => {
  const sourceLower = source.toLowerCase()

  // Search engines
  if (sourceLower.includes('google')) return '🔍'
  if (sourceLower.includes('bing')) return '🔎'
  if (sourceLower.includes('yahoo')) return '🔍'

  // Social media
  if (sourceLower.includes('facebook')) return '📘'
  if (sourceLower.includes('twitter') || sourceLower.includes('x.com')) return '🐦'
  if (sourceLower.includes('linkedin')) return '💼'
  if (sourceLower.includes('instagram')) return '📷'
  if (sourceLower.includes('pinterest')) return '📌'
  if (sourceLower.includes('reddit')) return '🤖'
  if (sourceLower.includes('social')) return '👥'

  // Referrals
  if (sourceLower.includes('referral')) return '🔗'

  // Direct
  if (sourceLower.includes('direct')) return '🏠'

  // Email
  if (sourceLower.includes('email')) return '📧'

  // Paid
  if (sourceLower.includes('paid')) return '💰'

  // Default
  return '🌐'
}

// Chart data computed properties
const chartLabels = computed(() => {
  return dailyTrendData.value.map((item: any) => {
    const date = new Date(item.report_date)
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  })
})

const earningsChartData = computed(() => {
  return dailyTrendData.value.map((item: any) => ({
    x: item.report_date,
    y: parseFloat(item.total_earnings || 0)
  }))
})

const impressionsClicksChartData = computed(() => {
  return [
    {
      label: 'Impressions',
      data: dailyTrendData.value.map((item: any) => parseInt(item.total_impressions || 0)),
      backgroundColor: 'rgba(99, 102, 241, 0.8)',
      borderColor: '#6366F1',
      borderWidth: 1,
    },
    {
      label: 'Clicks',
      data: dailyTrendData.value.map((item: any) => parseInt(item.total_clicks || 0)),
      backgroundColor: 'rgba(236, 72, 153, 0.8)',
      borderColor: '#EC4899',
      borderWidth: 1,
    },
  ]
})

const topCountriesLabels = computed(() => {
  return reports.value.slice(0, 10).map((report: any) => report.country_name || report.country_code)
})

const topCountriesEarnings = computed(() => {
  return reports.value.slice(0, 10).map((report: any) => parseFloat(report.total_earnings || 0))
})

// Visible page numbers for pagination
const visiblePages = computed(() => {
  const pages: number[] = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page

  // Always show first page
  pages.push(1)

  // Show pages around current page
  for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
    pages.push(i)
  }

  // Always show last page
  if (last > 1) {
    pages.push(last)
  }

  return [...new Set(pages)].sort((a, b) => a - b)
})

// Go to specific page
const goToPage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page
    fetchReports()
  }
}

// Debounce search input
const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchReports()
  }, 500)
}

// Fetch today's website data specifically
const fetchTodayData = async () => {
  try {
    const today = new Date().toISOString().split('T')[0]
    todayDate.value = today

    // Fetch website data and traffic sources from Analytics
    const websiteResponse = await axios.get('/api/adsense/reports/by-website', {
      params: {
        start_date: today,
        end_date: today,
      },
    })

    if (websiteResponse.data.data && websiteResponse.data.data.length > 0) {
      todayWebsites.value = websiteResponse.data.data

      // Fetch country data for each website
      try {
        const countryResponse = await axios.get('/api/adsense/reports', {
          params: {
            start_date: today,
            end_date: today,
          },
        })

        if (countryResponse.data.data) {
          const allCountries = countryResponse.data.data

          // Add top 5 countries to each website
          todayWebsites.value = todayWebsites.value.map(website => {
            const websiteCountries = allCountries
              .filter(item => item.domain === website.domain)
              .sort((a, b) => b.impressions - a.impressions)
              .slice(0, 5) // Top 5 countries

            return {
              ...website,
              topCountries: websiteCountries
            }
          })
        }
      } catch (error) {
        console.warn('Failed to fetch country data:', error)
      }

      // Try to fetch traffic sources from Analytics
      try {
        const trafficResponse = await axios.get('/api/adsense/traffic-sources', {
          params: {
            start_date: today,
            end_date: today,
          },
        })

        if (trafficResponse.data.success && trafficResponse.data.data) {
          const trafficByDomain = trafficResponse.data.data

          // Add traffic sources to each website
          todayWebsites.value = todayWebsites.value.map(website => {
            const domainSources = trafficByDomain[website.domain] || []

            return {
              ...website,
              trafficSources: domainSources.slice(0, 5) // Top 5 sources
            }
          })
        }
      } catch (analyticsError) {
        console.warn('Analytics not configured, skipping traffic sources:', analyticsError)
        // Continue without traffic sources
      }

      // Calculate total earnings
      todayTotalEarnings.value = todayWebsites.value.reduce((sum, website) => {
        return sum + parseFloat(website.total_earnings || 0)
      }, 0)
    } else {
      todayWebsites.value = []
      todayTotalEarnings.value = 0
    }
  } catch (error) {
    console.error('Failed to fetch today\'s data:', error)
    todayWebsites.value = []
    todayTotalEarnings.value = 0
  }
}

// Fetch reports by country
const fetchReports = async () => {
  loading.value = true
  try {
    const [summaryResponse, reportsResponse, websiteResponse, dailyTrendResponse] = await Promise.all([
      axios.get('/api/adsense/reports/summary', {
        params: {
          start_date: startDate.value,
          end_date: endDate.value,
        },
      }),
      axios.get('/api/adsense/reports/by-country', {
        params: {
          start_date: startDate.value,
          end_date: endDate.value,
          search: searchQuery.value,
          per_page: perPage.value,
          page: pagination.value.current_page,
        },
      }),
      axios.get('/api/adsense/reports/by-website', {
        params: {
          start_date: startDate.value,
          end_date: endDate.value,
        },
      }),
      axios.get('/api/adsense/reports/daily-trend', {
        params: {
          start_date: startDate.value,
          end_date: endDate.value,
        },
      }),
    ])

    summary.value = summaryResponse.data.data
    reports.value = reportsResponse.data.data
    websiteReports.value = websiteResponse.data.data
    dailyTrendData.value = dailyTrendResponse.data.data

    // Update pagination info
    if (reportsResponse.data.meta) {
      pagination.value = {
        current_page: reportsResponse.data.meta.current_page,
        per_page: reportsResponse.data.meta.per_page,
        last_page: reportsResponse.data.meta.last_page,
        total_countries: reportsResponse.data.meta.total_countries,
        from: reportsResponse.data.meta.from || 0,
        to: reportsResponse.data.meta.to || 0,
      }
    }

    // Also fetch today's data
    await fetchTodayData()
  } catch (error) {
    console.error('Failed to fetch AdSense reports:', error)
  } finally {
    loading.value = false
  }
}

// Fetch latest available date
const fetchLatestDate = async () => {
  try {
    const response = await axios.get('/api/adsense/reports/latest-date')
    latestDate.value = response.data.data.latest_report_date
  } catch (error) {
    console.error('Failed to fetch latest date:', error)
  }
}

// Fetch user assignments
const fetchUserAssignments = async () => {
  try {
    const response = await axios.get('/api/adsense/user-assignments')
    userAssignments.value = response.data.data
    userTargets.value = response.data.data.targets

    // Check if user is admin
    await checkUserRole()
  } catch (error) {
    console.error('Failed to fetch user assignments:', error)
  }
}

// Check if user has admin role
const checkUserRole = async () => {
  try {
    const response = await axios.get('/api/user')
    const user = response.data

    // Check if user has Admin role
    if (user && user.roles) {
      isAdmin.value = user.roles.some(role => role.name === 'Admin')
    }
  } catch (error) {
    console.error('Failed to check user role:', error)
    isAdmin.value = false
  }
}

// Sync data from AdSense API
const syncData = async () => {
  syncing.value = true
  try {
    await axios.post('/api/adsense/sync', {
      start_date: startDate.value,
      end_date: endDate.value,
    })

    // Refresh data after sync
    await fetchReports()
    await fetchLatestDate()
  } catch (error) {
    console.error('Failed to sync AdSense data:', error)
    alert('Failed to sync data. Please check your AdSense API configuration.')
  } finally {
    syncing.value = false
  }
}

// Initialize
onMounted(async () => {
  await fetchUserAssignments()
  await fetchLatestDate()
  await fetchReports()
})
</script>
