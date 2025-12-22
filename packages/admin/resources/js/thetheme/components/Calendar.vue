<template>
  <div
    class="w-full min-w-[18rem] max-w-sm rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-800"
  >
    <div class="flex items-center justify-between p-4 leading-8">
      <h1 class="text-base font-medium text-gray-800 dark:text-gray-100">
        {{ __(months[month]) }} {{ year }}
      </h1>
      <div class="flex items-center text-gray-800 dark:text-gray-100">
        <span class="cursor-pointer hover:text-gray-500" @click="prev">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
            <polyline points="15 6 9 12 15 18"></polyline>
          </svg>
        </span>
        <span class="cursor-pointer hover:text-gray-500" @click="next">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="icon icon-tabler icon-tabler-chevron-right ml-3"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="currentColor"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
          >
            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
            <polyline points="9 6 15 12 9 18"></polyline>
          </svg>
        </span>
      </div>
    </div>

    <ol class="grid grid-cols-7 px-2 pb-2">
      <li
        v-for="weekDay in weekDays"
        :key="weekDay"
        class="pb-2 text-center text-sm font-medium text-gray-800"
      >
        {{ weekDay }}
      </li>
      <li
        v-if="firstWeekDayOfTheMonth"
        class="flex items-center justify-center py-1"
        :style="{ gridColumnStart: firstWeekDayOfTheMonth }"
      >
        &nbsp;
      </li>

      <li
        v-for="day in days"
        :key="day"
        class="flex items-center justify-center py-1"
      >
        <p
          class="flex h-6 w-6 cursor-pointer items-center justify-center rounded-full text-sm font-medium"
          :class="{
            'bg-indigo-700 text-white': day == selected,
            'text-gray-500 hover:bg-gray-200': day != selected,
            'bg-gray-200': day == currentDay,
          }"
          @click="
            select(day),
              $emit('selected', {
                day: ('0' + day).slice(-2),
                month: ('0' + (month + 1)).slice(-2),
                year,
                date: `${year}-${('0' + (month + 1)).slice(-2)}-${(
                  '0' + day
                ).slice(-2)}`,
                label: `${shortMonths[month]} ${day}, ${year}`,
              })
          "
        >
          {{ day }}
        </p>
      </li>
    </ol>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'

  defineEmits(['selected'])

  const props = defineProps<{
    date: string
  }>()

  const today = props.date ? new Date(props.date) : new Date(),
    // dayInt = ref(today.getDate()),
    month = ref(today.getMonth()),
    year = ref(today.getFullYear()),
    selected = ref(),
    currentDay = ref<any>(today.getDate()),
    firstWeekDayOfTheMonth = ref(getFirstWeekDayOfTheMonth()),
    days = ref<any>([]),
    weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
    months = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December',
    ],
    shortMonths = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ]

  updateDays()

  function updateDays() {
    const currentDays = []
    selected.value = null
    currentDay.value = null

    for (let day = 1; day <= daysInMonth(); day++) {
      currentDays.push(day)

      if (
        day === today.getDate() &&
        month.value === today.getMonth() &&
        year.value === today.getFullYear()
      ) {
        currentDay.value = day
      }
    }

    days.value = currentDays
    firstWeekDayOfTheMonth.value = getFirstWeekDayOfTheMonth()
  }

  function next() {
    year.value = month.value === 11 ? year.value + 1 : year.value
    month.value = (month.value + 1) % 12
    updateDays()
  }

  function prev() {
    year.value = month.value === 0 ? year.value - 1 : year.value
    month.value = month.value === 0 ? 11 : month.value - 1
    updateDays()
  }

  function daysInMonth() {
    // day 0 here returns the last day of the PREVIOUS month
    return new Date(year.value, month.value + 1, 0).getDate()
  }

  function getFirstWeekDayOfTheMonth() {
    return new Date(year.value, month.value).getDay()
  }

  function select(day: any) {
    selected.value = day
  }
</script>
