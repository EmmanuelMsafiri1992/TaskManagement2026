<template>
  <div class="flex">
    <div v-if="!noSearch" class="relative mb-4 w-64 rounded-md shadow-sm">
      <div
        class="pointer-events-none absolute inset-y-0 flex items-center ltr:left-0 ltr:pl-3 rtl:right-0 rtl:pr-3"
      >
        <svg
          class="h-5 w-5 text-gray-400"
          x-description="Heroicon name: search"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path
            fill-rule="evenodd"
            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
            clip-rule="evenodd"
          ></path>
        </svg>
      </div>
      <input
        v-model="index.params.search"
        type="search"
        :placeholder="__('Search')"
        class="block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 ltr:pl-10 rtl:pr-10 sm:text-sm"
        @input="index.onSearch"
      />
    </div>

    <IndexFilters v-if="!noFilters" :name="name" />
  </div>

  <div v-if="index.fetching" class="mt-8 flex justify-center">
    <Loader size="40" color="#5850ec" />
  </div>

  <div v-else :data-cy="name ? 'index-' + name : 'index'">
    <IndexNoData
      v-if="!index.items?.length"
      :no-create="noCreate || cannot(`${name}:create`)"
      :btn-text="btnText"
      :btn-to="btnTo"
    />

    <slot v-else :index="index" :items="index.items" />
  </div>
</template>

<script setup lang="ts">
  import { useIndexStore } from 'spack'
  import IndexFilters from './IndexFilters.vue'
  import IndexNoData from './IndexNoData.vue'
  import Loader from './Loader.vue'

  const props = withDefaults(
    defineProps<{
      name: string
      uri: string
      filterUri?: string
      shallowUri?: string
      btnTo?: string
      btnText?: string
      noCreate?: boolean
      noFilters?: boolean
      noSearch?: boolean
      orderByDirection?: string
      searchColumn?: string
    }>(),
    {
      searchColumn: 'name',
      orderByDirection: 'desc',
      filterUri: '',
      shallowUri: '',
      btnTo: '',
      btnText: '',
    },
  )

  const index = useIndexStore(props.name)()

  index.setConfig(props)
  index.fetch()

  if (!props.noFilters) index.getFilters()
</script>
