<template>
  <IndexNoData v-if="!tasks.length" no-create />

  <Card v-else>
    <div class="border-b border-gray-200 px-6 py-4">
      <h3 class="text-base font-medium leading-6 text-gray-900">
        {{ __(title) }}
      </h3>
    </div>
    <div class="space-y-4 divide-y pb-4">
      <div
        v-for="task in tasks"
        :key="task.id"
        class="flex items-start px-6 pt-4"
      >
        <span
          class="inline-block h-5 w-5 cursor-pointer rounded-full bg-white text-white"
          :class="{
            'opacity-50': processing == task.id,
            'bg-blue-400': task.completed_at,
            'border border-gray-300': !task.completed_at,
          }"
          @click="taskComplete(task.id)"
        >
          <svg viewBox="0 0 16 16" fill="white">
            <path
              d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"
            />
          </svg>
        </span>

        <div class="-mt-1 ltr:pl-4 rtl:pr-4">
          <span
            class="block cursor-pointer hover:underline"
            @click="openModal(task.id)"
            >{{ task.title }}</span
          >

          <div class="mt-1.5 flex space-x-4 rtl:space-x-reverse">
            <div v-if="task.due_at" class="flex items-center py-1">
              <CalendarIcon class="h-4 w-4 text-gray-400" />
              <span class="text-xs text-gray-500 ltr:ml-2 rtl:mr-2">{{
                task.due_at
              }}</span>
            </div>

            <router-link
              v-slot="{ href, navigate }"
              custom
              :to="'/projects/' + task.project_id"
            >
              <a
                :href="href"
                class="flex cursor-pointer items-center text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:underline"
                @click="navigate"
              >
                <span
                  class="block h-2 w-2 flex-shrink-0 rounded-full"
                  :style="{ 'background-color': task.project.meta.color }"
                  aria-hidden="true"
                ></span>
                <span
                  class="truncate text-xs text-gray-500 ltr:ml-2 rtl:mr-2"
                  >{{ task.project.name }}</span
                >
              </a>
            </router-link>

            <span
              v-if="task.id == timer.taskId"
              class="flex cursor-pointer items-center"
              @click.stop="timer.stop"
            >
              <StopIcon class="h-4 w-4 text-red-500 hover:text-red-800" />
            </span>
            <span
              v-else
              class="flex cursor-pointer items-center"
              @click.stop="timer.start(task)"
            >
              <PlayIcon class="h-4 w-4 text-gray-500 hover:text-gray-800" />
            </span>
          </div>
        </div>

        <div class="ltr:ml-auto rtl:mr-auto">
          <UserAvatar :user="task.users[0]" class="h-6 w-6" />
        </div>
      </div>
    </div>
  </Card>
</template>

<script setup lang="ts">
  import { IndexNoData } from 'thetheme'
  import { Card } from 'thetheme'
  import { ref } from 'vue'
  import { UserAvatar } from 'thetheme'
  import { CalendarIcon, PlayIcon, StopIcon } from '@heroicons/vue/24/outline'
  import { useIndexStore, useModalsStore } from 'spack'
  import TaskModal from 'View/task/TaskModal.vue'
  import { useTimerStore } from 'Store/timer'
  import { axios } from 'spack'
  import type { ConcreteComponent } from 'vue'

  withDefaults(
    defineProps<{
      title: string
      tasks?: any[]
    }>(),
    {
      tasks: () => [],
    },
  )

  const index = useIndexStore('tasks')()
  const processing = ref<number | null>(null)
  const timer = useTimerStore()

  function taskComplete(id: number) {
    processing.value = id

    axios.patch(`tasks/${id}/complete`).then(() => {
      index.fetch()
    })
  }

  function openModal(id: number) {
    console.log('openModal')

    useModalsStore().add(TaskModal as ConcreteComponent, {
      id,
      page: 'tasks',
    })
  }
</script>
