import type { ServiceType } from '../../types/index';
import { ServiceTypeEnum } from '../../enums/ServiceType.enum';
import type { PickupPoint } from '../../infrastructure/supportResource/dtos/pickupPoint.interface';
import { pickupPointService } from '../../infrastructure/supportResource/pickupPoint.service';

export async function fetchPickupPointUseCase(
  serviceType: ServiceType,
  types: string[] = ['TRAIN_STATION']
): Promise<PickupPoint[]> {
  if (serviceType !== ServiceTypeEnum.TRAIN) return [];

  const response = await pickupPointService(types);

  return response?.data ?? [];
}
