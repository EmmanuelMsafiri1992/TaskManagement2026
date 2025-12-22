<template>
  <Dropdown close-outside class="inline-block ltr:ml-auto rtl:mr-auto">
    <template #trigger>
      <span class="cursor-pointer text-gray-600 hover:text-gray-700">
        <EllipsisVerticalIcon class="h-4 w-4" />
      </span>
    </template>

    <template #content>
      <div
        class="absolute top-4 z-40 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 ltr:origin-bottom-right rtl:left-0 rtl:origin-bottom-left"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <div class="py-1" role="none">
          <button
            class="w-full px-4 py-1.5 text-left text-sm font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click="favorite"
          >
            {{
              isFavoriteProject ? __('Remove favorite') : __('Add to favorite')
            }}
          </button>
          <button
            v-if="can('project:update')"
            class="w-full px-4 py-1.5 text-left text-sm font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click.prevent="openModal"
          >
            {{ __('Edit') }}
          </button>
          <button
            v-if="can('project:delete')"
            class="w-full px-4 py-1.5 text-left text-sm font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click="archive"
          >
            {{ __('Archive') }}
          </button>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { Dropdown } from 'thetheme'
  import { ref } from 'vue'
  import { EllipsisVerticalIcon } from '@heroicons/vue/24/solid'
  import Form from './Form.vue'
  import { axios, useModalsStore, useToastStore } from 'spack'
  import { useFavoriteStore } from 'Store/favorite'
  // import { projectAction } from 'Use/project-actions'

  const props = defineProps<{
    projectId: number
    isFavorite: any
  }>()

  const isFavoriteProject = ref(props.isFavorite)

  function openModal() {
    useModalsStore().add(Form, { id: props.projectId })
  }

  function favorite() {
    axios
      .patch('/projects/' + props.projectId + '/favorite')
      .then(({ data }) => {
        console.log(data)
        useToastStore().flash(data.message)
        useFavoriteStore().items = data.favorites.favorites
        isFavoriteProject.value = data.favorite
      })
  }

  function archive() {
    axios.patch(`projects/${props.projectId}/archive`).then(({ data }) => {
      useToastStore().flash(data.message)
      window.location.href = '/'
    })
  }
</script>
