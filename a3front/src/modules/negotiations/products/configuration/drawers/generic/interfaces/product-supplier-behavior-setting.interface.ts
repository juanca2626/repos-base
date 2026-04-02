import type { ProductSupplierBehaviorMatrixItem } from './product-supplier-behavior-matrix-item.interface';

export interface ProductSupplierBehaviorSetting {
  applyGeneralBehavior: boolean;
  matrix: ProductSupplierBehaviorMatrixItem[];
}
