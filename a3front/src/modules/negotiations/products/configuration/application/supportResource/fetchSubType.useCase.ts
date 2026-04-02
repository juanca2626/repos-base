import { serviceSubTypeService } from '../../infrastructure/supportResource/serviceSubType.service';
import type { ServiceSubType } from '../../infrastructure/supportResource/dtos/serviceSubType.interface';
import type { ServiceType } from '../../types/index';
import { ServiceTypeEnum } from '../../enums/ServiceType.enum';

export interface FetchSubTypeParams {
  serviceTypeId: string;
  serviceType: ServiceType;
}

export const fetchSubTypeUseCase = async ({
  serviceTypeId,
  serviceType,
}: FetchSubTypeParams): Promise<ServiceSubType[]> => {
  if (serviceType === ServiceTypeEnum.TRAIN) return [];

  const response = await serviceSubTypeService(serviceTypeId);
  return response?.data ?? [];
};
