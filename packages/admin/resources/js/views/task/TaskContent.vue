<template>
  <div>
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

    <!-- Copy Content Button for Social Media Sharing -->
    <div v-if="task.data.description" class="mt-4">
      <button
        @click.stop="copyContent"
        class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors"
        :class="{ 'bg-green-600 hover:bg-green-700': copied }"
      >
        <ClipboardDocumentIcon v-if="!copied" class="h-5 w-5" />
        <ClipboardDocumentCheckIcon v-else class="h-5 w-5" />
        {{ copied ? 'Copied!' : 'Copy for Social Media' }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { marked } from 'marked'
  import { useTaskStore } from 'Store/task'
  import { Bars3BottomLeftIcon, ClipboardDocumentIcon, ClipboardDocumentCheckIcon } from '@heroicons/vue/24/outline'

  const task = useTaskStore()
  const copied = ref(false)

  const renderer = new marked.Renderer()
  const linkRenderer = renderer.link
  renderer.link = (href: string, title: string, text: string) => {
    const html = linkRenderer.call(renderer, href, title, text)
    return html.replace(
      /^<a /,
      `<a onclick="event.stopPropagation()" target="_blank" rel="noreferrer noopener nofollow" `,
    )
  }

  function copyContent() {
    if (task.data.description) {
      navigator.clipboard.writeText(task.data.description).then(() => {
        copied.value = true
        setTimeout(() => {
          copied.value = false
        }, 2000)
      })
    }
  }
</script>
