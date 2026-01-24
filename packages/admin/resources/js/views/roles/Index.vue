<template>
  <SettingsLayout>
    <template #topbar>
      <TheButton
        v-if="can('role:create')"
        class="ml-auto"
        size="sm"
        data-cy="topbar-create-button"
        @click="openModal()"
      >
        {{ __('Create Role') }}
      </TheButton>
    </template>

    <div v-if="loading" class="mt-8 flex justify-center">
      <Loader size="40" color="#5850ec" />
    </div>

    <div v-else class="space-y-6">
      <!-- Roles Cards -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div
          v-for="role in roles"
          :key="role.id"
          class="overflow-hidden rounded-lg bg-white shadow"
        >
          <!-- Role Header -->
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ role.name }}</h3>
                <p class="text-sm text-gray-500">
                  {{ role.permissions_count }} {{ __('permissions') }} · {{ role.users_count }} {{ __('users') }}
                </p>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  v-if="can('role:update')"
                  class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600"
                  :title="__('Edit Role')"
                  @click="openModal(role.id)"
                >
                  <PencilSquareIcon class="h-5 w-5" />
                </button>
                <button
                  v-if="can('role:update')"
                  class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-indigo-600"
                  :title="__('Manage Users')"
                  @click="openUserModal(role)"
                >
                  <UserPlusIcon class="h-5 w-5" />
                </button>
                <button
                  v-if="can('role:delete') && role.users_count === 0"
                  class="rounded p-1 text-gray-400 hover:bg-gray-100 hover:text-red-600"
                  :title="__('Delete Role')"
                  @click="deleteRole(role)"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Users List -->
          <div class="px-6 py-4">
            <div v-if="role.users && role.users.length > 0" class="space-y-3">
              <div
                v-for="user in role.users"
                :key="user.id"
                class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2"
              >
                <div class="flex items-center space-x-3">
                  <UserAvatar :user="user" size="8" />
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                    <p class="text-xs text-gray-500">{{ user.email }}</p>
                  </div>
                </div>
                <button
                  v-if="can('role:update')"
                  class="rounded p-1 text-gray-400 hover:bg-red-100 hover:text-red-600"
                  :title="__('Remove from role')"
                  @click="removeUserFromRole(role, user)"
                >
                  <XMarkIcon class="h-4 w-4" />
                </button>
              </div>
            </div>
            <div v-else class="py-4 text-center text-sm text-gray-500">
              {{ __('No users assigned to this role') }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Assignment Modal -->
    <TransitionRoot appear :show="userModal.show" as="template">
      <Dialog as="div" class="relative z-50" @close="userModal.show = false">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-black bg-opacity-25" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4 text-center">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                  {{ __('Assign Users to') }} {{ userModal.role?.name }}
                </DialogTitle>

                <div class="mt-4">
                  <p class="text-sm text-gray-500 mb-4">
                    {{ __('Select users to add to this role. Users can have multiple roles.') }}
                  </p>

                  <!-- Search -->
                  <div class="relative mb-4">
                    <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                    <input
                      v-model="userModal.search"
                      type="text"
                      class="block w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                      :placeholder="__('Search users...')"
                    />
                  </div>

                  <!-- Users List -->
                  <div class="max-h-64 space-y-2 overflow-y-auto">
                    <label
                      v-for="user in filteredUsers"
                      :key="user.id"
                      class="flex cursor-pointer items-center rounded-lg border border-gray-200 p-3 hover:bg-gray-50"
                      :class="{ 'bg-indigo-50 border-indigo-300': userModal.selectedUsers.includes(user.id) }"
                    >
                      <input
                        v-model="userModal.selectedUsers"
                        type="checkbox"
                        :value="user.id"
                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                      />
                      <div class="ml-3 flex items-center space-x-3">
                        <UserAvatar :user="user" size="8" />
                        <div>
                          <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                          <p class="text-xs text-gray-500">
                            {{ user.email }}
                            <span v-if="user.roles?.length" class="ml-1">
                              · {{ user.roles.map(r => r.name).join(', ') }}
                            </span>
                          </p>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                  <button
                    type="button"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                    @click="userModal.show = false"
                  >
                    {{ __('Cancel') }}
                  </button>
                  <button
                    type="button"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                    :disabled="userModal.saving"
                    @click="assignUsers"
                  >
                    {{ userModal.saving ? __('Saving...') : __('Assign Users') }}
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { MagnifyingGlassIcon, PencilSquareIcon, TrashIcon, UserPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { useFlashStore, useIndexStore, useModalsStore } from 'spack'
import { axios } from 'spack/axios'
import { TheButton } from 'thetheme'
import SettingsLayout from 'View/settings/SettingsLayout.vue'
import UserAvatar from '@/thetheme/components/UserAvatar.vue'
import Loader from '@/thetheme/components/Loader.vue'
import Form from './Form.vue'
import { can } from '@/helpers'

const flash = useFlashStore()

const loading = ref(true)
const allUsers = ref<any[]>([])

// Initialize the index store for roles
const index = useIndexStore('role')()
index.uri = 'roles'

// Computed to get roles from the store
const roles = computed(() => {
  return index.items || []
})

const userModal = reactive({
  show: false,
  role: null as any,
  search: '',
  selectedUsers: [] as number[],
  saving: false,
})

const filteredUsers = computed(() => {
  if (!userModal.search) return allUsers.value
  const search = userModal.search.toLowerCase()
  return allUsers.value.filter(
    (user) =>
      user.name.toLowerCase().includes(search) ||
      user.email.toLowerCase().includes(search)
  )
})

function openModal(id = null) {
  useModalsStore().add(Form, { id })
}

async function fetchRoles() {
  try {
    await index.fetch()
  } catch (error) {
    console.error('Failed to fetch roles:', error)
  }
}

async function fetchUsers() {
  try {
    const response = await axios.get('roles-users')
    allUsers.value = response.data.data || response.data
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

function openUserModal(role: any) {
  userModal.role = role
  userModal.search = ''
  userModal.selectedUsers = role.users?.map((u: any) => u.id) || []
  userModal.show = true
}

async function assignUsers() {
  if (!userModal.role) return

  userModal.saving = true
  try {
    const response = await axios.post(`roles/${userModal.role.id}/assign-users`, {
      user_ids: userModal.selectedUsers,
    })
    await fetchRoles()
    userModal.show = false
    flash.success(response.data?.message || 'Users assigned successfully!')
  } catch (error: any) {
    console.error('Failed to assign users:', error)
    flash.error(error.response?.data?.message || 'Failed to assign users')
  } finally {
    userModal.saving = false
  }
}

async function removeUserFromRole(role: any, user: any) {
  if (!confirm(`Remove ${user.name} from ${role.name}?`)) return

  try {
    const response = await axios.post(`roles/${role.id}/remove-user`, {
      user_id: user.id,
    })
    await fetchRoles()
    flash.success(response.data?.message || 'User removed from role successfully!')
  } catch (error: any) {
    console.error('Failed to remove user:', error)
    flash.error(error.response?.data?.message || 'Failed to remove user from role')
  }
}

async function deleteRole(role: any) {
  if (!confirm(`Delete role "${role.name}"?`)) return

  try {
    const response = await axios.delete(`roles/${role.id}`)
    await fetchRoles()
    flash.success(response.data?.message || 'Role deleted successfully!')
  } catch (error: any) {
    console.error('Failed to delete role:', error)
    flash.error(error.response?.data?.message || 'Failed to delete role')
  }
}

onMounted(async () => {
  await Promise.all([fetchRoles(), fetchUsers()])
  loading.value = false
})
</script>
