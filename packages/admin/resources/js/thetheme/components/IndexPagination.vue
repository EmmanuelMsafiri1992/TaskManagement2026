<template>
  <nav
    class="flex items-center justify-between bg-white py-3"
    :class="[classes]"
    aria-label="Pagination"
  >
    <div class="hidden sm:block">
      <p class="text-sm text-gray-700">
        {{ __('Showing') }}
        <span class="font-medium text-gray-600">{{ index.data.from }}</span>
        {{ __('to') }}
        <span class="font-medium text-gray-600">{{ index.data.to }}</span>
      </p>
    </div>
    <div
      v-if="index.data.next_page_url || index.data.prev_page_url"
      class="flex flex-1 justify-between sm:justify-end"
    >
      <span
        class="relative inline-flex cursor-pointer items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
        :class="{ 'pointer-events-none opacity-50': !index.data.prev_page_url }"
        @click="index.prev()"
      >
        {{ __('Previous') }}
      </span>
      <span
        class="relative inline-flex cursor-pointer items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 ltr:ml-3 rtl:mr-3"
        :class="{ 'pointer-events-none opacity-50': !index.data.next_page_url }"
        @click="index.next()"
      >
        {{ __('Next') }}
      </span>
    </div>
  </nav>
</template>

<script setup lang="ts">
  withDefaults(
    defineProps<{
      index: any
      classes?: string
    }>(),
    {
      classes: 'px-4 sm:px-6 border-t border-gray-200',
    },
  )
</script>
