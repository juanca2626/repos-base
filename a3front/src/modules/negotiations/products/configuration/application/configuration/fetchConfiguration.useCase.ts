import { resolveConfigurationService } from '@/modules/negotiations/products/configuration/application/configuration/resolvers/fetchConfigurationServiceResolver';
import { resolveConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/resolvers/fetchConfigurationStrategyResolver';
import type { ServiceType } from '../../types/index';
import type { FetchConfigurationModel } from '@/modules/negotiations/products/configuration/domain/configuration/models/fetchConfiguration.model';

export async function fetchConfigurationUseCase(
  productSupplierId: string,
  type: ServiceType
): Promise<FetchConfigurationModel> {
  const service = resolveConfigurationService(type);
  const strategy = resolveConfigurationStrategy(type);

  const response = await service(productSupplierId);

  return strategy(response.data);
}
