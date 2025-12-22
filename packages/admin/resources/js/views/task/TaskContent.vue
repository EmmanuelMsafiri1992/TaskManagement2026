<template>
  <div @click="can('task:update') ? task.isContentForm = true : false">
    <h2 class="text-base font-semibold text-gray-800">
      {{ task.data.title }}
    </h2>

    <article
      v-if="task.data.description"
      class="prose prose-sm mt-2 break-all text-sm text-gray-700"
      v-html="
        marked.parse(task.data.description || '', { breaks: true, renderer })
      "
    ></article>

    <p v-else class="mt-4 flex items-center text-sm font-light text-gray-500">
      <Bars3BottomLeftIcon class="mr-2 h-4 w-4" />

      {{ __('Description') }}
    </p>
  </div>
</template>

<script setup lang="ts">
  import { marked } from 'marked'
  import { useTaskStore } from 'Store/task'
  import { Bars3BottomLeftIcon } from '@heroicons/vue/24/outline'

  const task = useTaskStore()

  const renderer = new marked.Renderer()
  const linkRenderer = renderer.link
  renderer.link = (href: string, title: string, text: string) => {
    const html = linkRenderer.call(renderer, href, title, text)
    return html.replace(
      /^<a /,
      `<a onclick="event.stopPropagation()" target="_blank" rel="noreferrer noopener nofollow" `,
    )
  }
</script>
