<template>
  <section class="py-3.5">
    <div class="flex items-center px-2">
      <h2 class="text-sm font-medium text-gray-600">{{ __('Share') }}</h2>

      <TaskShareDropdown class="ml-auto" />
    </div>
  </section>
</template>

<script setup lang="ts">
  import TaskShareDropdown from './TaskShareDropdown.vue'
</script>
