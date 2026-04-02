import type { ServiceType, SupportResourceKey } from '../../types/index';
import { SUPPORT_RESOURCE_KEYS } from '../../domain/supportResource/supportResourceKeys';
import { supportResourceService } from '../../infrastructure/supportResource/supportResource.service';

export interface FetchSupportResourceParams {
  serviceType: ServiceType;
  keys?: SupportResourceKey[];
}

export const fetchSupportResourceUseCase = async ({
  serviceType,
  keys,
}: FetchSupportResourceParams) => {
  const resourceKeys = keys ?? SUPPORT_RESOURCE_KEYS[serviceType];

  const response = await supportResourceService(resourceKeys);

  return response?.data ?? {};
};
