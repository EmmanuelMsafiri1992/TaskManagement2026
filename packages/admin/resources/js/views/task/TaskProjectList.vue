<template>
  <section class="py-3">
    <h2 class="px-2 text-sm font-medium text-gray-600">
      {{ __('Project List') }}
    </h2>

    <div v-if="!can('task:update')" class="mt-2 flex items-center px-2 py-1">
      <span class="block text-xs leading-none text-gray-600">
        {{ task.data.project_list.name }}
      </span>
    </div>

    <Dropdown v-else>
      <template #trigger>
        <div
          class="group mt-2 flex cursor-pointer items-center rounded-md px-2 py-1.5 hover:bg-gray-200"
        >
          <span class="block h-3.5 text-xs leading-none text-gray-600">
            {{ task.data.project_list.name }}
          </span>

          <svg
            class="ml-auto hidden h-3.5 w-3.5 cursor-pointer text-gray-500 group-hover:block"
            viewBox="0 0 16 16"
          >
            <path
              d="M14 5.758L13.156 5 7.992 9.506l-.55-.48.002.002-4.588-4.003L2 5.77 7.992 11 14 5.758"
              fill="currentColor"
            ></path>
          </svg>
        </div>
      </template>

      <template #content>
        <div
          class="absolute z-30 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:left-0 ltr:origin-top-left rtl:right-0 rtl:origin-top-right"
          role="menu"
          aria-orientation="vertical"
          aria-labelledby="user-menu"
        >
          <span
            v-for="option in task.options.project_lists"
            :key="option.name"
            class="block cursor-pointer px-4 py-2 text-xs text-gray-600 hover:bg-gray-200"
            @click="choose(option)"
          >
            {{ option.name }}
          </span>
        </div>
      </template>
    </Dropdown>
  </section>
</template>

<script setup lang="ts">
  import { axios } from 'spack'
  import { Dropdown } from 'thetheme'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'

  const task = useTaskStore()
  const projectDetail = useProjectDetail()

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }

  function choose(option: any) {
    task.data.project_list.name = option.name

    axios
      .patch(`tasks/${task.data.id}/list`, {
        project_list_id: option.id,
      })
      .then(() => {
        updateProjectDetail()
      })
  }
</script>
