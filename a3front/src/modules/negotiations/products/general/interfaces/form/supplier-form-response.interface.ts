import type { SupplierApiBase } from '@/modules/negotiations/products/general/interfaces/form';

export interface SupplierFormResponse extends SupplierApiBase {
  id: string;
  createdAt: string;
  updatedAt: string;
}
