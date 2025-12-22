<template>
  <Dropdown name="filter" class="ltr:ml-4 rtl:mr-4" close-outside>
    <template #trigger>
      <button
        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-3.5 py-2 text-sm font-medium shadow-sm hover:bg-gray-50 focus:outline-none"
        :class="{
          'bg-gray-700 hover:bg-gray-700': index.appliedFilters.length,
        }"
      >
        <FunnelIcon
          class="w-5"
          :class="index.appliedFilters.length ? 'text-white' : 'text-gray-400'"
        />
        <span
          v-if="index.appliedFilters.length"
          class="ml-2 font-bold text-white"
          >{{ index.appliedFilters.length }}</span
        >
      </button>
    </template>

    <template #content>
      <div
        class="absolute z-30 mt-2 max-h-80 w-72 origin-top-left overflow-y-auto overflow-x-hidden rounded-md border border-gray-200 shadow-lg ltr:left-0 rtl:right-0"
      >
        <div
          class="shadow-xs rounded-md bg-white"
          role="menu"
          aria-orientation="vertical"
          aria-labelledby="filters"
        >
          <div
            v-if="index.appliedFilters.length"
            class="cursor-pointer border-b border-gray-200 bg-gray-50 py-2 text-center text-sm font-medium uppercase text-gray-600 hover:text-gray-400"
            @click="index.resetFilters"
          >
            {{ __('Reset Filters') }}
          </div>
          <Component
            :is="filter?.component"
            v-for="filter in index.filters"
            :key="filter?.name"
            :filter="filter"
            :name="name"
          />
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { Dropdown } from 'thetheme'
  import { useIndexStore } from 'spack'
  import { FunnelIcon } from '@heroicons/vue/24/outline'

  const props = defineProps<{
    name: string
  }>()

  const index = useIndexStore(props.name)()
</script>
