import type { BaseTransportVehiclePhotoForm } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';

export interface TransportVehiclePhotoReviewForm extends BaseTransportVehiclePhotoForm {
  status: VehiclePhotoStatusEnum | null;
}
