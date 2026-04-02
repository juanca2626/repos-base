import { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';

export const supplierStatusDescriptions: Record<SupplierStatusEnum, string> = {
  [SupplierStatusEnum.ACTIVE]: 'Activo',
  [SupplierStatusEnum.INACTIVE]: 'Inactivo',
  [SupplierStatusEnum.SUSPENDED]: 'Suspendido',
  [SupplierStatusEnum.IN_REVIEW]: 'En evaluación',
};

export const generalInformationSupplierStatus: Partial<Record<SupplierStatusEnum, string>> = {
  [SupplierStatusEnum.ACTIVE]: 'Activo',
  [SupplierStatusEnum.INACTIVE]: 'Inactivo',
  [SupplierStatusEnum.SUSPENDED]: 'Suspendido',
  [SupplierStatusEnum.IN_REVIEW]: 'En evaluación',
};

export const supplierStatusOptions: SelectOption[] = Object.entries(supplierStatusDescriptions).map(
  ([value, label]) => ({
    label,
    value: value as SupplierStatusEnum,
  })
);
