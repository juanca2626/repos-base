import type { ProductSupplierBehaviorMode } from './product-supplier-behavior-mode.type';

export interface ProductSupplierBehaviorMatrixItem {
  operatingLocationKey: string;
  supplierCategoryCode: string;
  mode: ProductSupplierBehaviorMode;
}
