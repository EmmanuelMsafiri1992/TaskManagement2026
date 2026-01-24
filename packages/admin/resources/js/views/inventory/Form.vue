<template>
  <div class="p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-6">
      {{ modelValue ? __('Edit Item') : __('Add Item') }}
    </h2>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-2 gap-4">
        <!-- Item Name -->
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Item Name') }} *</label>
          <input
            v-model="formData.item_name"
            type="text"
            required
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Item Code -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Item Code') }}</label>
          <input
            v-model="formData.item_code"
            type="text"
            placeholder="Auto-generated if empty"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Category -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
          <input
            v-model="formData.category"
            type="text"
            list="categories"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <datalist id="categories">
            <option value="Electronics" />
            <option value="Furniture" />
            <option value="Office Supplies" />
            <option value="Vehicles" />
            <option value="Equipment" />
            <option value="Tools" />
          </datalist>
        </div>

        <!-- Quantity -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Quantity') }} *</label>
          <input
            v-model.number="formData.quantity"
            type="number"
            min="1"
            required
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Condition -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Condition') }} *</label>
          <select
            v-model="formData.condition"
            required
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="excellent">Excellent</option>
            <option value="good">Good</option>
            <option value="fair">Fair</option>
            <option value="poor">Poor</option>
            <option value="damaged">Damaged</option>
          </select>
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }} *</label>
          <select
            v-model="formData.status"
            required
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="available">Available</option>
            <option value="in_use">In Use</option>
            <option value="maintenance">Maintenance</option>
            <option value="retired">Retired</option>
          </select>
        </div>

        <!-- Location -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Location') }}</label>
          <input
            v-model="formData.location"
            type="text"
            list="locations"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
          <datalist id="locations">
            <option value="Office" />
            <option value="Warehouse" />
            <option value="Storage" />
            <option value="Workshop" />
          </datalist>
        </div>

        <!-- Assigned To -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Assigned To') }}</label>
          <select
            v-model="formData.assigned_to"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option :value="null">{{ __('Not Assigned') }}</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }}
            </option>
          </select>
        </div>

        <!-- Purchase Price -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Purchase Price') }}</label>
          <input
            v-model.number="formData.purchase_price"
            type="number"
            min="0"
            step="0.01"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Currency -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Currency') }}</label>
          <select
            v-model="formData.currency"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          >
            <option value="MWK">MWK</option>
            <option value="USD">USD</option>
            <option value="EUR">EUR</option>
            <option value="GBP">GBP</option>
            <option value="ZAR">ZAR</option>
          </select>
        </div>

        <!-- Purchase Date -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Purchase Date') }}</label>
          <input
            v-model="formData.purchase_date"
            type="date"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Supplier -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Supplier') }}</label>
          <input
            v-model="formData.supplier"
            type="text"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <!-- Description -->
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
          <textarea
            v-model="formData.description"
            rows="2"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>

        <!-- Notes -->
        <div class="col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Notes') }}</label>
          <textarea
            v-model="formData.notes"
            rows="2"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex justify-end gap-3">
        <button
          type="button"
          class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
          @click="$emit('close')"
        >
          {{ __('Cancel') }}
        </button>
        <button
          type="submit"
          :disabled="saving"
          class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
        >
          {{ saving ? __('Saving...') : __('Save') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useFlashStore } from 'spack'
import { axios } from 'spack/axios'

const flash = useFlashStore()

const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const saving = ref(false)
const users = ref([])

const formData = reactive({
  item_name: '',
  item_code: '',
  category: '',
  description: '',
  quantity: 1,
  condition: 'good',
  purchase_date: '',
  purchase_price: null,
  currency: 'MWK',
  supplier: '',
  location: '',
  assigned_to: null,
  notes: '',
  status: 'available',
})

const loadUsers = async () => {
  try {
    const response = await axios.get('inventory-users')
    users.value = response.data.data
  } catch (error) {
    console.error('Failed to load users:', error)
  }
}

const submit = async () => {
  saving.value = true

  try {
    let response
    if (props.modelValue?.id) {
      response = await axios.put(`inventory/${props.modelValue.id}`, formData)
    } else {
      response = await axios.post('inventory', formData)
    }
    flash.success(response.data?.message || (props.modelValue?.id ? 'Item updated successfully!' : 'Item created successfully!'))
    emit('saved')
  } catch (error) {
    console.error('Failed to save:', error)
    flash.error(error.response?.data?.message || 'Failed to save item')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await loadUsers()

  if (props.modelValue) {
    Object.keys(formData).forEach((key) => {
      if (props.modelValue[key] !== undefined) {
        formData[key] = props.modelValue[key]
      }
    })
  }
})
</script>
