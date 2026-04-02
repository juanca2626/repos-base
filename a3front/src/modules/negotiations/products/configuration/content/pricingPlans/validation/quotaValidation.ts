import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';

export function validateQuotaStep(_state: any, _serviceType: ServiceType) {
  const errors: Record<string, string> = {};

  return {
    valid: Object.keys(errors).length === 0,
    errors,
  };
}
