import { defineComponent, inject, renderSlot } from 'vue'
import { useFormStore } from 'spack'
import type { PropType } from 'vue'
import FieldBase from './FieldBase.vue'

interface FormData {
  [key: string]: any
}

export function useFieldSelect<T extends FormData = FormData>() {
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
      inputClass: {
        type: String,
        default: '',
      },
      labelKey: {
        type: String,
        default: 'label',
      },
      valueKey: {
        type: String,
        default: 'value',
      },
    },

    setup(props, { slots }) {
      const formName = inject('form_name', ''),
        __ = inject('__') as (word: string) => string,
        form = useFormStore<T>(formName)(),
        optionNull = null
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
          <div class="flex flex-wrap">
            <select
              required
              id={formName + '-' + props.name}
              class={{
                'block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm':
                  true,
                'border-gray-300': !form.errors[props.name],
                'border-red-600': form.errors[props.name],
                'sm:max-w-full': props.full,
                'sm:max-w-lg': props.lg,
                'max-w-lg sm:max-w-xs': props.inline,
              }}
              // @ts-ignore
              v-model={form.data[props.name]}
            >
              <option value={optionNull} disabled>
                {__('Choose')} {props.label}
              </option>

              {form.options[props.name]?.map((option: any) => {
                return (
                  <option value={option[props.valueKey]}>
                    {option[props.labelKey]}
                  </option>
                )
              })}
            </select>

            {slots.default && slots.default()}
          </div>
        </FieldBase>
      )
    },
  })
}
