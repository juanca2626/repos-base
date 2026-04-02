import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface SupplierRestaurant {
  id: number;
  businessName: string;
  status: SupplierStatusEnum;
  statusDescription: string;
  subClassificationName: string;
  locationName: string;
}

export interface SupplierRestaurantResponse {
  id: number;
  business_name: string;
  status: SupplierStatusEnum;
  status_description: string;
  sub_classification_name: string;
  location_name: string;
}
