<template>
  <div>
    <div
      v-show="isSkelton"
      class="mt-1 mb-4 cursor-pointer rounded-md border border-gray-300 hover:shadow"
      @click="showForm"
    >
      <p class="px-3 py-2 text-sm text-gray-500">{{ __('Write a comment') }}</p>
    </div>

    <form
      v-show="!isSkelton"
      class="mt-1 mb-4 rounded-md border border-gray-300"
      enctype="multipart/form-data"
    >
      <textarea
        ref="input"
        v-model="commentText"
        class="autosize comment-textarea block max-h-40 w-full resize-none rounded-md border-0 text-sm focus:ring-0"
        :placeholder="__('Write a comment')"
        @keydown.ctrl.enter="create"
        @keydown.esc="cancel"
      />

      <ul v-if="files.length" class="mx-3 mt-1 mb-1 divide-y rounded-md border">
        <li
          v-for="(file, fileIndex) in files"
          :key="file.name"
          class="block py-2 px-3"
        >
          <div class="flex items-center">
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
            <span class="w-0 flex-1 truncate text-sm ltr:ml-2 rtl:mr-2">
              {{ file.name }}
            </span>
            <span
              class="cursor-pointer ltr:ml-auto rtl:mr-auto"
              @click="removeFile(fileIndex)"
            >
              <svg
                class="h-5 w-5 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                />
              </svg>
            </span>
          </div>

          <div
            v-if="progress[fileIndex]"
            class="mt-2 w-full rounded-md bg-green-100"
          >
            <span
              class="block h-1 rounded-md bg-green-400"
              :style="{ width: progress[fileIndex] + '%' }"
            ></span>
          </div>
        </li>
      </ul>

      <div class="flex items-center px-3 pt-2 pb-3">
        <div class="flex items-center">
          <button
            type="button"
            class="inline-flex items-center rounded border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-0"
            @click="create"
          >
            {{ __('Save') }}
          </button>

          <button
            type="button"
            class="inline-flex items-center rounded border border-transparent bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-0 ltr:ml-1 rtl:mr-1"
            @click="cancel"
          >
            {{ __('Cancel') }}
          </button>
        </div>

        <div class="ml-auto flex">
          <label class="cursor-pointer">
            <input
              type="file"
              class="hidden"
              accept="image/png, image/jpeg, image/gif,.doc,.docx,.pdf,.txt"
              multiple
              @change="onChoose"
            />

            <svg
              class="h-5 w-5 text-gray-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
              />
            </svg>
          </label>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
  import { onMounted, ref } from 'vue'
  import { useTaskStore } from 'Store/task'
  import { useProjectDetail } from 'Store/project-detail'
  import autosize from 'autosize'
  import { axios } from 'spack'

  const props = defineProps<{
    id?: number
    index?: number
    comment?: any
  }>()

  onMounted(() => {
    autosize(document.querySelectorAll('.autosize'))
    input.value?.focus()
  })

  const task = useTaskStore(),
    files = ref(props.comment ? props.comment.attachments : []),
    commentText = ref(props.comment ? props.comment.comment : ''),
    input = ref<HTMLInputElement | null>(null),
    isSkelton = ref(props.id ? false : true)

  const progress = ref<any>({})
  const filePaths = ref<any>({})
  const submitting = ref(false)
  const projectDetail = useProjectDetail()

  function cancel() {
    isSkelton.value = true
    commentText.value = ''
    task.commentForm = null
  }

  function showForm() {
    isSkelton.value = false

    setTimeout(function () {
      input.value?.focus()
    })
  }

  function updateProjectDetail() {
    projectDetail.fetch(task.data.project_id)
  }

  function onChoose(e: any) {
    e.preventDefault()
    files.value = [...e.target.files]

    console.log(files.value)

    for (let i = 0; i < files.value.length; i++) {
      handle(i)
    }
  }

  function handle(index: number) {
    const file = files.value[index],
      reader = new FileReader()

    reader.readAsDataURL(file)
    reader.onload = () => {
      const formData = new FormData()
      formData.append('file', file)

      axios
        .post('file', formData, {
          onUploadProgress: function (progressEvent: any) {
            console.log('progressEvent')
            console.log(progressEvent)
            console.log(
              Math.round((progressEvent.loaded * 100) / progressEvent.total),
            )
            progress.value[index] =
              Math.round((progressEvent.loaded * 100) / progressEvent.total) - 5
          },
        })
        .then(({ data }) => {
          console.log('data')
          console.log(data)
          filePaths.value[index] = { path: data, name: file.name }
          progress.value[index] = 100
        })
    }
  }

  function removeFile(index: number) {
    console.log(filePaths.value)
    console.log(index)
    delete filePaths.value[index]
    console.log(files.value)
    files.value.splice(index, 1)
  }

  function create() {
    if (!commentText.value) return

    console.log('create')
    submitting.value = true

    axios
      .post('tasks/' + task.data.id + '/comments', {
        text: commentText.value,
        task_id: task.data.id,
        attachments: filePaths.value,
      })
      .then(({ data }) => {
        console.log('data')
        console.log(data)
        task.data.comments.push(data)
        commentText.value = ''
        files.value = []
        filePaths.value = {}
        progress.value = {}
        submitting.value = false
        updateProjectDetail()
        // detail.fetch({id: task.data.id, loading: false})
      })
  }
</script>
