// src/@operations/modules/operational-guidelines/utils/validation.ts

import { message } from 'ant-design-vue';

export const validateSelection = (formState: any, selectedValues: string[], index: number) => {
  const otherIndex: number = index === 0 ? 1 : 0;
  const otherSelectedValues: string[] = formState.values[otherIndex] || [];

  const duplicates = selectedValues.filter((value) => otherSelectedValues.includes(value));

  if (duplicates.length > 0) {
    message.error(
      `El proveedor ya ha sido seleccionado como ${index === 0 ? 'bloqueado' : 'preferente'}.`
    );
    formState.values[index] = selectedValues.filter((value) => !duplicates.includes(value));
    return false; // Retorna false si hay duplicados
  }

  console.log('Selección válida:', selectedValues);
  return true; // Retorna true si la selección es válida
};
