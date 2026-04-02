import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';
import { resolveConfigurationStore } from './configurationStore.resolver';

export function resolveServiceDetailId(
  serviceType: ServiceType,
  currentKey: string,
  currentCode: string
): string | null {
  const store = resolveConfigurationStore(serviceType);

  return store.getServiceDetailId(currentKey, currentCode);
}
