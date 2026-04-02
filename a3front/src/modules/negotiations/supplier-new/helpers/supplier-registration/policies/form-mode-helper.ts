import { FormModeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/form-mode.enum';

export const isCreateFormMode = (mode: FormModeEnum | null): boolean => {
  return mode === FormModeEnum.CREATE;
};

export const isEditFormMode = (mode: FormModeEnum | null): boolean => {
  return mode === FormModeEnum.EDIT;
};

export const isCloneFormMode = (mode: FormModeEnum | null): boolean => {
  return mode === FormModeEnum.CLONE;
};
