<template>
  <section id="comments" class="mt-8">
    <Collapsible :open="true">
      <template #trigger="{ open }">
        <div class="flex">
          <svg
            class="mr-4 mt-1 h-4 w-4 cursor-pointer text-gray-500"
            :class="{ 'rotate-[270deg]': !open }"
            viewBox="0 0 16 16"
          >
            <path
              d="M14 5.758L13.156 5 7.992 9.506l-.55-.48.002.002-4.588-4.003L2 5.77 7.992 11 14 5.758"
              fill="currentColor"
            ></path>
          </svg>

          <div class="flex-1 border-b pb-2">
            <span class="text-sm font-medium text-gray-800">{{
              __('Comments')
            }}</span>
            <span class="ml-2 text-sm font-light text-gray-600">{{
              task.data.comments.length
            }}</span>
          </div>
        </div>
      </template>

      <template #content>
        <div class="pl-8 pt-4">
          <CommentForm />

          <div class="space-y-4">
            <template
              v-for="(comment, index) in task.data.comments"
              :key="comment.id"
            >
              <Component
                :is="task.commentForm == index ? CommentForm : CommentItem"
                :id="comment.id"
                :index="index"
                :comment="comment"
              />
            </template>
          </div>
        </div>
      </template>
    </Collapsible>
  </section>
</template>

<script setup lang="ts">
  import { useTaskStore } from 'Store/task'
  import { Collapsible } from 'thetheme'
  import CommentItem from './CommentItem.vue'
  import CommentForm from './CommentForm.vue'

  const task = useTaskStore()
</script>
