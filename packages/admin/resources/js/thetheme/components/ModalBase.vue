<template>
  <div
    class="fixed inset-0 z-[250] flex min-h-screen items-center justify-center overflow-y-auto bg-gray-500 bg-opacity-75 p-4"
    @click="hide"
  >
    <div
      class="modal-content relative w-full rounded-lg bg-white shadow-xl"
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

  const emit = defineEmits(['close'])

  function hide() {
    emit('close')
    useModalsStore().pop()
  }
</script>
