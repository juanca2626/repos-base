import type { ProductSupplierTypeEnum } from '@/modules/negotiations/products/general/enums/product-supplier-type.enum';
export interface ProductSupplier {
  id: string;
  type: ProductSupplierTypeEnum;
  progress: number;
  status: boolean;
}
