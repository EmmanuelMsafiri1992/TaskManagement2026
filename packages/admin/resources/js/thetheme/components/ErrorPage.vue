<template>
  <div
    class="min-h-screen bg-white px-4 py-16 sm:px-6 sm:py-24 md:grid md:place-items-center lg:px-8"
  >
    <div class="mx-auto max-w-max">
      <main class="sm:flex">
        <p
          class="text-4xl font-extrabold text-indigo-600 sm:text-5xl"
          v-text="code"
        ></p>
        <div class="sm:ml-6">
          <div class="sm:border-l sm:border-gray-200 sm:pl-6">
            <h1
              class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl"
              v-text="heading"
            ></h1>
            <p class="mt-1 text-base text-gray-500" v-text="description"></p>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useBlankPageRuntime } from 'Use/blank-page-runtime'

  defineProps<{
    code: string
    heading: string
    description?: string
  }>()

  useBlankPageRuntime()
</script>
