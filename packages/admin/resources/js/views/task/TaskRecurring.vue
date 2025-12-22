<template>
  <section class="py-3.5">
    <div class="flex items-center px-2">
      <h2 class="text-sm font-medium text-gray-600">{{ __('Repeat') }}</h2>

      <span
        v-if="task.data.recurring_at"
        class="ml-2"
        :aria-label="task.data.meta.recurring.phrase"
        data-cooltipz-dir="bottom"
      >
        <ArrowPathIcon class="h-3 w-3 text-gray-600" />
      </span>

      <TaskRepeatDropdown v-if="can('task:update')" class="ml-auto" />
    </div>

    <div v-if="task.data.recurring_at" class="flex items-center px-2 pt-3">
      <span aria-label="Next Recurring At" data-cooltipz-dir="bottom">
        <ClockIcon class="h-4 w-4 text-gray-500" />
      </span>

      <p class="ml-2 text-xs text-gray-600">{{ task.data.recurring_at }}</p>
    </div>
  </section>
</template>

<script setup lang="ts">
  import { useTaskStore } from 'Store/task'
  import TaskRepeatDropdown from './TaskRepeatDropdown.vue'
  import { ArrowPathIcon, ClockIcon } from '@heroicons/vue/24/outline'

  const task = useTaskStore()
</script>
