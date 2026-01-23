<template>
  <FormModal :id="id" :name="name" uri="roles" size="4xl">
    <FieldText name="name" label="Name" class="col-span-12" />

    <div class="col-span-12">
      <div class="mb-4 flex items-center justify-between sticky top-0 bg-white py-2 z-10">
        <p class="block text-sm font-medium text-gray-700">
          {{ __('Permissions') }}
          <span class="text-xs text-gray-500 ml-2">
            ({{ selectedCount }}/{{ totalCount }} {{ __('selected') }})
          </span>
        </p>
        <div class="flex items-center gap-2">
          <span
            class="cursor-pointer text-xs text-indigo-600 hover:text-indigo-800"
            @click="select"
          >
            {{ __('Select All') }}
          </span>
          <span class="text-xs text-gray-400">|</span>
          <span
            class="cursor-pointer text-xs text-indigo-600 hover:text-indigo-800"
            @click="deselect"
          >
            {{ __('Deselect All') }}
          </span>
        </div>
      </div>

      <!-- Grouped Permissions -->
      <div v-if="groupedPermissions && Object.keys(groupedPermissions).length > 0" class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
        <div
          v-for="(permissions, group) in groupedPermissions"
          :key="group"
          class="rounded-lg border border-gray-200 bg-gray-50 p-3"
        >
          <div class="mb-2 flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-900 capitalize">{{ formatGroupName(group) }}</h4>
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-500">
                {{ getSelectedCount(permissions) }}/{{ permissions.length }}
              </span>
              <button
                type="button"
                class="text-xs text-indigo-600 hover:text-indigo-800"
                @click="selectGroup(permissions)"
              >
                {{ __('All') }}
              </button>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <label
              v-for="perm in permissions"
              :key="perm.value"
              class="flex cursor-pointer items-center rounded-md bg-white px-3 py-1.5 border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-colors text-sm"
              :class="{ 'border-indigo-500 bg-indigo-50': isSelected(perm.value) }"
            >
              <input
                type="checkbox"
                :value="perm.value"
                :checked="isSelected(perm.value)"
                class="h-3.5 w-3.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                @change="togglePermission(perm.value)"
              />
              <span class="ml-1.5 text-gray-700">{{ formatPermissionLabel(perm.label) }}</span>
            </label>
          </div>
        </div>
      </div>

      <!-- Fallback to simple checkbox list -->
      <div v-else-if="form.options.permissions && form.options.permissions.length > 0" class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        <label
          v-for="perm in form.options.permissions"
          :key="perm.value"
          class="flex cursor-pointer items-center rounded-md bg-gray-50 p-2 border border-gray-200 hover:border-indigo-300"
          :class="{ 'border-indigo-500 bg-indigo-50': isSelected(perm.value) }"
        >
          <input
            type="checkbox"
            :value="perm.value"
            :checked="isSelected(perm.value)"
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            @change="togglePermission(perm.value)"
          />
          <span class="ml-2 text-sm text-gray-700">{{ perm.label }}</span>
        </label>
      </div>

      <p v-else class="text-sm text-gray-500 text-center py-4">
        {{ __('No permissions available') }}
      </p>
    </div>
  </FormModal>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useFormStore, useIndexStore, useModalsStore } from 'spack'
  import { FormModal, useFieldText } from 'thetheme'
  import type { RoleForm } from 'types'

  defineProps<{
    id?: number
  }>()

  const name = 'role'
  const form = useFormStore<RoleForm>(name)()
  const index = useIndexStore(name)()
  const FieldText = useFieldText<any>()

  // Total and selected counts
  const totalCount = computed(() => form.options.permissions?.length || 0)
  const selectedCount = computed(() => form.data.permissions?.length || 0)

  // Group permissions by their prefix (e.g., 'project', 'task', 'user')
  const groupedPermissions = computed(() => {
    const permissions = form.options.permissions || []
    if (!permissions.length) return {}

    const groups: Record<string, any[]> = {}

    permissions.forEach((perm: any) => {
      // Extract group from permission name (e.g., 'project:create' -> 'project')
      const parts = perm.label.split(':')
      const group = parts[0] || 'other'

      if (!groups[group]) {
        groups[group] = []
      }
      groups[group].push(perm)
    })

    // Sort groups alphabetically
    const sortedGroups: Record<string, any[]> = {}
    Object.keys(groups).sort().forEach(key => {
      sortedGroups[key] = groups[key]
    })

    return sortedGroups
  })

  function isSelected(value: number) {
    return form.data.permissions?.includes(value)
  }

  function togglePermission(value: number) {
    if (!form.data.permissions) {
      form.data.permissions = []
    }

    const idx = form.data.permissions.indexOf(value)
    if (idx > -1) {
      form.data.permissions.splice(idx, 1)
    } else {
      form.data.permissions.push(value)
    }
  }

  function getSelectedCount(permissions: any[]) {
    return permissions.filter(p => isSelected(p.value)).length
  }

  function formatPermissionLabel(label: string) {
    // Convert 'project:create' to 'Create' or 'user:view' to 'View'
    const parts = label.split(':')
    if (parts.length > 1) {
      return parts[1].charAt(0).toUpperCase() + parts[1].slice(1).replace('_', ' ')
    }
    return label
  }

  function formatGroupName(group: string) {
    // Convert 'advance_request' to 'Advance Request'
    return group.replace(/_/g, ' ')
  }

  function selectGroup(permissions: any[]) {
    if (!form.data.permissions) {
      form.data.permissions = []
    }
    permissions.forEach((perm: any) => {
      if (!form.data.permissions.includes(perm.value)) {
        form.data.permissions.push(perm.value)
      }
    })
  }

  form.onSuccess((response) => {
    // The model is in response.data.model from FormRequest
    const model = response.data?.model || response.data
    if (model) {
      index.updateOrCreate(model)
    }
    // Refresh the list to get updated counts
    index.fetch()
    useModalsStore().pop()
  })

  function select() {
    form.data.permissions = form.options.permissions?.map((x: any) => x.value) || []
  }

  function deselect() {
    form.data.permissions = []
  }
</script>
