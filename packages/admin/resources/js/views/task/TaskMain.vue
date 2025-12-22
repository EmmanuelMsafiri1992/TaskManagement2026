<template>
  <div class="flex">
    <TaskCheckbox />

    <div class="flex-1 pl-4">
      <TaskContentForm v-if="task.isContentForm" />
      <TaskContent v-else />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useTaskStore } from 'Store/task'
  import TaskCheckbox from './TaskCheckbox.vue'
  import TaskContent from './TaskContent.vue'
  import TaskContentForm from './TaskContentForm.vue'

  const task = useTaskStore()
</script>
