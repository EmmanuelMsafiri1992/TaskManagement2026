<template>
  <Dropdown
    name="task-repeat"
    :modal="true"
    close-outside
    :close="closeDropdown"
    @toggle="onOpen"
  >
    <template #trigger>
      <span class="cursor-pointer">
        <svg
          class="h-4 w-4 text-gray-600 hover:text-gray-800"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          ></path>
        </svg>
      </span>
    </template>

    <template #content>
      <div
        class="absolute bottom-0 z-40 mb-6 w-96 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 ltr:origin-bottom-right rtl:left-0 rtl:origin-bottom-left"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <div class="px-6 py-4">
          <h3 class="mb-6 text-sm font-medium text-gray-700">
            {{ __('Recurrence') }}
          </h3>

          <section class="flex items-center">
            <p class="mr-6 whitespace-nowrap text-sm text-gray-600">
              {{ __('Repeat every') }}
            </p>

            <input
              v-model="form.every"
              type="number"
              class="mr-2 block w-16 rounded-md border-gray-300 px-2.5 py-1.5 text-xs text-gray-800 shadow-sm focus:border-gray-300 focus:ring-0"
              min="1"
              max="99"
              @change="
                form.every = Math.max(Math.min(Math.round(form.every), 99), 1)
              "
            />

            <select
              v-model="form.type"
              class="block rounded-md border-gray-300 py-1.5 pl-2.5 text-xs text-gray-800 focus:border-gray-300 focus:outline-none focus:ring-0"
            >
              <option value="daily">Day{{ form.every > 1 ? 's' : '' }}</option>
              <option value="weekly">
                Week{{ form.every > 1 ? 's' : '' }}
              </option>
              <option value="monthly">
                Month{{ form.every > 1 ? 's' : '' }}
              </option>
              <option value="yearly">
                Year{{ form.every > 1 ? 's' : '' }}
              </option>
            </select>
          </section>

          <section v-if="form.type == 'daily'" class="mt-6 flex items-center">
            <p class="mr-6 whitespace-nowrap text-sm text-gray-600">
              {{ __('Hour At') }}
            </p>

            <select
              v-model="form.hour_at"
              class="block rounded-md border-gray-300 py-1.5 pl-2.5 text-xs text-gray-800 focus:border-gray-300 focus:outline-none focus:ring-0"
            >
              <option v-for="(i, index) in 24" :key="i" :value="index">
                {{ index.toString().padStart(2, '0') }}:00
              </option>
            </select>
          </section>

          <section v-if="form.type == 'weekly'" class="mt-6">
            <p class="whitespace-nowrap text-sm text-gray-600">
              {{ __('Repeat on') }}
            </p>

            <div class="mt-2.5 flex space-x-2">
              <span
                v-for="weekDay in weekDays"
                :key="weekDay.value"
                class="flex h-6 w-6 cursor-pointer items-center justify-center rounded text-xs"
                :class="{
                  'border border-gray-300 text-gray-500':
                    !form.repeat_on.includes(weekDay.value),
                  'bg-indigo-600 text-white': (form.repeat_on as any).includes(
                    weekDay.value,
                  ),
                }"
                @click="chooseWeekDay(weekDay.value)"
              >
                {{ weekDay.label }}
              </span>
            </div>
          </section>

          <section v-if="form.type == 'monthly'" class="mt-6 flex items-center">
            <p class="mr-6 whitespace-nowrap text-sm text-gray-600">
              {{ __('Day') }}
            </p>

            <select
              v-model="form.day_at"
              class="block rounded-md border-gray-300 py-1.5 pl-2.5 text-xs text-gray-800 focus:border-gray-300 focus:outline-none focus:ring-0"
            >
              <option v-for="i in 31" :key="i" :value="i">
                {{ i.toString().padStart(2, '0') }}
              </option>
              <option value="lastday">{{ __('Last Day') }}</option>
            </select>
          </section>

          <section v-if="form.type == 'yearly'" class="mt-6 flex items-center">
            <p class="mr-6 whitespace-nowrap text-sm text-gray-600">
              {{ __('Month') }} / {{ __('Day') }}
            </p>

            <select
              v-model="form.month_at"
              class="block rounded-md border-gray-300 py-1.5 pl-2.5 text-xs text-gray-800 focus:border-gray-300 focus:outline-none focus:ring-0"
            >
              <option
                v-for="month in months"
                :key="month.label"
                :value="month.value"
              >
                {{ month.label }}
              </option>
            </select>

            <select
              v-model="form.day_at"
              class="ml-2 block rounded-md border-gray-300 py-1.5 pl-2.5 text-xs text-gray-800 focus:border-gray-300 focus:outline-none focus:ring-0"
            >
              <option v-for="i in 31" :key="i" :value="i">
                {{ i.toString().padStart(2, '0') }}
              </option>
              <option value="lastday">{{ __('Last Day') }}</option>
            </select>
          </section>

          <section class="mt-6">
            <p class="mb-3 whitespace-nowrap text-sm text-gray-600">
              {{ __('Ends') }}
            </p>

            <div class="space-y-1.5">
              <div class="flex h-[1.875rem] items-center">
                <input
                  id="ends-never"
                  v-model="form.ends"
                  type="radio"
                  value="never"
                  class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                />
                <label for="ends-never" class="ml-3">
                  <span class="block text-sm text-gray-600">{{
                    __('Never')
                  }}</span>
                </label>
              </div>
              <div class="flex items-center">
                <div class="flex w-[9.375rem] items-center">
                  <input
                    id="ends-on"
                    v-model="form.ends"
                    type="radio"
                    value="on"
                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <label for="ends-on" class="ml-3">
                    <span class="block text-sm text-gray-600">{{
                      __('On')
                    }}</span>
                  </label>
                </div>

                <div>
                  <FlatPickr
                    id="ends-on-date"
                    v-model="form.ends_at"
                    :disabled="form.ends != 'on'"
                    class="block rounded-md border-gray-300 px-2 py-1.5 text-xs text-gray-800 shadow-sm focus:border-gray-300 focus:ring-0 disabled:opacity-50"
                  />
                </div>
              </div>
              <div class="flex items-center">
                <div class="flex w-[9.375rem] items-center">
                  <input
                    id="ends-after"
                    v-model="form.ends"
                    type="radio"
                    value="after"
                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  />
                  <label for="ends-after" class="ml-3">
                    <span class="block text-sm text-gray-600">{{
                      __('After')
                    }}</span>
                  </label>
                </div>

                <div>
                  <div
                    class="flex rounded-md border border-gray-300 shadow-sm"
                    :class="{ 'opacity-50': form.ends != 'after' }"
                  >
                    <input
                      v-model="form.repetitions"
                      type="number"
                      placeholder="1"
                      class="block w-20 rounded-md border-0 px-2.5 py-1.5 text-xs text-gray-800 focus:ring-0"
                      :disabled="form.ends != 'after'"
                      @change="
                        form.repetitions = Math.max(
                          Math.min(Math.round(form.repetitions), 999),
                          1,
                        )
                      "
                    />

                    <div class="pointer-events-none flex items-center pr-2.5">
                      <span class="text-xs text-gray-600">
                        {{ __('Occurences') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <section class="mt-8">
            <!-- <p class="whitespace-nowrap text-sm text-gray-600 mb-4">Completion</p> -->

            <div class="flex items-center">
              <input
                id="task-completion"
                v-model="form.task_completion_required"
                type="checkbox"
                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
              />
              <label for="task-completion" class="pl-3 text-sm text-gray-600">{{
                __('Only if last task was completed')
              }}</label>
            </div>
          </section>

          <section class="mt-8 flex justify-start">
            <TheButton size="xs" :processing="processing" @click="save">
              {{ __('Save') }}
            </TheButton>

            <button
              type="button"
              class="inline-flex items-center rounded border border-transparent bg-white px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-0 ltr:ml-1 rtl:mr-1"
              @click="cancel"
            >
              {{ __('Cancel') }}
            </button>
          </section>

          <!-- <section class="flex justify-end mt-6">
            <button
              type="button"
              class="ltr:mr-1 rtl:ml-1 inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-gray-700 hover:text-gray-900 bg-white focus:outline-none focus:ring-0"
              @click="cancel"
            >
              {{ __('Cancel') }}
            </button>

            <TheButton
              @click="save"
              size="xs"
              :processing="processing"
            >
              {{ __('Save') }}
            </TheButton>
          </section> -->
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  // @ts-nocheck
  import { reactive, ref } from 'vue'
  import { Dropdown, TheButton } from 'thetheme'
  import { useTaskStore } from 'Store/task'
  // import { useDetailStore } from 'Store/detail'
  import FlatPickr from '@/flatpickr'
  import { axios } from 'spack'

  const task = useTaskStore(),
    // projectDetail = useDetailStore('project')(),
    processing = ref(false),
    closeDropdown = ref(false),
    defaultForm = {
      type: 'daily',
      every: 1,
      hour_at: 0,
      day_at: 1,
      month_at: 1,
      repeat_on: [],
      task_completion_required: true,
      ends_at: null,
      repetitions: 1,
      ends: 'never',
    },
    months = [
      { label: 'Jan', value: 1 },
      { label: 'Feb', value: 2 },
      { label: 'Mar', value: 3 },
      { label: 'Apr', value: 4 },
      { label: 'May', value: 5 },
      { label: 'Jun', value: 6 },
      { label: 'Jul', value: 7 },
      { label: 'Aug', value: 8 },
      { label: 'Sep', value: 9 },
      { label: 'Oct', value: 10 },
      { label: 'Nov', value: 11 },
      { label: 'Dec', value: 12 },
    ],
    weekDays = [
      { label: 'M', value: 'Mon' },
      { label: 'T', value: 'Tue' },
      { label: 'W', value: 'Wed' },
      { label: 'T', value: 'Thu' },
      { label: 'F', value: 'Fri' },
      { label: 'S', value: 'Sat' },
      { label: 'S', value: 'Sun' },
    ],
    form = reactive({ ...defaultForm })

  // function updateProjectDetail() {
  //   projectDetail.fetch({
  //     loading: false,
  //     id: task.data.project_id,
  //   })
  // }

  function chooseWeekDay(weekDay: any) {
    if ((form.repeat_on as any).includes(weekDay)) {
      form.repeat_on.splice((form.repeat_on as any).indexOf(weekDay), 1)
    } else {
      const repeat = form.repeat_on as any
      repeat.push(weekDay)

      // console.log('weekDay:', weekDay)
      // console.log('index:', index)

      // form.repeat_on.splice(index, 0, weekDay)
    }

    console.log(form.repeat_on)
  }

  function save() {
    console.log('save!')
    processing.value = true

    axios
      .post(`tasks/${task.data.id}/recurring`, {
        ...form,
      })
      .then(({ data }) => {
        console.log('data')
        console.log(data)
        processing.value = false
        closeDropdown.value = true
        Object.assign(task.data, data)
      })
      .catch(() => {
        processing.value = false
      })
  }

  function cancel() {
    console.log('cancel!')
    // console.log( closeDropdown.value )
    closeDropdown.value = true
    // console.log( closeDropdown.value )
  }

  function onOpen() {
    console.log('hit onOpen!')
    console.log(task.data.meta?.recurring)
    console.log(form)
    console.log(defaultForm)

    closeDropdown.value = false

    task.data.meta?.recurring ? updateForm() : resetForm()
  }

  function resetForm() {
    console.log('resetForm!')

    Object.assign(form, defaultForm)
    console.log(form)
  }

  function updateForm() {
    console.log('updateForm!')

    Object.assign(form, task.data.meta.recurring)
  }
</script>
