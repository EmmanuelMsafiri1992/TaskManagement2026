<template>
  <Dropdown name="task-labels" :modal="true" close-outside>
    <template #trigger>
      <span class="cursor-pointer">
        <svg
          class="h-4 w-4 text-gray-600 hover:text-gray-800"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          ></path>
        </svg>
      </span>
    </template>

    <template #content>
      <div
        class="absolute z-40 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 ltr:origin-top-right rtl:left-0 rtl:origin-top-left"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <p
          v-if="!task.options.labels.length"
          class="py-1.5 text-center text-xs text-gray-600"
        >
          There are No Labels!
        </p>

        <div
          v-for="option in task.options.labels"
          :key="option.label"
          class="flex items-center px-4 py-2 text-sm hover:bg-gray-200"
          :class="{ 'cursor-pointer': can('task:update') }"
          @click="choose(option)"
        >
          <!-- <span
            class="inline-block rounded-full h-5 w-5"
            :style="{'color': option.meta.color}"
            v-if="(task.data.labels.length ? task.data.labels.find(x => x.id == option.id) : null)"
          >
            <svg viewBox="0 0 16 16" fill="currentColor"><path d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/></svg>
          </span> -->

          <CheckIcon
            v-if="
              task.data.labels.length
                ? task.data.labels.find((x: any) => x.id == option.id)
                : null
            "
            class="inline-block h-4 w-4"
            :style="{ color: option.meta.color }"
          />
          <TagIcon
            v-else
            class="inline-block h-4 w-4"
            :style="{ color: option.meta.color }"
          />

          <span class="block text-gray-600 ltr:ml-2 rtl:mr-2">{{
            option.name
          }}</span>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { inject } from 'vue'
  import { Dropdown } from 'thetheme'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { TagIcon } from '@heroicons/vue/24/solid'
  import { CheckIcon } from '@heroicons/vue/24/solid'
  import { axios } from 'spack'
  // import { CheckIcon } from '@heroicons/vue/outline'

  const task = useTaskStore(),
    projectDetail = useProjectDetail(),
    can = inject('can') as (permission: string) => boolean

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }

  function choose(label: any) {
    if (!can('task:update')) return

    const statusLabels = ['not started', 'started', 'in progress', 'completed', 'stuck']
    const isStatusLabel = statusLabels.includes(label.name.toLowerCase())

    let index = task.data.labels.findIndex((x: any) => x.id == label.id)

    if (index >= 0) {
      task.data.labels.splice(index, 1)
    } else {
      // If adding a status label, remove other status labels
      if (isStatusLabel) {
        task.data.labels = task.data.labels.filter((l: any) =>
          !statusLabels.includes(l.name.toLowerCase())
        )
      }

      task.data.labels.push({
        id: label.id,
        name: label.name,
        meta: {
          color: label.meta.color,
        },
      })
    }

    console.log(task.data.labels)

    axios
      .patch('tasks/' + task.data.id + '/label', {
        label: label.id,
      })
      .then(({ data }) => {
        console.log(data)

        // Update completed_at status from server response
        if (data.completed_at !== undefined) {
          task.data.completed_at = data.completed_at
        }

        updateProjectDetail()
      })
  }
</script>
