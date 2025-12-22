<template>
  <Teleport to="body">
    <div
      v-if="flash.message"
      class="fixed h-10 px-4 rounded flex items-center text-white text-sm"
      :class="{ 'bg-green-600': flash.type == 'success', 'bg-red-500': flash.type == 'danger' }"
      style="right: 2rem; bottom: 7%; touch-action: pan-y; z-index: 2000;"
    >
      {{ flash.message }}

      <XMarkIcon
        class="w-5 ml-2 cursor-pointer"
        @click="flash.hide"
      />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
  import { useFlashStore } from 'spack'
  import { XMarkIcon } from '@heroicons/vue/24/outline'

  const flash = useFlashStore()
</script>
