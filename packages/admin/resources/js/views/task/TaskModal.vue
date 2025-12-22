<template>
  <ModalBase width="max-w-3xl">
    <div
      v-if="task.loading"
      class="flex justify-center rounded-lg bg-gray-300 py-28"
    >
      <Loader size="40" color="#5850ec" />
    </div>

    <div v-else>
      <div class="flex border-b border-gray-200 shadow-sm">
        <div class="flex items-center px-6 text-sm text-gray-700">
          <span
            class="inline-block h-2.5 w-2.5 rounded-full ltr:mr-2 rtl:ml-2"
            :style="{ 'background-color': task.data.project.meta.color }"
          >
            &nbsp;
          </span>
          <span>{{ task.data.project.name }}</span>
          <span class="px-2">/</span>
          <span>{{ task.data.project_list.name }}</span>
        </div>

        <!-- <div class="px-6 flex items-center space-x-2">
          <button @click="quickScroll('subtasks')" class="text-xs text-gray-700 hover:text-gray-900">Sub-tasks</button>
          <span class="text-gray-700">-</span>
          <button @click="quickScroll('comments')" class="text-xs text-gray-700 hover:text-gray-900">Comments</button>
        </div> -->

        <div class="ml-auto flex items-center">
          <!-- <div v-if="can('task:delete')">
            <div class="flex items-center bg-white pl-4" :class="{ 'opacity-50': processing }" v-if="deleteConfirmation">
              <span class="text-gray-600 text-sm mr-2">Are you sure to delete?</span>
              <CheckCircleIcon class="w-5 h-5 text-green-600 cursor-pointer hover:text-green-800 mr-1" @click="deleteTask"/>
              <XCircleIcon class="w-5 h-5 text-red-600 cursor-pointer hover:text-red-800" @click="deleteConfirmation = false"/>
            </div>

            <span @click="deleteConfirmation = true" v-else>
              <TrashIcon class="w-5 h-5 text-gray-500 hover:text-gray-800 cursor-pointer ltr:mr-1 rtl:ml-1"/>
            </span>
          </div> -->

          <span
            class="cursor-pointer px-4 py-4 text-gray-600 hover:text-gray-800"
            @click="close"
          >
            <svg class="h-6 w-6" viewBox="0 0 21 21">
              <g
                fill="none"
                fill-rule="evenodd"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                transform="translate(5 5)"
              >
                <path d="m10.5 10.5-10-10z" />
                <path d="m10.5.5-10 10" />
              </g>
            </svg>
          </span>
        </div>
      </div>

      <div class="flex">
        <main class="flex-1 px-6 pt-6 pb-8">
          <TaskMain />
          <!-- <SubTasks/> -->
          <Checklist />
          <TaskComments />
        </main>

        <aside
          class="w-60 divide-y divide-gray-200 rounded-br-lg bg-gray-50 px-6 py-6"
        >
          <TaskTimer />
          <TaskProjectList />
          <TaskAssignee />
          <TaskDueDate />
          <TaskLabels />
          <!-- <TaskPriority/> -->
          <TaskRecurring />
          <TaskDelete v-if="can('task:delete')"/>
          <TaskShare />
        </aside>
      </div>
    </div>
  </ModalBase>
</template>

<script setup lang="ts">
  import { ModalBase } from 'thetheme'
  import { Loader } from 'thetheme'
  import { useTaskStore } from 'Store/task'
  import { useModalsStore } from 'spack'
  import TaskMain from './TaskMain.vue'
  import Checklist from './Checklist.vue'
  import TaskShare from './TaskShare.vue'
  import TaskDelete from './TaskDelete.vue'
  import TaskRecurring from './TaskRecurring.vue'
  import TaskComments from './TaskComments.vue'
  import TaskAssignee from './TaskAssignee.vue'
  import TaskLabels from './TaskLabels.vue'
  import TaskProjectList from './TaskProjectList.vue'
  import TaskTimer from './TaskTimer.vue'
  import TaskDueDate from './TaskDueDate.vue'

  const props = defineProps<{
    id: number
  }>()

  const task = useTaskStore()

  task.fetch(props.id)

  useModalsStore().onHide(function () {
    if (window.location.pathname.split('/')[1] == 'projects') {
      const url = `/projects/${task.data.project_id}`
      history.replaceState(history.state, '', url)
    }
  })

  function close() {
    useModalsStore().pop()

    // const url = `/projects/${task.data.project_id}`
    // history.replaceState(history.state, '', url)
  }
</script>
