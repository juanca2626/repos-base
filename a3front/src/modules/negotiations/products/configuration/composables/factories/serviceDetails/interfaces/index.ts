import type { ServiceType } from '@/modules/negotiations/products/configuration/types';
import type {
  ServiceDetailsResponse,
  MultiDaysServiceDetailsResponse,
} from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

export interface ServiceDetailLoader {
  getServiceDetailBySectionKeyCode(
    serviceType: ServiceType,
    key: string,
    code: string
  ): ServiceDetailsResponse | MultiDaysServiceDetailsResponse | null;
}
