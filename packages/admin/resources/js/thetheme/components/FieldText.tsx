import { defineComponent, inject } from 'vue'
import { useFormStore } from 'spack'
import type { PropType } from 'vue'
import FieldBase from './FieldBase.vue'

interface FormData {
  [key: string]: any
}

export function useFieldText<T extends FormData = FormData>() {
  return defineComponent({
    props: {
      name: {
        type: String as unknown as PropType<keyof T>,
        required: true,
      },
      type: {
        type: String as PropType<'text' | 'password'>,
        default: 'text',
      },
      label: {
        type: String,
        default: '',
      },
      placeholder: {
        type: String,
        default: '',
      },
      inline: Boolean,
      lg: Boolean,
      full: Boolean,
      disabled: Boolean,
    },

    setup(props) {
      const formName = inject('form_name', ''),
        form = useFormStore<T>(formName)()

      // fieldClassError = 'border-red-600',
      // fieldClassDefault = 'mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm rounded-md border-gray-300',
      // fieldClass = reactive({
      //   'mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm rounded-md': true,
      //   'border-gray-300': !form.errors[props.name],
      //   'border-red-600': form.errors[props.name],
      // //   'sm:max-w-full': props.full,
      // //   'sm:max-w-lg': props.lg,
      // //   'max-w-lg sm:max-w-xs': props.inline,
      // })

      return () => (
        <FieldBase
          inline={props.inline}
          label={props.label}
          name={props.name}
          lg={props.lg}
          full={props.full}
        >
          <input
            // @ts-ignore
            v-model={form.data[props.name]}
            type={props.type}
            id={formName + '-' + props.name}
            placeholder={props.label}
            disabled={props.disabled}
            class={{
              'mt-1 block w-full rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm':
                true,
              'border-gray-300': !form.errors[props.name],
              'border-red-600': form.errors[props.name],
              'sm:max-w-full': props.full,
              'sm:max-w-lg': props.lg,
              'max-w-lg sm:max-w-xs': props.inline,
            }}
          />
        </FieldBase>
      )
    },
  })
}
