<template>
  <div class="mb-4 flex items-center">
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Tasks') }}</h1>

    <div class="flex ltr:ml-auto rtl:mr-auto">
      <nav class="flex space-x-2 rounded-md" aria-label="Tabs">
        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800':
              index.params?.type == null && project == null,
            'text-gray-600': index.params.type != null || project != null,
          }"
          @click="choose()"
        >
          <InboxIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ __('Inbox') }}</span
          >
        </span>

        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': index.params.type == 'today',
            'text-gray-600': index.params.type != 'today',
          }"
          @click="choose('today')"
        >
          <CalendarIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ __('Today') }}</span
          >
        </span>

        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': index.params.type == 'upcoming',
            'text-gray-600': index.params.type != 'upcoming',
          }"
          @click="choose('upcoming')"
        >
          <BeakerIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ __('Upcoming') }}</span
          >
        </span>

        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': index.params.type == 'overdue',
            'text-gray-600': index.params.type != 'overdue',
          }"
          @click="choose('overdue')"
        >
          <BoltIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ __('Overdue') }}</span
          >
        </span>

        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': index.params.type == 'completed',
            'text-gray-600': index.params.type != 'completed',
          }"
          @click="choose('completed')"
        >
          <CheckCircleIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ __('Completed') }}</span
          >
        </span>
      </nav>

      <div class="group relative ltr:ml-2 rtl:mr-2">
        <div
          v-if="project"
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': project,
            'text-gray-600': !project,
          }"
        >
          <span
            class="block h-2 w-2 flex-shrink-0 rounded-full"
            :style="{ 'background-color': projectColor }"
          ></span>
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ projectName }}</span
          >
        </div>

        <div
          v-else
          class="group flex cursor-pointer items-center rounded-md px-3 py-2 text-gray-600"
        >
          <FolderIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
            >{{ __('Projects') }}</span
          >
        </div>

        <div
          class="absolute top-full hidden group-hover:block ltr:right-0 rtl:left-0"
        >
          <div class="h-1.5">&nbsp;</div>
          <div
            class="min-w-[12rem] rounded-md bg-white ring-1 ring-black ring-opacity-5"
          >
            <div class="py-1" role="none">
              <p
                v-for="projectItem in projects"
                :key="projectItem.id"
                class="flex cursor-pointer items-center px-4 py-2 text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
                @click="
                  chooseProject(
                    projectItem.id,
                    projectItem.name,
                    projectItem.meta.color,
                  )
                "
              >
                <span
                  class="block h-2.5 w-2.5 flex-shrink-0 rounded-full"
                  :style="{ 'background-color': projectItem.meta.color }"
                  aria-hidden="true"
                ></span>
                <span class="truncate ltr:ml-2.5 rtl:mr-2.5">{{
                  projectItem.name
                }}</span>
              </p>

              <p
                v-show="!projects.length"
                class="flex items-center px-4 py-3 text-sm leading-5 text-gray-600"
              >
                {{ __('No projects') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="index.fetching" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <IndexNoData v-if="Array.isArray(index.data)" no-create />

    <div v-else class="space-y-4">
      <TasksList
        v-if="index.data['today']"
        title="Today"
        :tasks="index.data['today']"
      />
      <TasksList
        v-if="index.data['overdue']"
        title="Overdue"
        :tasks="index.data['overdue']"
      />
      <TasksList
        v-if="index.data['upcoming']"
        title="Upcoming"
        :tasks="index.data['upcoming']"
      />
      <TasksList
        v-if="index.data['no overdue']"
        title="No Overdue"
        :tasks="index.data['no overdue']"
      />
      <TasksList
        v-if="index.data['completed']"
        title="Completed"
        :tasks="index.data['completed']"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { IndexNoData, Loader } from 'thetheme'
  import { axios } from 'spack'
  import { inject, ref } from 'vue'
  import { useIndexStore } from 'spack'
  import {
    BeakerIcon,
    BoltIcon,
    CalendarIcon,
    CheckCircleIcon,
    FolderIcon,
    InboxIcon,
  } from '@heroicons/vue/24/outline'
  import TasksList from './TasksList.vue'

  const __ = inject('__')
  const index = useIndexStore('tasks')(),
    projects = ref<any>([]),
    project = ref<number | null>(null),
    projectName = ref(''),
    projectColor = ref('')

  index.setConfig({ uri: 'tasks' })
  index.fetch()

  getProjects()

  function getProjects() {
    axios.get('projects/options').then((response) => {
      console.log(response)
      projects.value = response.data
    })
  }

  function choose(type: null | string = null) {
    project.value = null
    index.params.project = null
    index.params.type = type
    index.fetch()
  }

  function chooseProject(id: number, name: string, color: string) {
    project.value = id
    projectName.value = name
    projectColor.value = color
    index.params.type = null
    index.params.project = id
    index.fetch()
  }
</script>
