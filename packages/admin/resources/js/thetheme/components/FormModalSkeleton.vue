<template>
  <div class="animate-pulse px-6">
    <div
      v-for="i in 2"
      :key="i"
      class="grid border-b border-gray-200 py-5 last:border-b-0 sm:grid-cols-3"
    >
      <label class="mr-16 h-4 rounded bg-gray-200 sm:mt-px sm:pt-2" />
      <div class="h-4 rounded bg-gray-200 sm:col-span-2">&nbsp;</div>
    </div>
  </div>

  <div
    class="sticky bottom-0 flex justify-end rounded-b-lg bg-gray-50 py-5 px-6"
  >
    <span class="h-4 w-32 rounded bg-gray-300">&nbsp;</span>
  </div>
</template>
