<template>
  <div
    class="fixed inset-0 z-[250] overflow-y-auto bg-gray-500 bg-opacity-75 py-12 px-8"
    @click="hide"
  >
    <div
      class="modal-content mx-auto rounded-lg bg-white shadow-xl"
      :class="[width]"
      @click.stop
    >
      <slot />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useModalsStore } from 'spack'

  withDefaults(
    defineProps<{
      width?: string
    }>(),
    {
      width: 'max-w-2xl',
    },
  )

  function hide() {
    useModalsStore().pop()
  }
</script>
