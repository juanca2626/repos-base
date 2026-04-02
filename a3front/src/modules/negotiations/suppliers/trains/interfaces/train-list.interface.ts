import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface SupplierTrain {
  id: number;
  businessName: string;
  status: SupplierStatusEnum;
  statusDescription: string;
  subClassificationName: string;
  locationName: string;
}

export interface SupplierTrainResponse {
  id: number;
  business_name: string;
  status: SupplierStatusEnum;
  status_description: string;
  sub_classification_name: string;
  location_name: string;
}
