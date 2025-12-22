import { defineComponent } from 'vue'
import { useStore } from 'Store/main'
import { RouterView } from 'vue-router'
import Layout from 'View/layouts/Layout'

export default defineComponent({
  setup() {
    const store = useStore()

    return () => (store.isBlankPage ? <RouterView /> : <Layout />)
  },
})
