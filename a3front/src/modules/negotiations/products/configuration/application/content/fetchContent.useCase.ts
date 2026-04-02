import { resolveContentService } from './contentServiceResolver';
import { resolveContentStrategy } from '@/modules/negotiations/products/configuration/domain/content/contentStrategyResolver';

import type { ServiceType, Role } from '../../types/index';
import type { GenericContentModel } from '@/modules/negotiations/products/configuration/domain/content/models/genericContent.model';
import type { PackageContentModel } from '@/modules/negotiations/products/configuration/domain/content/models/packageContent.model';
import type { TrainContentModel } from '@/modules/negotiations/products/configuration/domain/content/models/trainContent.model';

export async function fetchContentUseCase(
  type: ServiceType,
  role: Role,
  productSupplierId: string,
  serviceDetailId: string
): Promise<GenericContentModel | PackageContentModel | TrainContentModel> {
  const service = resolveContentService(type, role);
  const strategy = resolveContentStrategy(type, role);

  const response = await service(productSupplierId, serviceDetailId);
  const data = response?.data ?? {};

  return strategy(data);
}
