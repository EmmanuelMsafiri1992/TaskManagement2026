<template>
  <div class="mb-4 flex">
    <h1 class="text-2xl font-semibold text-gray-900" data-cy="page-title">
      {{ title }}
    </h1>

    <slot />

    <RouterLink
      v-if="btnText && btnTo && can(permission || '')"
      v-slot="{ navigate }"
      :to="btnTo"
      custom
    >
      <TheButton
        class="ml-auto"
        data-cy="topbar-create-button"
        @click="navigate"
      >
        {{ btnText }}
      </TheButton>
    </RouterLink>
  </div>
</template>

<script setup lang="ts">
  import TheButton from './TheButton.vue'

  defineProps<{
    title: string
    btnTo?: string
    btnText?: string
    permission?: string
  }>()
</script>
