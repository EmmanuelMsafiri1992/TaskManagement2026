<template>
  <Dropdown :name="'sidebar-project-menu-' + id" @toggle="onToggle">
    <template #trigger>
      <span class="text-gray-400 hover:text-gray-200">
        <EllipsisHorizontalIcon class="h-4 w-4" />
      </span>
    </template>

    <template #content>
      <div
        class="absolute bottom-6 z-40 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 ltr:origin-bottom-right rtl:left-0 rtl:origin-bottom-left"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <div class="py-1" role="none">
          <button
            class="w-full px-4 py-1.5 text-left text-xs font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click.prevent="favorite"
          >
            {{
              isFavoriteProject ? __('Remove favorite') : __('Add to favorite')
            }}
          </button>
          <button
            v-if="can('project:update')"
            class="w-full px-4 py-1.5 text-left text-xs font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click.prevent="edit"
          >
            {{ __('Edit') }}
          </button>
          <button
            v-if="can('project:delete')"
            class="w-full px-4 py-1.5 text-left text-xs font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click.prevent="archive"
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
  import { EllipsisHorizontalIcon } from '@heroicons/vue/24/outline'
  // import { useProjectFavoriteStore } from 'Store/project-favorite'
  // import { useProjectIndex } from 'Store/project'
  // import { useProjectArchiveStore } from 'Store/project-archive'
  import { ref } from 'vue'
  // import { projectAction } from 'Use/project-actions'
  import { useFavoriteStore } from 'Store/favorite'
  import { axios, useModalsStore } from 'spack'
  import Form from 'View/projects/Form.vue'

  const props = defineProps<{
    id: number
    index: number
    isFavorite: any
  }>()

  const emit = defineEmits(['toggleMenu'])

  const isFavoriteProject = ref(props.isFavorite)
  // const project = useProjectIndex()
  // const archive = useProjectArchiveStore()

  function onToggle(state: any) {
    emit('toggleMenu', {
      state,
      index: props.index,
    })
  }

  function favorite() {
    axios.patch('/projects/' + props.id + '/favorite').then(({ data }) => {
      console.log(data)
      // useToastStore().flash(data.message)
      useFavoriteStore().items = data.favorites.favorites
      isFavoriteProject.value = data.favorite
    })
  }

  function archive() {
    axios.patch(`projects/${props.id}/archive`).then(() => {
      // useToastStore().flash(data.message)
      window.location.href = '/'
    })
  }

  function edit() {
    useModalsStore().add(Form, { id: props.id })
  }
</script>
