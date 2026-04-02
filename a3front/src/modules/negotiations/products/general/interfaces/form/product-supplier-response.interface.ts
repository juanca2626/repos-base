import type {
  ProductSupplier,
  SupplierApiBase,
} from '@/modules/negotiations/products/general/interfaces/form';

export interface Supplier extends SupplierApiBase {
  id: string;
}

export interface ProductSupplierResponse extends ProductSupplier {
  supplier: Supplier;
}
