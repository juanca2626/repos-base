import type { ConfigurationStatusEnum } from '@/modules/negotiations/products/general/enums/configuration-status.enum';
import type { ProductSupplierTypeEnum } from '@/modules/negotiations/products/general/enums/product-supplier-type.enum';

export interface ProductSupplierBatchRequest {
  type: ProductSupplierTypeEnum;
  productId: string;
  productCode: string;
  productSuppliers: ProductSupplierRequest[];
}

export interface ProductSupplierRequest {
  id?: string | null;
  supplierId: string | null;
  supplierCode: string;
  progress: number;
  configurationStatus: ConfigurationStatusEnum;
  status: boolean;
}
