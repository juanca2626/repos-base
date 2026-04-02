import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface SupplierApiBase {
  originalId: number;
  code: string;
  name: string;
  providerTypeCode: string;
  providerTypeName: string;
  country: string;
  state: string;
  city: string | null;
  status: SupplierStatusEnum;
}

export interface SupplierFormRequest extends SupplierApiBase {}

export interface SupplierFormBatchRequest {
  items: SupplierApiBase[];
}
