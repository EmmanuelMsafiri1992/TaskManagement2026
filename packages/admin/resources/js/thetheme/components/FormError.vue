<template>
  <p v-if="error" class="mt-1 text-sm text-red-600">
    {{ Array.isArray(error) ? error[0] : error }}
  </p>
</template>

<script setup lang="ts">
defineProps<{
  error?: string | string[]
}>()
</script>
