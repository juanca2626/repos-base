import { serviceTypeRegistry } from './serviceType.registry';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';

export function resolveConfigurationStore(serviceType: ServiceType) {
  const entry = serviceTypeRegistry[serviceType];

  if (!entry) {
    throw new Error(`Unsupported service type ${serviceType}`);
  }

  return entry.configurationStore();
}
