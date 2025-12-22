<template>
  <div class="group relative flex py-1">
    <div class="h-6 w-6">
      <UserAvatar class="h-6 w-6" :avatar="comment.user.avatar" />
    </div>
    <div class="group flex-1 ltr:pl-4 rtl:pr-4">
      <div class="flex">
        <h2 class="flex text-sm font-medium leading-none text-gray-700">
          {{ comment.user.name }}
          <span class="text-xs font-normal text-gray-500 ltr:ml-3 rtl:mr-3">{{
            comment.updated_at
          }}</span>
        </h2>
      </div>

      <!-- eslint-disable vue/no-v-html -->
      <p
        class="prose prose-sm mt-1.5 break-all text-sm text-gray-600"
        v-html="marked.parse(comment.text || '', { breaks: true, renderer })"
      ></p>
      <!--eslint-enable-->

      <ul
        v-if="comment.attachments.length"
        class="mt-4 mb-1 divide-y rounded-md border"
      >
        <li
          v-for="attachment in comment.attachments"
          :key="attachment.name"
          class="flex items-center py-2 px-3"
        >
          <div class="flex flex-1 items-center">
            <svg
              class="h-4 w-4 text-gray-400"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                clip-rule="evenodd"
              />
            </svg>
            <a
              :href="'/' + attachment.path"
              class="block w-0 flex-1 truncate text-xs text-gray-700 hover:underline ltr:ml-2 ltr:pr-2 rtl:mr-2 rtl:pl-2"
              target="blank"
            >
              {{ attachment.name }}
            </a>
          </div>
          <a
            :href="'/' + attachment.path"
            class="ltr:ml-auto rtl:mr-auto"
            target="blank"
          >
            <ArrowDownTrayIcon class="h-5 w-5 text-gray-400" />
          </a>
        </li>
      </ul>
    </div>

    <div
      v-if="deleteConfirmation"
      class="absolute right-0 flex items-center bg-white pl-4"
      :class="{ 'opacity-50': processing }"
    >
      <span class="mr-2 text-sm text-gray-600">{{
        __('Are you sure to delete?')
      }}</span>
      <CheckCircleIcon
        class="mr-1 h-5 w-5 cursor-pointer text-green-600 hover:text-green-800"
        @click="deleteComment"
      />
      <XCircleIcon
        class="h-5 w-5 cursor-pointer text-red-600 hover:text-red-800"
        @click="deleteConfirmation = false"
      />
    </div>

    <div v-else class="absolute right-0 hidden bg-white pl-4 group-hover:flex">
      <!-- <span @click="task.commentForm = index" class="mr-1">
        <PencilAltIcon class="w-4 h-4 text-gray-500 cursor-pointer hover:text-gray-900"/>
      </span> -->
      <TrashIcon
        v-if="comment.user_id == appData.user.id"
        class="h-4 w-4 cursor-pointer text-gray-500 hover:text-gray-900"
        @click="deleteConfirmation = true"
      />
    </div>

    <!-- <div class="w-4" v-if="config.user.id == comment.author_id">
      <span @click="onDelete(comment.id, index)">
        <TrashIcon class="w-4 h-4 text-gray-500 hover:text-gray-800 cursor-pointer"/>
      </span>
    </div> -->
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { marked } from 'marked'
  import { useTaskStore } from 'Store/task'
  import { axios } from 'spack'
  import { UserAvatar } from 'thetheme'
  import { appData } from '@/app-data'
  import {
    ArrowDownTrayIcon,
    CheckCircleIcon,
    TrashIcon,
    XCircleIcon,
  } from '@heroicons/vue/24/outline'

  const props = defineProps<{
    id: number
    index: number
    comment: any
  }>()

  const renderer = new marked.Renderer()
  const linkRenderer = renderer.link
  renderer.link = (href, title, text) => {
    const html = linkRenderer.call(renderer, href, title, text)
    return html.replace(
      /^<a /,
      `<a target="_blank" rel="noreferrer noopener nofollow" `,
    )
  }

  const task = useTaskStore(),
    deleteConfirmation = ref(false),
    processing = ref(false)

  function deleteComment() {
    if (processing.value) return

    processing.value = true

    axios.delete(`comments/${props.id}`).then(({ data }) => {
      processing.value = false
      if (!data.success) return
      task.data?.comments.splice(props.index, 1)
    })
  }
</script>
