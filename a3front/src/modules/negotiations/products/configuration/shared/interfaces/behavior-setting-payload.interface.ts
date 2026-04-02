import type { ProductSupplierBehaviorMode } from '@/modules/negotiations/products/configuration/interfaces';

export interface BehaviorSettingPayload {
  supplierCategoryCode: string;
  mode: ProductSupplierBehaviorMode;
}
