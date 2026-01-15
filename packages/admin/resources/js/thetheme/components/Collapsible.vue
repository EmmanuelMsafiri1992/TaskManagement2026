<template>
  <div @click.prevent="isOpen = !isOpen">
    <slot name="trigger" :open="isOpen" />
  </div>

  <slot v-if="isOpen" name="content" />
</template>

<script setup lang="ts">
  import { ref } from 'vue'

  const props = withDefaults(defineProps<{
    open?: boolean
  }>(), {
    open: false
  })

  const isOpen = ref(props.open)
</script>
