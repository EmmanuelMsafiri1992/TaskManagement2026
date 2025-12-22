<template>
  <div class="flex items-center py-2.5 px-2">
    <div v-if="show" class="flex-1 ltr:pr-2 rtl:pl-2">
      <input
        id="list-edit-input"
        ref="input"
        v-model="listName"
        type="text"
        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        @keyup.enter="submit"
      />
    </div>

    <div
      v-if="!show"
      class="px-2 py-[0.313rem] text-sm font-semibold leading-none text-gray-700"
      v-text="text"
    ></div>

    <div
      v-if="can('task-list:update') || can('task-list:delete')"
      class="ltr:ml-auto rtl:mr-auto"
    >
      <SectionDropdown
        :list-id="id"
        :list-index="index"
        @list-edit="onListEdit"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import SectionDropdown from './SectionDropdown.vue'
  import { axios } from 'spack'

  const props = defineProps<{
    id: number
    index: number
    name: string
  }>()

  const show = ref(false)
  const listName = ref('')
  const input = ref<HTMLInputElement | null>(null)
  const text = ref(props.name)

  function closeIfClickedOutside(event: Event) {
    console.log('closeIfClickedOutside')
    console.log((event.target as HTMLInputElement).closest('#list-edit-input'))
    if (!(event.target as HTMLInputElement).closest('#list-edit-input')) {
      show.value = false
      document.removeEventListener('click', closeIfClickedOutside)
    }
  }

  function onListEdit() {
    show.value = true
    listName.value = text.value
    setTimeout(function () {
      input.value?.focus()
      document.addEventListener('click', closeIfClickedOutside)
    })
  }

  function submit() {
    if (!listName.value) return

    axios
      .patch(`lists/${props.id}`, {
        name: listName.value,
      })
      .then(() => {
        text.value = listName.value
        show.value = false
        listName.value = ''
        document.removeEventListener('click', closeIfClickedOutside)
      })
  }
</script>
