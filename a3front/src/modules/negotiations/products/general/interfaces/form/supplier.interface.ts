import type {
  Country,
  SupplierClassification,
  State,
  City,
} from '@/modules/negotiations/products/general/interfaces/resources';
import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface SupplierResponse {
  id: number;
  supplier_sub_classification_id: number;
  code: string;
  business_name: string;
  status: SupplierStatusEnum;
  supplier_classification: SupplierClassification;
  country: Country;
  state: State;
  city: City | null;
}
