<template>
  <Dropdown @toggle="onOpen">
    <template #trigger>
      <button
        class="relative bg-white p-1 text-gray-400 hover:text-gray-500 focus:outline-none"
        aria-label="Notifications"
      >
        <svg
          class="h-6 w-6"
          stroke="currentColor"
          fill="none"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
          />
        </svg>
        <div
          v-if="count"
          class="absolute -top-0.5 z-40 flex h-[1.125rem] w-[1.125rem] items-center justify-center rounded-full bg-red-600 text-[0.65rem] text-white ltr:-right-0.5 rtl:-left-0.5"
        >
          {{ count > 9 ? '9+' : count }}
        </div>
      </button>
    </template>

    <template #content>
      <div
        class="absolute mt-2 w-48 origin-top-right overflow-hidden rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none ltr:right-0 rtl:left-0"
        role="menu"
        aria-orientation="vertical"
        aria-labelledby="user-menu"
      >
        <p
          v-if="!notifications.length"
          class="py-4 text-center text-sm text-gray-700"
        >
          {{ __('No Notifications!') }}
        </p>

        <div v-else>
          <router-link
            v-for="notification in notifications"
            v-slot="{ href, navigate }"
            :key="notification.id"
            custom
            :to="getUrl(notification)"
          >
            <a
              :href="href"
              class="block px-4 py-2 text-sm text-gray-700 transition duration-150 ease-in-out hover:bg-gray-100"
              role="menuitem"
              @click="navigate"
            >
              {{ notification.data.message }}
            </a>
          </router-link>
        </div>
      </div>
    </template>
  </Dropdown>
</template>

<script setup lang="ts">
  import { onUnmounted, ref } from 'vue'
  import { axios } from 'spack'
  import { Dropdown } from 'thetheme'
  // import Echo from 'laravel-echo'
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  import Pusher from 'pusher-js'
  // import { appData } from '@/app-data'

  const notifications = ref<any>([]),
    count = ref()

  // @ts-ignore
  window.Pusher = Pusher

  // const E = new Echo({
  //   broadcaster: 'pusher',
  //   key: appData.PUSHER_APP_KEY,
  //   cluster: appData.PUSHER_APP_CLUSTER,
  //   forceTLS: true,
  // })

  // E.private(`App.Models.User.${appData.user.id}`).notification(
  //   (notification: any) => {
  //     console.log('notification')
  //     console.log(notification)
  //     count.value = count.value + 1
  //     notifications.value.push({
  //       type: notification.type,
  //       data: {
  //         item_id: notification.item_id,
  //         project_id: notification.project_id,
  //         message: notification.message,
  //       },
  //     })
  //   },
  // )

  // Import notification sound composable
  import { playNotificationSound } from '@/composables/notification-sound'

  let pollingInterval: number | null = null
  let previousCount = 0

  function fetchNotifications() {
    axios.get('notifications').then((response) => {
      const newCount = response.data.new

      // Play sound if there are new notifications
      if (newCount > previousCount && previousCount >= 0) {
        playNotificationSound()
      }

      previousCount = newCount
      count.value = newCount
      notifications.value = response.data.notifications
    })
  }

  // Initial fetch
  fetchNotifications()

  // Start polling every 10 seconds
  pollingInterval = window.setInterval(() => {
    if (document.hasFocus()) { // Only poll when tab is active
      fetchNotifications()
    }
  }, 10000) // 10 seconds

  // Cleanup on unmount
  onUnmounted(() => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
    }
  })

  function onOpen(payload: any) {
    if (payload && count.value) {
      axios.post('/notifications/read').then((response) => {
        if (response.data.success) {
          count.value = 0
          previousCount = 0
        }
      })
    }
  }

  function getUrl(notification: { type: string; data: any }) {
    const types: any = {
      'App\\Notifications\\ProjectAssigned':
        '/projects/' + notification.data.item_id,
      'App\\Notifications\\TaskAssigned':
        '/projects/' + notification.data.project_id,
    }

    return types[notification.type]
  }
</script>
