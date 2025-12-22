<template>
  <div class="cursor-pointer" @click="play = !play">
    <StopIcon
      v-if="task.id == timer.taskId"
      class="w-5 text-red-500 hover:text-red-800"
      @click="timer.stop"
    />
    <PlayIcon
      v-else
      class="w-5 text-gray-500 hover:text-gray-800"
      @click="timer.start(task)"
    />
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { useTimerStore } from 'Store/timer'
  import { PlayIcon, StopIcon } from '@heroicons/vue/24/outline'

  defineProps<{
    task: any
  }>()

  const play = ref(false)
  const timer = useTimerStore()
</script>
