<template>
  <div v-if="timer.isTimerRunning" class="flex items-center">
    <span class="block cursor-pointer" @click.stop="timer.stop">
      <StopIcon
        class="h-[1.125rem] w-[1.125rem] text-red-600 hover:text-red-800"
      />
    </span>

    <p
      class="ml-2 w-[4.500rem] text-base text-red-600"
      :aria-label="`${timer.taskTitle} (${timer.projectName})`"
      data-cooltipz-dir="bottom"
    >
      {{ timer.currentTaskTimer.h.toString().padStart(2, '0') }}:{{
        timer.currentTaskTimer.m.toString().padStart(2, '0')
      }}:{{ timer.currentTaskTimer.s.toString().padStart(2, '0') }}
    </p>
  </div>
</template>

<script setup lang="ts">
  import { useTimerStore } from 'Store/timer'
  import { StopIcon } from '@heroicons/vue/24/outline'

  const timer = useTimerStore()

  console.log('header timer')
  console.log(timer)

  // function isCurrentUserTask(user_id) {
  //   return Config.user.id == user_id
  // }
</script>
