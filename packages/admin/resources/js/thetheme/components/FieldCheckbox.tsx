import { defineComponent, inject } from 'vue'
import { useFormStore } from 'spack'
import type { PropType } from 'vue'
import FieldBase from './FieldBase.vue'

export function useFieldCheckbox<T>() {
  return defineComponent({
    props: {
      name: {
        type: String as unknown as PropType<keyof T>,
        required: true,
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
      inputClass: {
        type: String,
        default: '',
      },
    },

    setup(props) {
      const formName = inject('form_name', ''),
        // __ = inject('__'),
        form = useFormStore<T>(formName)()
      // optionNull = null

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
          <div class="grid grid-cols-3 gap-2">
            {form.options[props.name]?.map((option: any) => (
              <div class="flex items-center">
                <input
                  id={`${formName}-${props.name}-${option.value}`}
                  type="checkbox"
                  value={option.value}
                  class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                  // @ts-ignore
                  v-model={form.data[props.name]}
                />
                <label
                  for={`${formName}-${props.name}-${option.value}`}
                  class="ml-3 block whitespace-nowrap text-sm font-medium text-gray-700"
                >
                  {option.label}
                </label>
              </div>
            ))}
          </div>
        </FieldBase>
      )
    },
  })
}
