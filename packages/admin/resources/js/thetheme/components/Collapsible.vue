<template>
  <div @click.prevent="isOpen = !isOpen">
    <slot name="trigger" :open="isOpen" />
  </div>

  <slot v-if="isOpen" name="content" />
</template>

<script setup lang="ts">
  import { ref } from 'vue'

  const props = defineProps<{
    open: boolean
  }>()

  const isOpen = ref(false)

  props.open && (isOpen.value = true)
</script>
