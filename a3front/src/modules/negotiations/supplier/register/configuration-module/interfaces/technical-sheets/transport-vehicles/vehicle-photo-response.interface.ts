import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';

export interface ImageKey {
  mainKey: string;
  snakeKey: string;
}

export interface ImageField {
  name: string;
  url: string;
}

export interface VehiclePhotoFieldCollection {
  img_front_with_plate: ImageField | null;
  img_back_with_plate: ImageField | null;
  img_side: ImageField | null;
  img_inside: ImageField | null;
  img_seat_belts: ImageField | null;
  img_security_kit: ImageField | null;
  img_extinguisher: ImageField | null;
  img_spare_tires: ImageField | null;
}

export interface VehiclePhotoDetailResponse extends VehiclePhotoFieldCollection {
  id: string;
  supplier_transport_vehicle_id: string;
  status: VehicleDocumentStatusEnum;
}
