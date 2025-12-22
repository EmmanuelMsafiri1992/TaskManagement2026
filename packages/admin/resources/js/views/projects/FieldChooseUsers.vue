<template>
  <div class="sm:col-span-12">
    <div class="mb-4 flex items-center text-sm">
      <p class="block text-sm font-medium text-gray-700">
        {{ __('Team Members') }} ({{ form.data.users?.length }})
      </p>

      <div class="flex flex-1 items-center pl-4">
        <span
          class="block cursor-pointer text-xs text-gray-700 hover:text-gray-900"
          @click="select"
        >
          {{ __('Select All') }}
        </span>
        <span class="block px-1 text-xs text-gray-700">/</span>
        <span
          class="block cursor-pointer text-xs text-gray-700 hover:text-gray-900"
          @click="deselect"
        >
          {{ __('Deselect All') }}
        </span>
      </div>
    </div>

    <div
      role="list"
      class="divide-y divide-gray-200 overflow-y-auto rounded-md border border-gray-200"
      style="max-height: 228px"
    >
      <label
        v-for="option in form.options['users']"
        :key="option.id"
        class="flex cursor-pointer items-center px-4 py-3"
      >
        <div class="flex items-start">
          <UserAvatar class="h-6 w-6" :avatar="option.avatar" />
          <div class="text-sm ltr:ml-3 rtl:mr-3">
            <span
              class="mb-1 block truncate text-sm font-medium leading-none text-gray-900"
            >
              {{ option.name }}
            </span>
            <span class="block truncate text-sm leading-none text-gray-500">
              {{ option.email }}
            </span>
          </div>
        </div>
        <div class="ltr:ml-auto rtl:mr-auto">
          <input
            v-model="form.data['users']"
            type="checkbox"
            :value="option.id"
            class="h-5 w-5 cursor-pointer rounded-full border-gray-300 focus:outline-none focus:ring-0 focus:ring-offset-0"
          />
        </div>
      </label>
    </div>

    <div
      v-if="form.errors['users']"
      class="mt-2 text-xs italic leading-normal text-red-600"
    >
      {{ form.errors['users'][0] }}
    </div>
  </div>
</template>

<script setup lang="ts">
  import { inject } from 'vue'
  import { useFormStore } from 'spack'
  import { UserAvatar } from 'thetheme'
  import type { ProjectForm } from 'types'

  defineProps<{
    name: string
  }>()

  const form = useFormStore<ProjectForm>(inject('form_name', ''))()

  function select() {
    form.data.users = form.options.users.map((x: { id: number }) => x.id)
  }

  function deselect() {
    form.data.users = []
  }
</script>
