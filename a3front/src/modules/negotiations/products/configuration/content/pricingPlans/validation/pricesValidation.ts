import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';

export function validatePricesStep(state: any, _serviceType: ServiceType) {
  const errors: Record<string, string> = {};

  if (!state.prices?.length) {
    errors.prices = 'Debe agregar al menos un precio';
  }

  return {
    valid: Object.keys(errors).length === 0,
    errors,
  };
}
