<template>
  <SettingsLayout>
    <Card>
      <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900">{{ __('Sidebar Features') }}</h3>
        <p class="mt-1 text-sm text-gray-500">
          {{ __('Enable or disable sidebar menu items. Disabled items will not appear in the navigation.') }}
        </p>
      </div>

      <div v-if="loading" class="flex justify-center py-8">
        <Loader size="40" color="#5850ec" />
      </div>

      <div v-else class="mt-4">
        <div class="space-y-4">
          <div
            v-for="feature in features"
            :key="feature.key"
            class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3"
          >
            <div>
              <span class="text-sm font-medium text-gray-900">{{ feature.label }}</span>
              <span v-if="feature.uri" class="ml-2 text-xs text-gray-500">{{ feature.uri }}</span>
            </div>
            <button
              type="button"
              :class="[
                feature.enabled ? 'bg-indigo-600' : 'bg-gray-200',
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2'
              ]"
              role="switch"
              :aria-checked="feature.enabled"
              @click="toggleFeature(feature)"
            >
              <span
                :class="[
                  feature.enabled ? 'translate-x-5' : 'translate-x-0',
                  'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                ]"
              />
            </button>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <TheButton :loading="saving" @click="saveFeatures">
            {{ __('Save Changes') }}
          </TheButton>
        </div>
      </div>
    </Card>
  </SettingsLayout>
</template>

<script setup lang="ts">
import { ref, onMounted, inject } from 'vue'
import { axios } from 'spack/axios'
import SettingsLayout from './SettingsLayout.vue'
import { Card, Loader, TheButton } from 'thetheme'

const __ = inject('__') as any

interface Feature {
  key: string
  label: string
  uri: string | null
  enabled: boolean
}

const features = ref<Feature[]>([])
const loading = ref(true)
const saving = ref(false)

onMounted(async () => {
  await loadFeatures()
})

async function loadFeatures() {
  try {
    const response = await axios.get('settings/sidebar-features')
    features.value = response.data.features
  } catch (error) {
    console.error('Failed to load sidebar features:', error)
  } finally {
    loading.value = false
  }
}

function toggleFeature(feature: Feature) {
  feature.enabled = !feature.enabled
}

async function saveFeatures() {
  saving.value = true
  try {
    const disabledFeatures = features.value
      .filter((f) => !f.enabled)
      .map((f) => f.key)

    await axios.post('settings/sidebar-features', {
      disabled_features: disabledFeatures,
    })

    alert('Sidebar features saved successfully! Please refresh the page to see changes.')
  } catch (error) {
    console.error('Failed to save sidebar features:', error)
    alert('Failed to save sidebar features.')
  } finally {
    saving.value = false
  }
}
</script>
