<template>
  <form @submit.prevent="submit">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ modelValue ? __('Edit Advance Request') : __('New Advance Request') }}
        </h3>
        <button type="button" class="text-gray-400 hover:text-gray-600" @click="emit('close')">
          <XCircleIcon class="h-6 w-6" />
        </button>
      </div>

      <div class="space-y-4">
        <!-- Amount -->
        <div>
          <label for="amount" class="block text-sm font-medium text-gray-700">
            {{ __('Amount') }} <span class="text-red-500">*</span>
          </label>
          <div class="mt-1 relative rounded-md shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 sm:text-sm">MWK</span>
            </div>
            <input
              id="amount"
              v-model.number="form.amount"
              type="number"
              step="0.01"
              min="1"
              required
              class="block w-full pl-14 pr-4 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              :class="{ 'border-red-300': errors.amount }"
            />
          </div>
          <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount[0] }}</p>
        </div>

        <!-- Reason -->
        <div>
          <label for="reason" class="block text-sm font-medium text-gray-700">
            {{ __('Reason') }} <span class="text-red-500">*</span>
          </label>
          <textarea
            id="reason"
            v-model="form.reason"
            rows="4"
            required
            :placeholder="__('Please explain why you need this advance...')"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            :class="{ 'border-red-300': errors.reason }"
          ></textarea>
          <p v-if="errors.reason" class="mt-1 text-sm text-red-600">{{ errors.reason[0] }}</p>
        </div>

        <!-- Expected Deduction Date -->
        <div>
          <label for="expected_deduction_date" class="block text-sm font-medium text-gray-700">
            {{ __('Expected Deduction Date') }}
          </label>
          <input
            id="expected_deduction_date"
            v-model="form.expected_deduction_date"
            type="date"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <p class="mt-1 text-xs text-gray-500">{{ __('When do you expect this to be deducted from your salary?') }}</p>
        </div>
      </div>
    </div>

    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
      <TheButton type="button" white @click="emit('close')">
        {{ __('Cancel') }}
      </TheButton>
      <TheButton type="submit" :disabled="processing">
        {{ processing ? __('Saving...') : (modelValue ? __('Update') : __('Submit Request')) }}
      </TheButton>
    </div>
  </form>
</template>

<script setup>
import { inject, reactive, ref, watch } from 'vue'
import { XCircleIcon } from '@heroicons/vue/24/outline'
import { axios } from 'spack/axios'
import TheButton from '@/thetheme/components/TheButton.vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close', 'saved'])
const __ = inject('__')

const processing = ref(false)
const errors = ref({})

const form = reactive({
  amount: props.modelValue?.amount || '',
  reason: props.modelValue?.reason || '',
  expected_deduction_date: props.modelValue?.expected_deduction_date || ''
})

watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    form.amount = newValue.amount || ''
    form.reason = newValue.reason || ''
    form.expected_deduction_date = newValue.expected_deduction_date || ''
  }
}, { immediate: true })

const submit = async () => {
  processing.value = true
  errors.value = {}

  try {
    if (props.modelValue) {
      await axios.put(`advance-requests/${props.modelValue.id}`, form)
    } else {
      await axios.post('advance-requests', form)
    }

    emit('saved')
    emit('close')
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      alert(error.response?.data?.message || 'Failed to save advance request')
    }
  } finally {
    processing.value = false
  }
}
</script>
