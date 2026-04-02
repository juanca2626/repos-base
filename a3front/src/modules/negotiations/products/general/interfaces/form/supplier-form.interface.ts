import type { SupplierClassification } from '@/modules/negotiations/products/general/interfaces/resources';
import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';
import type { ProductSupplier } from '@/modules/negotiations/products/general/interfaces/form';

export interface SupplierForm {
  supplierId: string | null; // ID de coleccion espejo (product_ms)
  supplierOriginalId: number; // ID original que viene de list api (supplier_ms)
  productSupplierId: string | null; // ID del product supplier
  code: string;
  name: string;
  status: SupplierStatusEnum;
  supplierClassification: Omit<SupplierClassification, 'id'>;
  countryName: string;
  stateName: string;
  cityName: string | null;
  isLoading: boolean;
  productSupplier: ProductSupplier | null;
}
