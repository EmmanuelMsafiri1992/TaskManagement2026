<template>
  <Dropdown name="header-create">
    <template #trigger>
      <button
        class="flex items-center text-sm font-medium text-gray-500 transition duration-150 ease-in-out hover:border-gray-300 hover:text-gray-700 focus:border-gray-300 focus:text-gray-700 focus:outline-none"
      >
        <PlusIcon class="h-4 w-4" />
        <span class="ltr:ml-2 rtl:mr-2">{{ __('Create') }}</span>
      </button>
    </template>

    <template #content>
      <div
        class="absolute mt-2 w-48 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:left-0 rtl:right-0"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <div class="py-1" role="none">
          <a
            v-show="can('project:create')"
            href="#"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click.prevent="openProjectModal"
            >{{ __('Create Project') }}</a
          >
          <a
            v-show="can('user:create')"
            href="#"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            @click.prevent="openInvitationModal()"
            >{{ __('Invite Team Member') }}</a
          >
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { Dropdown } from 'thetheme'
  import { PlusIcon } from '@heroicons/vue/24/outline'
  import ProjectForm from 'View/projects/Form.vue'
  import InvitationForm from 'View/users/InvitationForm.vue'
  import { useModalsStore } from 'spack'

  function openProjectModal() {
    useModalsStore().add(ProjectForm)
  }

  function openInvitationModal() {
    useModalsStore().add(InvitationForm)
  }
</script>
