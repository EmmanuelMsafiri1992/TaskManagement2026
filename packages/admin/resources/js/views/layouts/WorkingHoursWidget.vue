<template>
  <div v-if="workingHours" class="overflow-hidden rounded-lg bg-white shadow">
    <div class="p-5">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ClockIcon class="h-8 w-8" :class="workingHours.has_custom_hours ? 'text-indigo-500' : 'text-gray-400'" />
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-500">
              {{ __('Your Working Hours') }}
            </h3>
            <div class="mt-1 flex items-baseline">
              <p class="text-2xl font-semibold text-gray-900">
                {{ workingHours.formatted_start }} - {{ workingHours.formatted_end }}
              </p>
            </div>
          </div>
        </div>
        <div v-if="workingHours.has_custom_hours" class="flex-shrink-0">
          <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">
            {{ __('Custom Schedule') }}
          </span>
        </div>
      </div>

      <div v-if="workingHours.has_custom_hours" class="mt-4 border-t border-gray-100 pt-4">
        <div class="flex flex-col space-y-2">
          <div class="flex items-center text-sm">
            <CalendarIcon class="h-4 w-4 text-gray-400 mr-2" />
            <span class="text-gray-500">{{ __('Period') }}:</span>
            <span class="ml-1 font-medium text-gray-900">{{ workingHours.effective_period }}</span>
          </div>
          <div v-if="workingHours.reason" class="flex items-start text-sm">
            <InformationCircleIcon class="h-4 w-4 text-gray-400 mr-2 mt-0.5" />
            <span class="text-gray-500">{{ workingHours.reason }}</span>
          </div>
        </div>
      </div>

      <div v-else class="mt-4 text-sm text-gray-500">
        {{ __('Standard working hours apply to you.') }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { CalendarIcon, ClockIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'

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
