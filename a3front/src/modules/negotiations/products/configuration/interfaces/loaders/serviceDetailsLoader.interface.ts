import type {
  ServiceDetailsResponse,
  MultiDaysServiceDetailsResponse,
} from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

export interface ServiceDetailsLoader {
  loadServiceDetails(
    productSupplierId: string
  ): Promise<ServiceDetailsResponse[] | MultiDaysServiceDetailsResponse[] | null>;
}
