<template>
  <SettingsLayout>
    <template #topbar>
      <TheButton size="sm" @click="openModal">
        {{ __('Create Label') }}
      </TheButton>
    </template>

    <IndexTable
      v-slot="{ items, index }"
      name="label"
      uri="labels"
      no-filters
      no-search
    >
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div
            class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
          >
            <div
              class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg"
            >
              <table class="min-w-full divide-y divide-gray-200">
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="item in items" :key="item.id">
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      <div class="flex items-center">
                        <TagIcon
                          class="mr-2 inline-block h-4 w-4"
                          :style="{ color: item.meta.color }"
                        />
                        {{ item.name }}
                      </div>
                    </td>

                    <td
                      class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5"
                    >
                      <span
                        v-if="can('label:update')"
                        class="ml-2"
                        @click="openModal(item.id)"
                      >
                        <PencilSquareIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        />
                      </span>

                      <TrashIcon
                        v-if="can('label:delete')"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.prevent="index.deleteIt(item.id)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexPagination :index="index" />
            </div>
          </div>
        </div>
      </div>
    </IndexTable>
  </SettingsLayout>
</template>

<script setup lang="ts">
  import SettingsLayout from 'View/settings/SettingsLayout.vue'
  import { IndexPagination, IndexTable, TheButton } from 'thetheme'
  import { useModalsStore } from 'spack'
  import LabelForm from './Form.vue'
  import { TagIcon } from '@heroicons/vue/24/solid'
  import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

  function openModal(id: number | null = null) {
    console.log(id)
    useModalsStore().add(LabelForm, { id })
  }
</script>
