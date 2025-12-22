<template>
  <section class="py-3.5">
    <div class="flex items-center px-2">
      <h2 class="text-sm font-medium text-gray-600">{{ __('Labels') }}</h2>

      <TaskLabelsDropdown v-if="can('task:update')" class="ml-auto"/>
    </div>

    <div v-if="task.data.labels.length" class="flex flex-wrap px-2 pt-2">
      <span
        v-for="label in task.data.labels"
        :key="label.id"
        class="mb-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white ltr:mr-2 rtl:ml-2"
        :style="{ 'background-color': label.meta.color }"
      >
        <svg
          class="-ml-0.5 mr-1.5 h-2 w-2 text-white"
          fill="currentColor"
          viewBox="0 0 8 8"
        >
          <circle cx="4" cy="4" r="3" />
        </svg>
        {{ label.name }}
      </span>
    </div>
  </section>
</template>

<script setup lang="ts">
  import { useTaskStore } from 'Store/task'
  import TaskLabelsDropdown from './TaskLabelsDropdown.vue'

  const task = useTaskStore()
</script>
