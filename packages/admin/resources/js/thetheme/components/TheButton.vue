<template>
  <a v-if="href" :href="href" :class="[classes]">
    <slot />
    <Loader
      v-if="processing"
      class="ltr:ml-2 rtl:mr-2"
      :size="loaderSize"
      :color="loaderColor"
    />
  </a>

  <button v-else :class="[classes]" :disabled="disabled || processing">
    <slot />
    <Loader
      v-if="processing"
      class="ltr:ml-2 rtl:mr-2"
      :size="loaderSize"
      :color="loaderColor"
    />
  </button>
</template>

<script setup lang="ts">
  import Loader from './Loader.vue'

  const props = withDefaults(
    defineProps<{
      href?: string
      processing?: boolean
      disabled?: boolean
      white?: boolean
      danger?: boolean
      size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl'
    }>(),
    {
      href: '',
      size: 'md',
    },
  )

  let classes =
      'inline-flex items-center border font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2',
    sizes: {
      xs: string
      sm: string
      md: string
      lg: string
      xl: string
    } = {
      xs: ' px-2.5 py-1.5 text-xs rounded',
      sm: ' px-3 py-2 text-sm leading-4 rounded-md',
      md: ' px-4 py-2 text-sm rounded-md',
      lg: ' px-4 py-2 text-base rounded-md',
      xl: ' px-6 py-3 text-base rounded-md',
    },
    loaderSizes = {
      xs: '16',
      sm: '16',
      md: '20',
      lg: '20',
      xl: '22',
    },
    whiteClass =
      ' text-gray-700 border-gray-300 bg-white hover:bg-gray-50 focus:ring-indigo-500',
    dangerClass =
      ' text-white border-transparent bg-red-600 hover:bg-red-700 focus:ring-red-500',
    defaultClass =
      ' text-white border-transparent bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
    loaderColor = props.white ? '' : 'text-white',
    loaderSize = loaderSizes[props.size]

  if (props.white) classes = classes.concat(whiteClass, sizes[props.size])
  else if (props.danger)
    classes = classes.concat(dangerClass, sizes[props.size])
  else classes = classes.concat(defaultClass, sizes[props.size])
</script>
