<template>
  <section class="py-3.5">
    <div class="flex items-center px-2">
      <h2 class="text-sm font-medium text-gray-600">{{ __('Assignee') }}</h2>

      <TaskAssign v-if="can('task:update')" class="ml-auto"/>
    </div>

    <div v-if="task.data.users.length" class="flex flex-wrap px-2 pt-2">
      <UserAvatar
        v-for="user in task.data.users"
        :key="user.id"
        data-cooltipz-dir="top"
        :aria-label="user.name"
        class="mr-1 mb-1 h-6 w-6 border-2 border-white"
        :avatar="user.avatar"
      />
    </div>
  </section>
</template>

<script setup lang="ts">
  import { UserAvatar } from 'thetheme'
  import { useTaskStore } from 'Store/task'
  import TaskAssign from './TaskAssign.vue'

  const task = useTaskStore()
</script>
