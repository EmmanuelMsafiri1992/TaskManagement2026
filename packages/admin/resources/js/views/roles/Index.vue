<template>
  <SettingsLayout>
    <template #topbar>
      <TheButton
        v-if="can('role:create')"
        class="ml-auto"
        size="sm"
        data-cy="topbar-create-button"
        @click="openModal()"
      >
        {{ __('Create Role') }}
      </TheButton>
    </template>

    <IndexTable
      v-slot="{ items, index }"
      name="role"
      uri="roles"
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
                <thead>
                  <tr>
                    <TableTh name="role" :index="index" :label="__('Role')" />
                    <TableTh
                      name="role"
                      :index="index"
                      :label="__('Permissions')"
                      align="center"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="item in items" :key="item.id">
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      {{ item.name }}
                    </td>
                    <td
                      class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-gray-500"
                    >
                      {{ item.permissions_count }}
                    </td>

                    <td
                      class="whitespace-no-wrap flex items-center justify-end px-6 py-4 text-right text-sm font-medium leading-5"
                    >
                      <span
                        v-if="can('role:update')"
                        class="ml-2"
                        @click="openModal(item.id)"
                      >
                        <PencilSquareIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        />
                      </span>

                      <TrashIcon
                        v-if="can('role:delete')"
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
  import { IndexPagination, IndexTable, TableTh, TheButton } from 'thetheme'
  import { useModalsStore } from 'spack'
  import SettingsLayout from 'View/settings/SettingsLayout.vue'
  import Form from './Form.vue'
  import { PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'

  function openModal(id = null) {
    useModalsStore().add(Form, { id })
  }
</script>
