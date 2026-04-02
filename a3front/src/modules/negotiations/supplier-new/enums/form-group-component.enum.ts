import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';

export const FormGroupComponentEnum = {
  NEGOTIATIONS: [
    FormComponentEnum.CLASSIFICATION_SUPPLIER,
    FormComponentEnum.GENERAL_INFORMATION,
    FormComponentEnum.COMMERCIAL_LOCATION,
    FormComponentEnum.PLACE_OPERATION,
    FormComponentEnum.CONTACT_INFORMATION,
  ],
  TREASURY: [FormComponentEnum.BASIC_INFORMATION],
  ACCOUNTING: [FormComponentEnum.SUNAT_INFORMATION],
  MODULE_NEGOTIATIONS: [
    FormComponentEnum.MODULE_SERVICES,
    FormComponentEnum.MODULE_POLICIES,
    FormComponentEnum.MODULE_CONTACT,
    FormComponentEnum.MODULE_SUNAT_INFORMATION,
  ],
  MODULE_TREASURY: [
    FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION,
    FormComponentEnum.MODULE_TREASURY_BENEFICIARIES,
    FormComponentEnum.MODULE_ACCOUNTING_SUNAT_INFORMATION,
  ],
  MODULE_ACCOUNTING: [FormComponentEnum.MODULE_ACCOUNTING_SUNAT_INFORMATION],
};
