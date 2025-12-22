<template>
  <Teleport to="body">
    <TransitionGroup name="modal">
      <Component
        :is="component.component"
        v-bind="component.payload"
        v-for="(component, index) in store.components"
        :key="index"
      />
    </TransitionGroup>
  </Teleport>
</template>

<script setup lang="ts">
  import { useModalsStore } from 'spack'

  const store = useModalsStore()
</script>
