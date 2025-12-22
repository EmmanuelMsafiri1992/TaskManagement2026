<template>
  <div class="mb-4 flex">
    <h1 class="text-2xl font-semibold text-gray-900">{{ __('Projects') }}</h1>

    <div class="flex items-center ltr:ml-auto rtl:mr-auto">
      <nav class="flex space-x-2 rounded-md" aria-label="Tabs">
        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': !isArchived,
            'text-gray-600': isArchived,
          }"
          @click="choose()"
        >
          <FolderIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
          >
            {{ __('Active') }}
          </span>
        </span>

        <span
          class="group flex cursor-pointer items-center rounded-md px-3 py-2"
          :class="{
            'bg-gray-200 text-gray-800': isArchived,
            'text-gray-600': !isArchived,
          }"
          @click="choose('archived')"
        >
          <ArchiveBoxIcon class="h-4 w-4 group-hover:text-gray-800" />
          <span
            class="text-sm font-medium group-hover:text-gray-800 ltr:ml-2 rtl:mr-2"
          >
            {{ __('Archived') }}
          </span>
        </span>
      </nav>

      <TheButton
        v-if="can('project:create')"
        class="ml-6"
        size="sm"
        data-cy="topbar-create-button"
        @click.stop="project.create"
      >
        {{ __('Create Project') }}
      </TheButton>
    </div>
  </div>

  <div v-if="index.fetching" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <IndexNoData v-if="!index.data.length" no-create />

    <Card v-else>
      <div class="space-y-4 divide-y pb-6">
        <div
          v-for="(item, itemIndex) in index.data"
          :key="item.id"
          class="flex px-6 pt-6"
        >
          <RouterLink
            v-if="!isArchived"
            v-slot="{ href, navigate }"
            custom
            :to="'/projects/' + item.id"
          >
            <a
              :href="href"
              class="flex cursor-pointer items-center leading-5 text-gray-700 transition duration-150 ease-in-out hover:underline"
              @click="navigate"
            >
              <span
                class="block h-2 w-2 flex-shrink-0 rounded-full"
                :style="{ 'background-color': item.meta?.color || '#ccc' }"
                aria-hidden="true"
              ></span>
              <span class="truncate text-gray-500 ltr:ml-2 rtl:mr-2">
                {{ item.name }}
              </span>
            </a>
          </RouterLink>

          <p
            v-if="isArchived"
            class="flex items-center leading-5 text-gray-700"
          >
            <span
              class="block h-2 w-2 flex-shrink-0 rounded-full"
              :style="{ 'background-color': item.meta?.color || '#ccc' }"
              aria-hidden="true"
            ></span>
            <span class="truncate text-gray-500 ltr:ml-2 rtl:mr-2">
              {{ item.name }}
            </span>
          </p>

          <div class="ml-auto flex space-x-3">
            <ArchiveBoxIcon
              v-show="!isArchived && can('project:delete')"
              class="h-4 w-4 cursor-pointer text-gray-600 hover:text-gray-900"
              @click="archive(item.id, itemIndex)"
            />
            <ArchiveBoxXMarkIcon
              v-show="isArchived && can('project:delete')"
              class="h-4 w-4 cursor-pointer text-gray-600 hover:text-gray-900"
              @click="restore(item.id, itemIndex)"
            />
            <TrashIcon
              v-show="isArchived && can('project:delete')"
              class="h-4 w-4 cursor-pointer text-gray-600 hover:text-gray-900"
              @click="onDelete(item.id, itemIndex)"
            />
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>

<script setup lang="ts">
  import { Card, TheButton } from 'thetheme'
  import {
    ArchiveBoxIcon,
    ArchiveBoxXMarkIcon,
    FolderIcon,
    TrashIcon,
  } from '@heroicons/vue/24/outline'
  import { axios, useConfirmStore, useIndexStore } from 'spack'
  import { IndexNoData, Loader } from 'thetheme'
  import { inject, ref } from 'vue'
  import { useProjectIndex } from 'Store/project'

  console.log('[ProjectsIndex] Component loading')

  const can = inject('can')
  const __ = inject('__')

  console.log('[ProjectsIndex] Injected functions:', { can: typeof can, __: typeof __ })

  const params = new URLSearchParams(window.location.search)
  const isArchived = ref(params.has('archived') ? true : false),
    index = useIndexStore('projects')(),
    project = useProjectIndex()

  console.log('[ProjectsIndex] Stores initialized:', { index, project })

  if (params.has('archived')) {
    index.setConfig({ uri: 'projects?archived' })
  } else {
    index.setConfig({ uri: 'projects' })
  }

  console.log('[ProjectsIndex] Fetching projects...')
  index.fetch()

  console.log('[ProjectsIndex] Component setup complete')

  function choose(type: null | string = null) {
    type == 'archived'
      ? index.setConfig({ uri: 'projects?archived' })
      : index.setConfig({ uri: 'projects' })

    if (type == 'archived') {
      isArchived.value = true
      history.replaceState(history.state, '', '?archived')
    } else {
      isArchived.value = false
      history.replaceState(history.state, '', '/projects')
    }

    index.fetch()
  }

  function archive(id: number, itemIndex: number) {
    axios.patch(`projects/${id}/archive`).then(() => {
      // useToastStore().flash(data.message)
      // window.location.href = '/'
      index.data.splice(itemIndex, 1)
    })
  }

  function restore(id: number, itemIndex: number) {
    axios.patch(`projects/${id}/restore`).then(() => {
      // project.items.push(data)
      index.data.splice(itemIndex, 1)
    })
  }

  function onDelete(ids: number | number[], itemIndex: number) {
    const confirm = useConfirmStore()

    confirm.confirm({
      title: 'Delete Resource',
      description: 'Are you sure you want to delete this resource?',
      button: 'Delete',
      danger: true,
      onProceed() {
        const items: number[] = Array.isArray(ids) ? ids : [ids]

        const proceedUri = 'projects'

        axios
          .delete(`${proceedUri}/null`, {
            data: { items },
          })
          .then(() => {
            confirm.hide()
            index.data.splice(itemIndex, 1)
            // fetch()
            // useFlashStore().flash(data.message)
          })
          .catch(() => {
            // useFlashStore().danger(error.response.data.message)

            confirm.hide()
          })
      },
    })
  }

  // function onDelete() {
  //   axios.delete(`projects/${props.id}`).then(() => {
  //     archive.items.splice(props.index, 1)
  //   })
  // }
</script>
