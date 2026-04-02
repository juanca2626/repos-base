import type { DataObject } from '@/modules/negotiations/suppliers/types';

export const hasValue = (key: string, data: DataObject): boolean => {
  return key in data && data[key] !== null && data[key] !== undefined && data[key] !== '';
};

export const hasAnyValue = (keys: string[], data: DataObject): boolean => {
  return keys.some((key) => hasValue(key, data));
};

export const hasAllValues = (keys: string[], data: DataObject): boolean => {
  return keys.every((key) => hasValue(key, data));
};

export const isFilled = (value: any) => {
  return value !== null && value !== undefined && value !== '';
};
