<template>
  <Dropdown close-outside>
    <template #trigger>
      <span class="cursor-pointer text-gray-600 hover:text-gray-700">
        <EllipsisHorizontalIcon class="h-4 w-4" />
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
            @click.prevent="$emit('listEdit')"
          >
            {{ __('Edit') }}
          </button>
          <button
            class="w-full px-4 py-1.5 text-left text-sm font-normal leading-5 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click="onDelete"
          >
            {{ __('Delete') }}
          </button>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { EllipsisHorizontalIcon } from '@heroicons/vue/24/solid'
  import { axios, useConfirmStore } from 'spack'
  import { Dropdown } from 'thetheme'
  import { useProjectDetail } from 'Store/project-detail'

  const props = defineProps<{
    listId: number
    listIndex: number
  }>()

  defineEmits(['listEdit'])

  const detail = useProjectDetail()

  function onDelete() {
    console.log('onDelete!')

    useConfirmStore().confirm({
      title: 'Delete Resource',
      description: 'Are you sure you want to delete this resource?',
      button: 'Delete',
      danger: true,
      onProceed() {
        axios
          .delete(`lists/${props.listId}`)
          .then(() => {
            useConfirmStore().hide()
            // useToastStore().flash(data.message)
            detail.data.lists.splice(props.listIndex, 1)
          })
          .catch(() => {
            // useToastStore().danger(error.response.data.message)

            useConfirmStore().hide()
          })
      },
    })

    return

    // useConfirmStore().show({
    //   title: 'Delete Resource',
    //   description: 'Are you sure you want to delete this resource?',
    //   button: 'Delete',
    //   danger: true,
    //   onProceed() {
    //     axios
    //       .delete(`lists/${props.listId}`)
    //       .then(({data}) => {
    //         useConfirmStore().hide()
    //         useToastStore().flash(data.message)
    //         detail.data.lists.splice(props.listIndex, 1)
    //       })
    //       .catch((error) => {
    //         useToastStore().danger(error.response.data.message)

    //         useConfirmStore().hide()
    //       })
    //   },
    // })
  }
</script>
