<template>
  <Dropdown name="task-assign" :modal="true" close-outside>
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
        <div
          v-for="user in task.data.project.users"
          :key="user.id"
          class="flex items-center px-4 py-2 text-sm hover:bg-gray-200"
          :class="{ 'cursor-pointer': can('task:update') }"
          @click="choose(user)"
        >
          <span
            v-if="
              task.data.users.length
                ? task.data.users.find((x: any) => x.id == user.id)
                : null
            "
            class="inline-block h-5 w-5 rounded-full bg-blue-400 text-white"
          >
            <svg viewBox="0 0 16 16" fill="white">
              <path
                d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"
              />
            </svg>
          </span>

          <UserAvatar v-else class="h-5 w-5" :avatar="user.avatar" />

          <span class="block text-gray-600 ltr:ml-2 rtl:mr-2">{{
            user.name
          }}</span>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { inject } from 'vue'
  import { Dropdown, UserAvatar } from 'thetheme'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios } from 'spack'

  const task = useTaskStore(),
    projectDetail = useProjectDetail(),
    can = inject('can') as (permission: string) => boolean

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }

  function choose(user: any) {
    if (!can('task:update')) return
    let index = task.data.users.findIndex((x: any) => x.id == user.id)

    if (index >= 0) {
      task.data.users.splice(index, 1)
    } else {
      task.data.users.push({
        id: user.id,
        name: user.name,
      })
    }

    console.log(task.data.users)

    axios
      .patch('tasks/' + task.data.id + '/assign', {
        user: user.id,
      })
      .then(({ data }) => {
        console.log(data)
        updateProjectDetail()
      })
  }
</script>
