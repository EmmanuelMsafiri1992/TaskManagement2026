<template>
  <div v-if="processing" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else>
    <!-- Tabs and Search Box -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex space-x-4">
        <button
          :class="[
            activeTab === 'active'
              ? 'border-indigo-500 text-indigo-600'
              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
            'border-b-2 px-1 pb-2 text-sm font-medium',
          ]"
          @click="switchTab('active')"
        >
          {{ __('Active') }}
        </button>
        <button
          :class="[
            activeTab === 'archived'
              ? 'border-indigo-500 text-indigo-600'
              : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
            'border-b-2 px-1 pb-2 text-sm font-medium',
          ]"
          @click="switchTab('archived')"
        >
          {{ __('Archived') }}
        </button>
      </div>
      <div class="relative w-64 rounded-md shadow-sm">
        <div
          class="pointer-events-none absolute inset-y-0 flex items-center ltr:left-0 ltr:pl-3 rtl:right-0 rtl:pr-3"
        >
          <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
          v-model="indexUser.params.search"
          type="search"
          :placeholder="__('Search users...')"
          class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
          @input="handleSearch"
        />
      </div>
    </div>

    <section v-if="activeTab === 'active' && indexInvitation.data?.data?.length" class="mb-8">
      <Topbar :title="__('Pending Invitations')">
        <div v-if="can('user:create')" class="ltr:ml-auto rtl:mr-auto">
          <TheButton
            size="sm"
            data-cy="topbar-invitation-create-button"
            @click="openInvitationModal()"
          >
            {{ __('Invite Team Member') }}
          </TheButton>
        </div>
      </Topbar>

      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div
            class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
          >
            <div
              class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg"
            >
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="invitation"
                      :index="indexInvitation"
                      :label="__('Email')"
                      sort="email"
                    />
                    <TableTh
                      name="invitation"
                      :index="indexInvitation"
                      :label="__('Role')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="item in indexInvitation.data.data" :key="item.id">
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      {{ item.email }}
                    </td>
                    <td
                      class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      {{ item.role.name }}
                    </td>

                    <td
                      class="flex items-center justify-end whitespace-nowrap px-6 py-4 text-right text-sm font-medium leading-5"
                    >
                      <TrashIcon
                        v-if="can('user:delete')"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.prevent="indexInvitation.deleteIt(item.id)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexPagination :index="indexInvitation" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="activeTab === 'active'">
      <Topbar :title="__('Team Members')">
        <div class="ltr:ml-auto rtl:mr-auto">
          <TheButton
            v-if="can('user:create') && !indexInvitation.data?.length"
            size="sm"
            data-cy="topbar-invitation-create-button"
            @click="openInvitationModal()"
          >
            {{ __('Invite Team Member') }}
          </TheButton>

          <TheButton
            v-if="can('user:create')"
            class="ltr:ml-2 rtl:mr-2"
            size="sm"
            data-cy="topbar-create-button"
            @click="openModal"
          >
            {{ __('Create Team Member') }}
          </TheButton>
        </div>
      </Topbar>

      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div
            class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
          >
            <div
              class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg"
            >
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="user"
                      :index="indexUser"
                      :label="__('Name')"
                      sort="name"
                    />
                    <TableTh
                      name="user"
                      :index="indexUser"
                      :label="__('Role')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="item in indexUser.data.data" :key="item.id">
                    <td
                      class="whitespace-no-wrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      <div class="flex">
                        <UserAvatar class="h-6 w-6" :avatar="item.avatar" />
                        <div class="text-sm ltr:ml-3 rtl:mr-3">
                          <span
                            class="mb-1 block truncate text-sm font-medium leading-none text-gray-900"
                          >
                            {{ item.name }}
                          </span>
                          <span class="block text-sm font-normal text-gray-500">
                            {{ item.email }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td
                      class="whitespace-no-wrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      {{ item.roles[0].name }}
                    </td>

                    <td
                      class="whitespace-no-wrap flex items-center justify-end px-6 py-4 text-right text-sm font-medium leading-5"
                    >
                      <UserIcon
                        v-if="canImpersonate(item)"
                        class="w-5 cursor-pointer text-gray-400 hover:text-indigo-600"
                        :title="__('Login as this user')"
                        @click.stop="impersonateUser(item)"
                      />

                      <span
                        v-if="can('user:update')"
                        class="ml-2"
                        @click="openModal(item.id)"
                      >
                        <PencilSquareIcon
                          class="w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        />
                      </span>

                      <ArchiveBoxIcon
                        v-if="can('user:delete') && item.id !== 1"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-orange-600"
                        :title="__('Archive user')"
                        @click.prevent="archiveUser(item)"
                      />

                      <TrashIcon
                        v-if="can('user:delete')"
                        class="ml-2 w-5 cursor-pointer text-gray-400 hover:text-gray-800"
                        @click.prevent="indexUser.deleteIt(item.id)"
                      />
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexPagination :index="indexUser" />
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Archived Users Section -->
    <section v-if="activeTab === 'archived'">
      <Topbar :title="__('Archived Team Members')">
        <div class="ltr:ml-auto rtl:mr-auto">
          <span class="text-sm text-gray-500">
            {{ __('These users cannot log in until restored') }}
          </span>
        </div>
      </Topbar>

      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div
            class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
          >
            <div
              class="overflow-hidden border-b border-gray-200 shadow sm:rounded-lg"
            >
              <table class="min-w-full divide-y divide-gray-200">
                <thead>
                  <tr>
                    <TableTh
                      name="archived-user"
                      :index="indexArchivedUser"
                      :label="__('Name')"
                      sort="name"
                    />
                    <TableTh
                      name="archived-user"
                      :index="indexArchivedUser"
                      :label="__('Role')"
                    />
                    <th class="bg-gray-50 px-6 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-gray-50">
                  <tr v-for="item in indexArchivedUser.data.data" :key="item.id" class="opacity-75">
                    <td
                      class="whitespace-no-wrap px-6 py-4 text-sm font-medium text-gray-500"
                    >
                      <div class="flex">
                        <UserAvatar class="h-6 w-6 grayscale" :avatar="item.avatar" />
                        <div class="text-sm ltr:ml-3 rtl:mr-3">
                          <span
                            class="mb-1 block truncate text-sm font-medium leading-none text-gray-600"
                          >
                            {{ item.name }}
                          </span>
                          <span class="block text-sm font-normal text-gray-400">
                            {{ item.email }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td
                      class="whitespace-no-wrap px-6 py-4 text-sm font-medium text-gray-400"
                    >
                      {{ item.roles[0]?.name }}
                    </td>

                    <td
                      class="whitespace-no-wrap flex items-center justify-end px-6 py-4 text-right text-sm font-medium leading-5"
                    >
                      <TheButton
                        v-if="can('user:delete')"
                        size="sm"
                        variant="secondary"
                        @click="unarchiveUser(item)"
                      >
                        {{ __('Restore') }}
                      </TheButton>
                    </td>
                  </tr>
                  <tr v-if="!indexArchivedUser.data?.data?.length">
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                      {{ __('No archived users found') }}
                    </td>
                  </tr>
                </tbody>
              </table>

              <IndexPagination :index="indexArchivedUser" />
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
  import { inject, ref } from 'vue'
  import { useIndexStore, useModalsStore } from 'spack'
  import {
    IndexPagination,
    Loader,
    TableTh,
    TheButton,
    Topbar,
    UserAvatar,
  } from 'thetheme'
  import InvitationForm from './InvitationForm.vue'
  import Form from './Form.vue'
  import {
    ArchiveBoxIcon,
    MagnifyingGlassIcon,
    PencilSquareIcon,
    TrashIcon,
    UserIcon,
  } from '@heroicons/vue/24/outline'
  import { axios } from 'spack/axios'
  import { appData } from '@/app-data'

  const can = inject('can')
  const __ = inject('__')
  const indexUser = useIndexStore('user')(),
    indexInvitation = useIndexStore('invitation')(),
    indexArchivedUser = useIndexStore('archived-user')(),
    processing = ref(true),
    activeTab = ref('active')

  checkProcessing()

  indexUser.setConfig({
    uri: 'users',
    orderByDirection: 'desc',
  })
  indexUser.fetch()

  indexArchivedUser.setConfig({
    uri: 'users-archived',
    orderByDirection: 'desc',
  })

  // Only fetch invitations if user has permission
  if (can('user:view') || can('user:create')) {
    indexInvitation.setConfig({
      uri: 'invitations',
      orderByDirection: 'desc',
    })
    indexInvitation.fetch()
  }

  function checkProcessing() {
    setTimeout(function () {
      if (indexUser.fetching || indexInvitation.fetching) {
        checkProcessing()
        return
      }

      renderData()
    }, 150)
  }

  function renderData() {
    processing.value = false
  }

  function switchTab(tab: string) {
    activeTab.value = tab
    if (tab === 'archived' && !indexArchivedUser.data?.data) {
      indexArchivedUser.fetch()
    }
  }

  async function archiveUser(user: any) {
    if (!confirm(`Are you sure you want to archive ${user.name}? They will no longer be able to log in.`)) {
      return
    }

    try {
      await axios.post(`users/${user.id}/archive`)
      indexUser.fetch()
      alert(`${user.name} has been archived`)
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to archive user')
    }
  }

  async function unarchiveUser(user: any) {
    if (!confirm(`Are you sure you want to restore ${user.name}?`)) {
      return
    }

    try {
      await axios.post(`users/${user.id}/unarchive`)
      indexArchivedUser.fetch()
      alert(`${user.name} has been restored`)
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to restore user')
    }
  }

  function openInvitationModal(id = null) {
    useModalsStore().add(InvitationForm, { id })
  }

  function openModal(id: number | null = null) {
    useModalsStore().add(Form, { id })
  }

  function handleSearch() {
    processing.value = true
    indexUser.onSearch()
    checkProcessing()
  }

  function canImpersonate(user: any) {
    // Only super admins can impersonate
    if (!appData.is_super_admin) return false
    // Cannot impersonate yourself
    if (user.id === appData.user?.id) return false
    return true
  }

  async function impersonateUser(user: any) {
    if (!confirm(`Are you sure you want to login as ${user.name}?`)) {
      return
    }

    try {
      const response = await axios.post(`impersonate/${user.id}`)
      if (response.data.success) {
        alert(`You are now logged in as ${user.name}`)
        window.location.href = '/'
      }
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to impersonate user')
    }
  }
</script>
