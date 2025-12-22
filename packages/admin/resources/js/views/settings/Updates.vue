<template>
  <SettingsLayout>
    <Card class="p-6">
      <h3 class="mb-6 font-medium text-gray-900">
        {{ __('Current Version') }}:
        <span class="text-gray-500 ltr:ml-2 rtl:mr-2">
          {{ updates.version }}
        </span>
      </h3>

      <label class="block text-sm font-medium text-gray-700">
        {{ __('Purchase Code') }}
      </label>

      <div
        class="mt-1 flex items-center rounded-md border border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
      >
        <input
          v-model="purchaseCode"
          :type="inputType"
          :placeholder="__('Purchase Code')"
          class="flex-1 rounded-md border-0 text-sm focus:border-0 focus:outline-none focus:ring-0"
        />

        <div class="cursor-pointer px-2" @click="handleInputType">
          <EyeIcon v-show="inputType == 'text'" class="h-4 w-4 text-gray-500" />
          <EyeSlashIcon
            v-show="inputType == 'password'"
            class="h-4 w-4 text-gray-500"
          />
        </div>
      </div>

      <div
        v-if="errors.purchase_code"
        class="mt-2 text-xs italic leading-normal text-red-600"
      >
        {{ errors.purchase_code[0] }}
      </div>

      <div
        v-if="updates.update_available"
        class="mt-6 mb-6 flex items-center justify-between rounded-md border border-gray-200 p-4"
      >
        <div>
          {{ __('A new Spack update is available.') }}
          <span class="text-indigo-600">{{ updates.new_version }}</span>
          <p class="mt-1 text-gray-500">
            {{
              __(
                'while updating it will down your app and take some time to update.',
              )
            }}
          </p>
        </div>

        <TheButton
          white
          :processing="updateProcessing"
          :disabled="processing"
          @click="updateNow"
        >
          {{ __('Update Now') }}
        </TheButton>
      </div>

      <div
        v-if="uptodate"
        class="mt-4 mb-6 flex items-center justify-between rounded-md border border-gray-200 p-4"
      >
        {{ __('Spack is up to date.') }}
      </div>

      <TheButton
        class="mt-4"
        :processing="checkProcessing"
        :disabled="processing"
        size="sm"
        @click="checkUpdates"
      >
        {{ __('Check for Updates') }}
      </TheButton>

      <p class="mt-2 text-sm text-gray-500">
        <span v-if="updates.checked_at">
          {{ __('Last checked at') }}: {{ updates.checked_at }}
        </span>
        <span v-else>{{ __('Never checked for updates') }}</span>
      </p>
    </Card>
  </SettingsLayout>
</template>

<script setup lang="ts">
  import { axios, useToastStore } from 'spack'
  import { inject, ref } from 'vue'
  import { useRouter } from 'vue-router'
  import SettingsLayout from './SettingsLayout.vue'
  import { EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
  import { appData } from '@/app-data'
  import { Card, TheButton } from 'thetheme'

  let updates = ref(appData.app_updates),
    processing = ref(false),
    checkProcessing = ref(false),
    updateProcessing = ref(false),
    uptodate = ref(false),
    purchaseCode = ref(appData.purchase_code),
    errors = ref<any>({}),
    inputType = ref('password'),
    router = useRouter(),
    can = inject('can') as (permission: string) => boolean

  if (!can('setting:updates')) {
    router.push('/404')
  }

  function checkUpdates() {
    processing.value = true
    checkProcessing.value = true

    axios
      .post('check-updates', {
        purchase_code: purchaseCode.value,
        domain: window.location.host,
        url: window.location.href,
      })
      .then(({ data }) => {
        updates.value = data

        if (data.update_available) {
          uptodate.value = false
        } else {
          uptodate.value = true
        }

        errors.value = {}
        processing.value = false
        checkProcessing.value = false
      })
      .catch((error) => {
        if (error.response.status == 422) {
          errors.value = error.response.data.errors
        }

        processing.value = false
        checkProcessing.value = false
        useToastStore().danger(error.response.data.message)
      })
  }

  function updateNow() {
    processing.value = true
    updateProcessing.value = true

    axios
      .post('update-app')
      .then(({ data }) => {
        if (data.status == 'done') {
          runUpdateRecipe()
        }
      })
      .catch((error) => {
        processing.value = false
        updateProcessing.value = false
        useToastStore().danger(error.response.data.message)
      })
  }

  function runUpdateRecipe() {
    axios
      .post('update-recipe')
      .then(({ data }) => {
        if (data.success) {
          location.reload()
          // updates.value = data.updates
        }
        // processing.value = false
        // updateProcessing.value = false
      })
      .catch((error) => {
        processing.value = false
        updateProcessing.value = false
        useToastStore().danger(error.response.data.message)
      })
  }

  function handleInputType() {
    if (inputType.value == 'password') {
      inputType.value = 'text'
    } else {
      inputType.value = 'password'
    }
  }
</script>
