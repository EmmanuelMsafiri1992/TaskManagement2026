<template>
  <Teleport to="body">
    <div
      v-if="show"
      class="fixed inset-0 z-[9999] flex items-center justify-center overflow-y-auto"
    >
      <!-- Backdrop - cannot be clicked to close -->
      <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity"></div>

      <!-- Modal -->
      <div class="relative z-10 w-full max-w-lg transform overflow-hidden rounded-xl bg-white shadow-2xl transition-all sm:mx-4">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-4">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
              <ExclamationTriangleIcon class="h-6 w-6 text-white" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-white">
                {{ getTitle() }}
              </h3>
              <p class="text-sm text-amber-100">
                {{ __('Please provide an explanation') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-5">
          <!-- Report Details -->
          <div class="mb-5 rounded-lg bg-gray-50 p-4">
            <div class="flex items-start gap-3">
              <ClockIcon class="mt-0.5 h-5 w-5 flex-shrink-0 text-gray-400" />
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900">
                  {{ report?.reason_label }}
                </p>
                <p class="mt-1 text-sm text-gray-600">
                  <span class="font-medium">{{ __('From') }}:</span> {{ report?.inactive_from_full }}
                </p>
                <p class="text-sm text-gray-600">
                  <span class="font-medium">{{ __('To') }}:</span> {{ report?.inactive_until_full }}
                </p>
                <p class="mt-2 text-sm text-gray-600">
                  <span class="font-medium">{{ __('Duration') }}:</span>
                  <span class="ml-1 inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                    {{ report?.duration }}
                  </span>
                </p>
                <p v-if="report?.page_title" class="mt-2 text-sm text-gray-600">
                  <span class="font-medium">{{ __('Page') }}:</span> {{ report?.page_title }}
                </p>
              </div>
            </div>
          </div>

          <!-- Question based on reason type -->
          <p class="mb-3 text-sm text-gray-700">
            {{ getQuestion() }}
          </p>

          <!-- Explanation textarea -->
          <div>
            <label for="explanation" class="sr-only">{{ __('Explanation') }}</label>
            <textarea
              id="explanation"
              v-model="explanation"
              rows="4"
              :placeholder="getPlaceholder()"
              class="block w-full rounded-lg border-gray-300 shadow-sm transition-colors focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': error }"
              @input="error = ''"
            ></textarea>
            <p v-if="error" class="mt-1 text-sm text-red-600">
              {{ error }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
              {{ __('Minimum 10 characters required') }} ({{ explanation.length }}/10)
            </p>
          </div>

          <!-- Pending count -->
          <p v-if="pendingCount > 1" class="mt-3 text-center text-sm text-gray-500">
            {{ pendingCount - 1 }} {{ __('more report(s) pending after this one') }}
          </p>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 bg-gray-50 px-6 py-4">
          <button
            type="button"
            :disabled="submitting || explanation.length < 10"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            @click="handleSubmit"
          >
            <span v-if="submitting" class="flex items-center justify-center gap-2">
              <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              {{ __('Submitting...') }}
            </span>
            <span v-else>{{ __('Submit Explanation') }}</span>
          </button>

          <p class="mt-3 text-center text-xs text-gray-500">
            {{ __('This popup cannot be closed until you provide an explanation.') }}
          </p>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, inject, watch } from 'vue'
import { ExclamationTriangleIcon, ClockIcon } from '@heroicons/vue/24/outline'

interface InactivityReport {
  id: number
  inactive_from: string
  inactive_until: string
  inactive_from_full: string
  inactive_until_full: string
  duration: string
  duration_minutes: number
  reason_type: 'same_page' | 'computer_inactive' | 'power_outage' | 'session_gap'
  reason_label: string
  page_url: string | null
  page_title: string | null
  detected_at: string
}

const props = defineProps<{
  show: boolean
  report: InactivityReport | null
  pendingCount: number
}>()

const emit = defineEmits<{
  submit: [reportId: number, explanation: string]
}>()

const __ = inject('__') as (key: string) => string

const explanation = ref('')
const error = ref('')
const submitting = ref(false)

// Reset explanation when report changes
watch(() => props.report?.id, () => {
  explanation.value = ''
  error.value = ''
})

function getTitle(): string {
  switch (props.report?.reason_type) {
    case 'power_outage':
      return __('Unexpected Session End Detected')
    case 'same_page':
      return __('Extended Time on Same Page')
    case 'computer_inactive':
      return __('Inactivity Detected')
    default:
      return __('Activity Report Required')
  }
}

function getQuestion(): string {
  switch (props.report?.reason_type) {
    case 'power_outage':
      return __('It looks like your previous session ended unexpectedly. Did you experience a power outage or is there something else that happened?')
    case 'same_page':
      return __('You\'ve been on the same page for an extended period. Would you mind sharing what you were reading or doing?')
    case 'computer_inactive':
      return __('You appear to have been away from your computer. Where were you and what were you doing during this time?')
    default:
      return __('Please explain what you were doing during this inactive period.')
  }
}

function getPlaceholder(): string {
  switch (props.report?.reason_type) {
    case 'power_outage':
      return __('e.g., Yes, there was load shedding in my area, or I had to leave urgently...')
    case 'same_page':
      return __('e.g., I was reading the documentation carefully, or I was on a call while referencing this page...')
    case 'computer_inactive':
      return __('e.g., I was in a meeting, or I stepped out for a bathroom break...')
    default:
      return __('Please provide details about your activity...')
  }
}

async function handleSubmit() {
  if (!props.report) return

  if (explanation.value.length < 10) {
    error.value = __('Please provide a more detailed explanation (minimum 10 characters)')
    return
  }

  submitting.value = true

  try {
    emit('submit', props.report.id, explanation.value)
  } finally {
    submitting.value = false
  }
}

// Prevent escape key from closing
function handleKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && props.show) {
    e.preventDefault()
    e.stopPropagation()
  }
}

// Add keydown listener when mounted
if (typeof window !== 'undefined') {
  window.addEventListener('keydown', handleKeydown, true)
}
</script>
