import { defineStore } from 'pinia'
import { ref, markRaw } from 'vue'
import type { Component } from 'vue'

interface ModalComponent {
  component: Component
  payload?: Record<string, any>
}

export const useModalsStore = defineStore('modals', () => {
  const components = ref<ModalComponent[]>([])
  const hideCallbacks = ref<Map<number, Function>>(new Map())

  function add(component: Component, payload: Record<string, any> = {}) {
    components.value.push({
      component: markRaw(component),
      payload,
    })
  }

  function pop() {
    const currentIndex = components.value.length - 1
    const callback = hideCallbacks.value.get(currentIndex)
    if (callback) {
      callback()
      hideCallbacks.value.delete(currentIndex)
    }
    components.value.pop()
  }

  function clear() {
    components.value = []
    hideCallbacks.value.clear()
  }

  function onHide(callback: Function) {
    const currentIndex = components.value.length - 1
    hideCallbacks.value.set(currentIndex, callback)
  }

  return {
    components,
    add,
    pop,
    clear,
    onHide,
  }
})
