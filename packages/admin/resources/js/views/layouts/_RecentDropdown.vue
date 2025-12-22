<template>
  <Dropdown>
    <template #trigger>
      <button
        class="flex items-center text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700 focus:outline-none"
      >
        <span>{{ __('Recent') }}</span>

        <div class="ltr:ml-1 rtl:mr-1">
          <svg
            class="h-4 w-4 fill-current"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
          >
            <path
              fill-rule="evenodd"
              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
      </button>
    </template>

    <template #content>
      <div
        class="absolute mt-2 w-48 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:left-0 rtl:right-0"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <p v-show="!projects.length" class="px-4 py-4 text-sm text-gray-700">
          {{ __('There are no data') }}
        </p>

        <div v-show="projects.length" class="py-1" role="none">
          <template v-for="project in projects" :key="project.name">
            <router-link
              class="flex items-center px-4 py-2 text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
              :to="'/projects/' + project.project_id"
            >
              <span
                class="block h-2 w-2 rounded-full"
                :style="{ 'background-color': project.project.meta.color }"
                >&nbsp;</span
              >
              <span class="flex-1 font-medium ltr:ml-2 rtl:mr-2">{{
                project.project.name
              }}</span>
            </router-link>
          </template>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { Dropdown } from 'thetheme'
  import { axios } from 'spack'

  const projects = ref<any>([])

  axios.get('recent-projects').then((response) => {
    projects.value = response.data
  })
</script>
