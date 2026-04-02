import {
  hasAnyValue,
  hasValue,
} from '@/modules/negotiations/suppliers/helpers/field-validation-helper';
import type { FormSectionErrors } from '@/modules/negotiations/supplier-new/types/supplier-registration/policies';
import type { DataObject } from '@/modules/negotiations/suppliers/types';

export const validateRequiredFields = (
  keys: string[],
  data: DataObject,
  formSectionErrors: FormSectionErrors
): boolean => {
  const invalidKeys = keys.filter((key) => !hasValue(key, data));

  invalidKeys.forEach((key) => {
    formSectionErrors[key].isInvalid = true;
  });

  return invalidKeys.length === 0;
};

export const validateAllIfAnyFieldPresent = (
  keys: string[],
  data: DataObject,
  formSectionErrors: FormSectionErrors
): boolean => {
  if (hasAnyValue(keys, data)) {
    return validateRequiredFields(keys, data, formSectionErrors);
  }

  return true;
};
