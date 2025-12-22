<template>
  <div class="pb-8">
    <div class="mt-2 mb-6 flex items-center" :class="{ 'opacity-50': loading }">
      <span class="text-gray-700">{{ __('Total logged time') }}:</span>
      <span class="font-semibold text-gray-700 ltr:ml-2 rtl:mr-2">
        {{ totalTime }}
      </span>
      <a
        :href="`/api/projects/${detail.data.id}/export-time-logs`"
        class="inline-flex items-center rounded border border-transparent bg-indigo-600 px-2.5 py-1.5 text-xs font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 ltr:ml-auto rtl:mr-auto"
      >
        {{ __('Export CSV') }}
      </a>
    </div>

    <IndexTable
      v-slot="{ items, index }"
      name="timelog"
      :uri="`projects/${detail.data.id}/time-logs`"
      no-search
      no-filters
    >
      <div class="rounded-lg border-b border-gray-200 shadow">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th
                class="px-6 py-3 text-xs font-medium uppercase leading-4 tracking-wider text-gray-500 ltr:text-left rtl:text-right"
              >
                {{ __('Task') }}
              </th>
              <th
                class="px-6 py-3 text-xs font-medium uppercase leading-4 tracking-wider text-gray-500 ltr:text-left rtl:text-right"
              >
                {{ __('Assignee') }}
              </th>
              <th
                class="px-6 py-3 text-xs font-medium uppercase leading-4 tracking-wider text-gray-500 ltr:text-left rtl:text-right"
              >
                {{ __('Start') }}
              </th>
              <th
                class="px-6 py-3 text-xs font-medium uppercase leading-4 tracking-wider text-gray-500 ltr:text-left rtl:text-right"
              >
                {{ __('Stop') }}
              </th>
              <th
                class="px-6 py-3 text-xs font-medium uppercase leading-4 tracking-wider text-gray-500 ltr:text-left rtl:text-right"
              >
                {{ __('Time') }}
              </th>
              <th
                class="px-6 py-3 text-xs font-medium uppercase leading-4 tracking-wider text-gray-500 ltr:text-left rtl:text-right"
              >
                {{ __('Description') }}
              </th>
              <th class="px-6 py-3">&nbsp;</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="item in items" :key="item.id">
              <td
                class="whitespace-nowrap px-6 py-4 align-top text-sm font-medium text-gray-500"
              >
                <p
                  class="cursor-pointer hover:text-gray-700"
                  @click="openModal(item.task_id)"
                >
                  {{ item.task.title }}
                </p>
              </td>
              <td
                class="whitespace-nowrap px-6 py-4 align-top text-sm font-medium text-gray-500"
              >
                <div class="flex items-center">
                  <UserAvatar class="h-6 w-6" :avatar="item.user.avatar" />
                  <span class="ltr:ml-2 rtl:mr-2">{{ item.user.name }}</span>
                </div>
              </td>

              <td
                class="whitespace-nowrap px-6 py-4 align-top text-sm font-medium text-gray-500"
              >
                {{ item.started_at }}
              </td>
              <td
                class="whitespace-nowrap px-6 py-4 align-top text-sm font-medium text-gray-500"
              >
                {{ item.stopped_at }}
              </td>
              <td
                class="whitespace-nowrap px-6 py-4 align-top text-sm font-medium text-gray-500"
              >
                <p>{{ item.time }}</p>
                {{ item.is_manual ? '(manual)' : '' }}
              </td>
              <td
                class="whitespace-nowrap px-6 py-4 align-top text-sm font-medium text-gray-500"
              >
                {{ item.description || '—' }}
              </td>

              <td class="px-6 py-3 text-right">&nbsp;</td>
            </tr>
          </tbody>
        </table>

        <IndexPagination class="rounded-b-lg" :index="index" />
      </div>
    </IndexTable>
  </div>
</template>

<script setup lang="ts">
  import { IndexPagination, IndexTable, UserAvatar } from 'thetheme'
  import { ref } from 'vue'
  import { axios, useModalsStore } from 'spack'
  import TaskModal from '../task/TaskModal.vue'
  import { useProjectDetail } from 'Store/project-detail'
  import type { ConcreteComponent } from 'vue'

  const detail = useProjectDetail()
  const loading = ref(true)
  const totalTime = ref('00:00:00')

  // const indexLoaded = computed(() => {
  //   return index.loading ? false : true
  // })

  getTotalTime()

  function openModal(id = null) {
    useModalsStore().add(TaskModal as ConcreteComponent, {
      id,
      width: 'max-w-3xl',
    })
  }

  function getTotalTime() {
    axios.get('projects/' + detail.data.id + '/total-time').then(({ data }) => {
      console.log(data)

      const decimalTimeString = data
      const n = new Date(0, 0)
      n.setSeconds(+decimalTimeString)
      totalTime.value = n.toTimeString().slice(0, 8)
      loading.value = false
    })
  }
</script>
