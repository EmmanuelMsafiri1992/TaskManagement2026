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
      <!-- Already completed - show disabled state -->
      <div v-if="task.data.completed_at && !copied" class="inline-flex items-center gap-2 rounded-md bg-gray-400 px-4 py-2 text-sm font-medium text-white cursor-not-allowed">
        <CheckCircleIcon class="h-5 w-5" />
        Already Shared
      </div>

      <!-- Active copy button -->
      <button
        v-else
        :disabled="processing"
        @click.stop="copyAndComplete"
        class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors"
        :class="{
          'bg-green-600 hover:bg-green-700 focus:ring-green-500': copied,
          'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500': !copied && !processing,
          'bg-indigo-400 cursor-wait': processing
        }"
      >
        <ClipboardDocumentCheckIcon v-if="copied" class="h-5 w-5" />
        <ClipboardDocumentIcon v-else-if="!processing" class="h-5 w-5" />
        <svg v-else class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ copied ? 'Copied & Completed!' : (processing ? 'Processing...' : 'Copy for Social Media') }}
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { marked } from 'marked'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import { axios, useModalsStore } from 'spack'
  import { Bars3BottomLeftIcon, ClipboardDocumentIcon, ClipboardDocumentCheckIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'

  const task = useTaskStore()
  const projectDetail = useProjectDetail()
  const copied = ref(false)
  const processing = ref(false)

  const renderer = new marked.Renderer()
  const linkRenderer = renderer.link
  renderer.link = (href: string, title: string, text: string) => {
    const html = linkRenderer.call(renderer, href, title, text)
    return html.replace(
      /^<a /,
      `<a onclick="event.stopPropagation()" target="_blank" rel="noreferrer noopener nofollow" `,
    )
  }

  async function copyAndComplete() {
    if (!task.data.description || processing.value) return

    processing.value = true

    try {
      // Copy content to clipboard
      await navigator.clipboard.writeText(task.data.description)
      copied.value = true

      // Mark task as complete
      const { data } = await axios.patch(`tasks/${task.data.id}/complete`)
      task.data.completed_at = data.completed_at

      // Update project detail
      if (task.data.project_id) {
        projectDetail.fetch(task.data.project_id)
      }

      // Close modal after a short delay
      setTimeout(() => {
        useModalsStore().pop()
      }, 1500)

    } catch (error) {
      console.error('Failed to copy or complete task:', error)
      copied.value = false
    } finally {
      processing.value = false
    }
  }
</script>
