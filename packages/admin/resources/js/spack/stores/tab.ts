import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export const useTabStore = (name: string = 'tab') => {
  return defineStore(`tab-${name}`, () => {
    const active = ref<string | number>(0)
    const activeTab = ref<string | number>(0)
    const tabsList = ref<any[]>([])

    const tab = computed(() => {
      const activeIndex = typeof active.value === 'number' ? active.value : parseInt(active.value as string)
      return tabsList.value[activeIndex]?.component
    })

    function setTab(tab: string | number) {
      activeTab.value = tab
      active.value = tab
    }

    function select(tab: string | number) {
      active.value = tab
      activeTab.value = tab
    }

    function tabs(list: any[]) {
      tabsList.value = list
    }

    function reset() {
      activeTab.value = 0
      active.value = 0
    }

    return {
      active,
      activeTab,
      tabsList,
      tab,
      setTab,
      select,
      tabs,
      reset,
    }
  })
}
