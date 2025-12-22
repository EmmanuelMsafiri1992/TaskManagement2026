<template>
  <section class="py-3">
    <h2 class="px-2 text-sm font-medium text-gray-600">{{ __('Due Date') }}</h2>

    <div
      v-if="!can('task:update')"
      class="mt-2 flex items-center px-2"
      data-cooltipz-dir="top"
    >
      <CalendarIcon class="h-4 w-4 text-gray-500" />

      <span
        class="block text-xs leading-none text-gray-600 ltr:ml-1.5 rtl:mr-1.5"
      >
        {{ task.data.due_at ? task.data.due_at : __('No due date') }}
      </span>
    </div>

    <Dropdown
      v-else
      close-outside
      :close="closeDropdown"
      :modal="true"
    >
      <template #trigger>
        <div
          class="group mt-2 flex cursor-pointer items-center rounded-md px-2 py-1.5 hover:bg-gray-200"
        >
          <CalendarIcon class="h-4 w-4 text-gray-500" />

          <span
            class="block text-xs leading-none text-gray-600 ltr:ml-1.5 rtl:mr-1.5"
          >
            {{
              task.data.due_at ? task.data.human_due_date : __('No due date')
            }}
          </span>

          <span
            v-if="task.data.due_at"
            class="ml-auto flex h-4 w-4 items-center justify-center rounded-md hover:bg-gray-300"
            @click.stop="onDateSelected"
          >
            <XMarkIcon class="h-3.5 w-3.5 text-gray-600" />
          </span>
        </div>
      </template>

      <template #content>
        <div
          class="absolute z-30 mt-2 ltr:left-0 ltr:origin-top-left rtl:right-0 rtl:origin-top-right"
          role="menu"
          aria-orientation="vertical"
          aria-labelledby="user-menu"
        >
          <Calendar
            :date="task.data?.due_at || ''"
            @selected="onDateSelected"
          />
        </div>
      </template>
    </Dropdown>
  </section>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { Calendar, Dropdown } from 'thetheme'
  import { CalendarIcon, XMarkIcon } from '@heroicons/vue/24/outline'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios } from 'spack'

  const closeDropdown = ref(false)
  const task = useTaskStore()
  const projectDetail = useProjectDetail()

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }

  // function choose(option) {
  //   task.data.priority = {
  //     name: option.name,
  //     color: option.color,
  //   }

  //   axios
  //     .patch('tasks/' + task.data.id + '/priority', {
  //       priority: option.id,
  //     })
  //     .then(({data}) => {
  //       console.log(data)
  //     })
  // }

  // function removeDate() {
  //   console.log('removeDate')
  // }

  const onDateSelected = function (payload: any) {
    console.log('onDateSelected')
    console.log(payload)
    closeDropdown.value = true
    task.data.due_at = payload ? payload.date : null
    task.data.human_due_date = payload ? payload.label : null

    axios
      .patch('tasks/' + task.data.id + '/due-date', {
        due_at: payload ? payload.date : null,
      })
      .then(({ data }) => {
        console.log(data)
        updateProjectDetail()
      })
  }
</script>
