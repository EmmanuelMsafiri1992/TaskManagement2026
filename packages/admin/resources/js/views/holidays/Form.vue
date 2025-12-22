<template>
  <FormBase :form="form" @submit="submit">
    <template #title>
      {{ form.id ? __('Edit Holiday') : __('Add Holiday') }}
    </template>

    <div class="space-y-6">
      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Holiday Name') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.name"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.name }"
            :placeholder="__('e.g., New Year\'s Day')"
            required
          />
          <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
            {{ form.errors.name }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Date') }}
          <span class="text-red-500">*</span>
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <input
            v-model="form.date"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.date }"
            required
          />
          <p v-if="form.errors.date" class="mt-2 text-sm text-red-600">
            {{ form.errors.date }}
          </p>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Description') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <textarea
            v-model="form.description"
            rows="2"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :placeholder="__('Optional description')"
          ></textarea>
        </div>
      </div>

      <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4">
        <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
          {{ __('Recurring') }}
        </label>
        <div class="mt-1 sm:col-span-2 sm:mt-0">
          <div class="flex items-center">
            <input
              v-model="form.is_recurring"
              type="checkbox"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label class="ml-2 block text-sm text-gray-900">
              {{ __('This holiday recurs annually') }}
            </label>
          </div>
          <p class="mt-1 text-sm text-gray-500">
            {{ __('Check this if the holiday repeats every year on the same date') }}
          </p>
        </div>
      </div>
    </div>
  </FormBase>
</template>

<script setup>
import { watch } from 'vue'
import FormBase from '@/thetheme/components/FormBase.vue'
import { useForm } from '@/composables/useForm'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])

const form = useForm('/api/holidays', {
  name: '',
  date: '',
  description: '',
  is_recurring: false
})

const submit = () => {
  form.submit(() => {
    emit('close')
  })
}

watch(() => props.modelValue, (holiday) => {
  if (holiday) {
    form.setData({
      ...holiday,
      date: holiday.date.split('T')[0]
    })
  } else {
    form.reset()
  }
}, { immediate: true })
</script>
