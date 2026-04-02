import type { SelectProps } from 'ant-design-vue';

export const setDefaultSelectValue = <T>(
  options: SelectProps['options'] | undefined,
  formField: Record<string, any>,
  key: keyof T,
  defaultIndex: number = 0 // Índice opcional, por defecto 0 (primer valor)
) => {
  if (Array.isArray(options) && options.length > defaultIndex && !formField[key as string]) {
    const defaultOption = options[defaultIndex]; // Usa el índice especificado

    if (defaultOption?.value !== undefined && defaultOption?.label !== undefined) {
      formField._id = defaultOption.value;
      formField[key as string] = defaultOption.value;
      formField.description = defaultOption.label;
    }
  }
};

export const mapToSelectOptions = <T extends { _id: string; iso: string; description: string }>(
  items: T[],
  allLabel: string
): SelectProps['options'] => [
  { value: 'ALL', label: allLabel },
  ...items.map(({ _id, iso, description }) => ({ id: _id, value: iso, label: description })),
];
