<template>
  <Dropdown>
    <template #trigger>
      <button
        class="relative bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none"
      >
        <ClockIcon class="h-6 w-6" />

        <div
          v-if="timer.timers.length"
          class="absolute -top-0.5 z-40 flex h-[1.125rem] w-[1.125rem] items-center justify-center rounded-full bg-red-600 text-[0.65rem] text-white ltr:-right-0.5 rtl:-left-0.5"
        >
          {{ timer.timers.length > 9 ? '9+' : timer.timers.length }}
        </div>
      </button>
    </template>

    <template #content>
      <div
        class="absolute mt-2 w-64 origin-top-right overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 rtl:left-0"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <p
          v-if="!timer.timers.length"
          class="py-4 text-center text-sm text-gray-700"
        >
          {{ __('No Timers!') }}
        </p>

        <div v-else>
          <ul class="divide-y divide-gray-100">
            <li
              v-for="time in timer.timers"
              :key="time.id"
              class="px-4 py-2 text-sm hover:bg-gray-100"
            >
              <h3 v-if="time.task" class="flex items-start justify-between text-gray-900">
                {{ time.task.title }}
                <TaskItemTimer
                  v-if="isCurrentUserTask(time.user_id)"
                  class="ml-3"
                  :task="time.task"
                />
              </h3>
              <h4 class="mt-2 flex items-center text-xs text-gray-600">
                Started at: {{ time.started_at }}
              </h4>
              <div v-if="time.task && time.task.project" class="mt-1.5 flex items-center">
                <router-link
                  v-slot="{ href, navigate }"
                  custom
                  :to="'/projects/' + time.task.project.id"
                >
                  <a
                    :href="href"
                    class="flex items-center text-xs text-gray-600 transition duration-150 ease-in-out hover:underline"
                    role="menuitem"
                    @click="navigate"
                  >
                    <span
                      class="mr-1.5 block h-2 w-2 rounded-full"
                      :style="{
                        backgroundColor: time.task.project.meta?.color,
                      }"
                      aria-hidden="true"
                      >&nbsp;</span
                    >
                    {{ time.task.project.name }}
                  </a>
                </router-link>

                <span class="px-2">-</span>

                <router-link
                  v-slot="{ href, navigate }"
                  custom
                  :to="'/users/' + time.user.id"
                >
                  <a
                    :href="href"
                    class="flex items-center text-xs text-gray-600 transition duration-150 ease-in-out hover:underline"
                    role="menuitem"
                    @click="navigate"
                  >
                    <UserAvatar
                      class="mr-1.5 h-4 w-4 text-[9px] font-semibold"
                      :avatar="time.user.avatar"
                    />
                    {{ time.user.name }}
                  </a>
                </router-link>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { useTimerStore } from 'Store/timer'
  import { Dropdown, UserAvatar } from 'thetheme'
  import { ClockIcon } from '@heroicons/vue/24/outline'
  import TaskItemTimer from './TaskItemTimer.vue'
  import { appData } from '@/app-data'

  const timer = useTimerStore()
  timer.fetch()

  function isCurrentUserTask(user_id: number) {
    return appData.user.id == user_id
  }

  // let count = ref()
</script>
