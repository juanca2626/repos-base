import { ValueTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/value-type.enum';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import type { Ref } from 'vue';

export const toRegularSingularText = (value: number | null, text?: string): string => {
  if (value === 1 && text && text.endsWith('s')) {
    return text.slice(0, -1);
  }

  return text || '';
};

export const isValueTypeAmount = (valueType: ValueTypeEnum | string): boolean => {
  return valueType === ValueTypeEnum.AMOUNT;
};

export const getValueTypeSymbol = (valueType: ValueTypeEnum | string): string => {
  return valueType === ValueTypeEnum.AMOUNT ? '$' : '%';
};

export const formatValueWithSymbol = (value: number, valueType: ValueTypeEnum | string): string => {
  const symbol = getValueTypeSymbol(valueType);

  return isValueTypeAmount(valueType) ? `${symbol}${value}` : `${value}${symbol}`;
};

export const irregularPlurals: Record<string, string> = {
  Habitación: 'Habitaciones',
};

export const applyPluralToText = (value: number | null, text?: string) => {
  if (!text) return '';

  if (value === 1) return text;

  return irregularPlurals[text] ?? `${text}s`;
};

export const findLabel = (
  options: Ref<SelectOption[]>,
  value: string | number | null | undefined
): string | undefined => {
  if (value == null) return undefined;

  return options.value.find((option) => option.value === value)?.label;
};
