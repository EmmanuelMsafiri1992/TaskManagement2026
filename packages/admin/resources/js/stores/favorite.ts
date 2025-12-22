import { defineStore } from 'pinia'
import { ref } from 'vue'
import { axios } from 'spack'

export const useFavoriteStore = defineStore('favorite', () => {
  const items = ref<any>([])

  async function fetch() {
    const { data } = await axios.get('favorites')
    items.value = data
    console.log('onfetch favorites')
    console.log(items.value)
  }

  return { items, fetch }
})
