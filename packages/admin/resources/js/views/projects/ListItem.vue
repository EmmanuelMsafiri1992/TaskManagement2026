<template>
  <div
    id="list-item"
    :data-id="task.id"
    class="mb-2 block cursor-pointer rounded bg-white px-2.5 py-1 shadow-md"
    @click="openModal(task.id)"
  >
    <div class="flex py-1.5">
      <span
        class="block flex-1 text-sm"
        :class="{
          'text-gray-500 line-through': task.completed_at,
          'text-gray-700': !task.completed_at,
        }"
        v-text="task.title"
      ></span>

      <!-- <div v-show="isCurrentUserTask">
        <span @click.stop="timer.stop" v-if="task.id == timer.taskId">
          <StopIcon class="w-4 h-4 text-red-500 hover:text-red-800"/>
        </span>
        <span @click.stop="timer.start(task)" v-else>
          <PlayIcon class="w-4 h-4 text-gray-500 hover:text-gray-800"/>
        </span>
      </div> -->
    </div>

    <div class="flex items-center">
      <div class="flex flex-1">
        <div
          v-if="task.recurring_at"
          class="flex items-center py-1 ltr:mr-2.5 rtl:ml-2.5"
        >
          <span
            :aria-label="task.meta.recurring.phrase"
            data-cooltipz-dir="right"
            data-cooltipz-size="medium"
          >
            <ArrowPathIcon class="h-4 w-4 text-gray-400" />
          </span>
        </div>

        <div
          v-show="task.due_at"
          class="flex items-center py-1 ltr:mr-2.5 rtl:ml-2.5"
        >
          <CalendarIcon class="h-4 w-4 text-gray-400" />
          <span
            class="text-xs text-gray-500 ltr:ml-1 rtl:mr-1"
            v-text="task.human_due_date"
          ></span>
        </div>

        <div
          v-show="task.comments.length"
          class="flex items-center py-1 ltr:mr-2.5 rtl:ml-2.5"
        >
          <ChatBubbleOvalLeftEllipsisIcon class="h-4 w-4 text-gray-400" />
          <span
            class="text-xs text-gray-500 ltr:ml-1 rtl:mr-1"
            v-text="task.comments.length"
          ></span>
        </div>

        <div
          v-if="
            task.checklists.length
              ? task.checklists[0].checklist_items.length
              : 0
          "
          class="flex items-center py-1 ltr:mr-2.5 rtl:ml-2.5"
        >
          <CheckCircleIcon class="h-4 w-4 text-gray-400" />
          <span
            class="text-xs tracking-widest text-gray-500 ltr:ml-0.5 rtl:mr-0.5"
            v-text="countChecklistItems(task.checklists)"
          ></span>
        </div>
      </div>

      <div
        v-if="task.users.length"
        class="flex items-center py-1 ltr:ml-auto rtl:mr-auto"
      >
        <span v-show="checkIsCurrentUserTask()"></span>
        <img
          v-show="task.users[0].avatar"
          class="h-4 w-4 rounded-full"
          :src="task.users[0].avatar"
          alt="avatar"
          :title="task.users[0].name"
        />

        <span
          v-show="!task.users[0].avatar"
          :title="task.users[0].name"
          class="inline-block h-4 w-4 overflow-hidden rounded-full bg-gray-100 text-gray-300"
        >
          <svg fill="currentColor" viewBox="0 0 24 24">
            <path
              d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
        </span>

        <span
          v-show="task.users.length > 1"
          class="text-[9px] text-gray-600 ltr:ml-px rtl:mr-px"
          >+{{ task.users.length - 1 }}</span
        >
      </div>
    </div>

    <div v-if="task.labels.length" class="mt-1 flex flex-wrap">
      <span
        v-for="label in task.labels"
        :key="label.id"
        class="mb-1 inline-flex items-center rounded-md px-1.5 py-1 text-[10px] font-semibold leading-none text-white ltr:mr-1 rtl:ml-1"
        :style="{ 'background-color': label.meta.color }"
      >
        {{ label.name }}
      </span>
    </div>

    <div
      v-if="task.id == timer.taskId && timer.isTimerRunning"
      class="mt-1.5 mb-1.5 flex items-center"
    >
      <span @click.stop="timer.stop">
        <StopIcon
          class="h-[0.875rem] w-[0.875rem] text-red-500 hover:text-red-800"
        />
      </span>

      <p class="ml-1 text-xs text-red-600">
        {{ timer.currentTaskTimer.h.toString().padStart(2, '0') }}:{{
          timer.currentTaskTimer.m.toString().padStart(2, '0')
        }}:{{ timer.currentTaskTimer.s.toString().padStart(2, '0') }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import TaskModal from 'View/task/TaskModal.vue'
  import { useModalsStore } from 'spack'
  import { useTimerStore } from 'Store/timer'
  import { appData } from '@/app-data'
  import {
    ArrowPathIcon,
    CalendarIcon,
    ChatBubbleOvalLeftEllipsisIcon,
    CheckCircleIcon,
    StopIcon,
  } from '@heroicons/vue/24/outline'
  import type { ConcreteComponent } from 'vue'

  const props = defineProps<{
    task: any
  }>()

  // const processing = ref(false)
  const timer = useTimerStore()
  const isCurrentUserTask = ref(false)

  // function taskComplete(id) {
  //   processing.value = true

  //   axios.patch(`tasks/${id}/complete`).then(({data}) => {
  //     processing.value = false
  //     props.task.completed_at = data.completed_at
  //   })
  // }

  function openModal(id = null) {
    const url = `/projects/${props.task.project_id}/tasks/${id}`
    history.replaceState(history.state, '', url)

    useModalsStore().add(TaskModal as ConcreteComponent, {
      id,
      width: 'max-w-3xl',
    })
  }

  // function countSubTasks(tasks) {
  //   return (
  //     tasks.filter((x) => x.completed_at != null).length + '/' + tasks.length
  //   )
  // }

  function countChecklistItems(items: any) {
    return (
      items[0].checklist_items.filter((x: any) => x.completed_at != null)
        .length +
      '/' +
      items[0].checklist_items.length
    )
  }

  // function handleTimer() {
  //   console.log('handleTimer!')
  // }

  function checkIsCurrentUserTask() {
    if (props.task.users.length) {
      if (props.task.users.find((x: any) => x.id == appData.user.id)) {
        return (isCurrentUserTask.value = true)
      }
    }

    isCurrentUserTask.value = false
  }

  checkIsCurrentUserTask()
</script>
