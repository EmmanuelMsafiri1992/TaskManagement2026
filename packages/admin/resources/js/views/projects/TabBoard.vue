<template>
  <div id="kanban-wrapper" class="relative flex-1">
    <div
      id="container"
      class="absolute inset-0 select-none overflow-x-auto overflow-y-hidden whitespace-nowrap text-[0] transition-opacity"
    >
      <section
        v-for="(list, index) in detail.data.lists"
        id="section-draggable"
        :key="list.id"
        :data-id="list.id"
        class="box-border inline-block h-full w-72 whitespace-nowrap rounded align-top text-base ltr:mr-4 rtl:ml-4"
      >
        <div
          class="box-border flex max-h-full flex-col whitespace-normal rounded bg-gray-200"
        >
          <SectionHeader :id="list.id" :name="list.name" :index="index" />
          <div
            :id="'list-' + index"
            :data-id="list.id"
            class="min-h-0 overflow-y-auto px-2"
          >
            <ListItem v-for="task in list.tasks" :key="task.id" :task="task" />
          </div>
          <TaskFormQuick
            :project-id="list.project_id"
            :list-id="list.id"
            :list-index="index"
          />
        </div>
      </section>

      <section
        v-if="can('task-list:create')"
        class="box-border inline-block h-full w-72 whitespace-nowrap align-top text-base"
      >
        <div
          v-show="!showListForm"
          class="flex cursor-pointer items-center rounded border-2 border-dashed border-gray-300 px-2 py-1.5 hover:border-gray-400"
          @click="handleShowListForm"
        >
          <svg
            class="h-6 w-6 text-gray-600"
            viewBox="0 0 21 21"
            fill="currentColor"
          >
            <g
              fill="none"
              fill-rule="evenodd"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="m5.5 10.5h10"></path>
              <path d="m10.5 5.5v10"></path>
            </g>
          </svg>
          <span class="text-sm text-gray-700 ltr:ml-2 rtl:mr-2">
            {{ __('Add list') }}
          </span>
        </div>

        <div
          v-show="showListForm"
          class="box-border max-h-full whitespace-normal rounded bg-gray-200"
        >
          <div class="py-2.5 px-2">
            <input
              ref="listinput"
              v-model="listName"
              type="text"
              placeholder="Enter list title..."
              class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
              @keyup.enter="addList"
            />
            <div class="mt-1 flex">
              <TheButton
                size="xs"
                :processing="listFormProcessing"
                @click="addList"
              >
                {{ __('Add list') }}
              </TheButton>
              <TheButton
                class="ltr:ml-1 rtl:mr-1"
                white
                size="xs"
                :disabled="listFormProcessing"
                @click="hideListForm"
              >
                {{ __('Cancel') }}
              </TheButton>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup lang="ts">
  import Sortable from 'sortablejs'
  import { inject, onMounted, ref, watch } from 'vue'
  import { useProjectDetail } from 'Store/project-detail'
  import SectionHeader from './SectionHeader.vue'
  import ListItem from './ListItem.vue'
  import TaskFormQuick from './TaskFormQuick.vue'
  import { axios } from 'spack'
  import { TheButton } from 'thetheme'

  const detail = useProjectDetail()
  const showListForm = ref(false)
  const listName = ref('')
  const listinput = ref<HTMLInputElement | null>(null)
  const listFormProcessing = ref(false)

  console.log('[TabBoard] Component mounted')
  console.log('[TabBoard] detail.data.lists:', detail.data.lists)
  console.log('[TabBoard] Number of lists:', detail.data.lists?.length || 0)

  watch(() => detail.data.lists, (newLists) => {
    console.log('[TabBoard] Lists data changed:', newLists)
    console.log('[TabBoard] New list count:', newLists?.length || 0)
  }, { immediate: true, deep: true })

  // function onListEdit(payload) {
  //   console.log('onListEdit!')
  //   console.log(payload)
  //   listEdit.value = payload.id
  //   listEditName.value = payload.name
  //   // console.log( listeditinput.value )
  //   setTimeout(function () {
  //     //   listeditinput.value.focus()
  //     console.log(listeditinput.value)
  //   }, 1000)
  // }

  function handleShowListForm() {
    console.log('handleShowListForm')
    showListForm.value = true
    setTimeout(function () {
      listinput.value?.focus()
    })
    // listinput.value.focus()
    // console.log( listinput.value )
    // const listinput = ref()
    // console.log('listinput')
    // console.log(listinput)
    // console.log(listinput.value)
    // listinput.focus()
    // console.log($refs.listinput)
    // showListForm = true; console.log($refs.listinput);
  }

  function listSortableInit() {
    const i = detail.data.lists.length - 1
    console.log(i)

    setTimeout(function () {
      Sortable.create(document.getElementById(`list-${i}`) as HTMLElement, {
        group: 'shared',
        delay: 300, // time in milliseconds to define when the sorting should start
        delayOnTouchOnly: true, // only delay if user is using touch
        touchStartThreshold: 100, // px, how many pixels the point should move before cancelling a delayed drag event
        onUpdate: function (evt) {
          console.log('onUpdate!!')
          // al.updateTaskListSort(evt.item.getAttribute('data-id'), evt.item.parentElement.getAttribute('data-id'), evt.oldIndex, evt.newIndex)

          // console.log(evt.item.parentElement.getAttribute('data-id'))
          console.log(evt.item.getAttribute('data-id'))

          axios
            .patch(`tasks/${evt.item.getAttribute('data-id')}/sort`, {
              id: evt.item.getAttribute('data-id'),
              projectId: detail.data.id,
              oldIndex: evt.oldIndex,
              newIndex: evt.newIndex,
            })
            .then(({ data }) => {
              console.log('data')
              console.log(data)
            })
        },
        onAdd: function (evt) {
          console.log('onAdd!!')
          // al.updateTaskListSortOtherList(evt.item.getAttribute('data-id'), evt.from.getAttribute('data-id'), evt.to.getAttribute('data-id'), evt.oldIndex, evt.newIndex)

          const taskId = evt.item.getAttribute('data-id')

          axios
            .patch(`tasks/${taskId}/move`, {
              projectId: detail.data.id,
              fromList: evt.from.getAttribute('data-id'),
              toList: evt.to.getAttribute('data-id'),
              oldIndex: evt.oldIndex,
              newIndex: evt.newIndex,
            })
            .then(({ data }) => {
              console.log('data')
              console.log(data)
            })
        },
      })
    })
  }

  function hideListForm() {
    showListForm.value = false
    listName.value = ''
  }

  function addList() {
    if (!listName.value) return

    listFormProcessing.value = true

    axios
      .post('projects/' + detail.data.id + '/lists', {
        name: listName.value,
      })
      .then((response) => {
        console.log(response.data)
        showListForm.value = false
        listName.value = ''
        detail.data.lists.push(response.data)
        listFormProcessing.value = false
        listSortableInit()
      })
  }

  // let draggablesArray
  // let draggingItem
  // let oldIndex

  const can = inject('can') as (permission: string) => boolean

  onMounted(function () {
    const kanbanWrapper = document.querySelector(
      '#kanban-wrapper',
    ) as HTMLElement
    kanbanWrapper.style.height =
      window.innerHeight - (64 + 24 + 48 + 16 + 24) + 'px'

    if (can('task-list:update')) {
      Sortable.create(document.getElementById('container') as HTMLElement, {
        draggable: '#section-draggable',
        onUpdate: function (evt) {
          console.log('onUpdate!')
          console.log(evt)
          console.log(evt.item.getAttribute('data-id'))
          console.log(evt.oldIndex)
          console.log(evt.newIndex)
          axios
            .patch(`projects/${detail.data.id}/list-sort`, {
              id: evt.item.getAttribute('data-id'),
              oldIndex: evt.oldIndex,
              newIndex: evt.newIndex,
            })
            .then(({ data }) => {
              console.log('data')
              console.log(data)
              console.log(detail.data.lists)
              detail.data.lists = data
            })
        },
      })

      for (let i = 0; i < detail.data.lists.length; i++) {
        // const list = detail.data.lists[i]

        Sortable.create(document.getElementById(`list-${i}`) as HTMLElement, {
          group: 'shared',
          delay: 300, // time in milliseconds to define when the sorting should start
          delayOnTouchOnly: true, // only delay if user is using touch
          touchStartThreshold: 100, // px, how many pixels the point should move before cancelling a delayed drag event
          onUpdate: function (evt) {
            console.log('onUpdate!!')
            // al.updateTaskListSort(evt.item.getAttribute('data-id'), evt.item.parentElement.getAttribute('data-id'), evt.oldIndex, evt.newIndex)

            // console.log(evt.item.parentElement.getAttribute('data-id'))
            console.log(evt.item.getAttribute('data-id'))

            axios
              .patch(`tasks/${evt.item.getAttribute('data-id')}/sort`, {
                id: evt.item.getAttribute('data-id'),
                projectId: detail.data.id,
                oldIndex: evt.oldIndex,
                newIndex: evt.newIndex,
              })
              .then(({ data }) => {
                console.log('data')
                console.log(data)
              })
          },
          onAdd: function (evt) {
            console.log('onAdd!!')
            // al.updateTaskListSortOtherList(evt.item.getAttribute('data-id'), evt.from.getAttribute('data-id'), evt.to.getAttribute('data-id'), evt.oldIndex, evt.newIndex)

            const taskId = evt.item.getAttribute('data-id')

            axios
              .patch(`tasks/${taskId}/move`, {
                projectId: detail.data.id,
                fromList: evt.from.getAttribute('data-id'),
                toList: evt.to.getAttribute('data-id'),
                oldIndex: evt.oldIndex,
                newIndex: evt.newIndex,
              })
              .then(({ data }) => {
                console.log('data')
                console.log(data)
              })
          },
        })
      }
    }
  })
</script>
