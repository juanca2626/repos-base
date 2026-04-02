import type { ServiceType } from '@/modules/negotiations/products/general/interfaces/resources';

export interface ProductResponse {
  id: string;
  serviceType: ServiceType;
  code: string;
  name: string;
  status: boolean;
  createdAt: string;
  updatedAt: string;
}
