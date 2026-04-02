import type { UploadFile } from 'ant-design-vue';
import type {
  VehiclePhotoTransform,
  FileUploadData,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

interface ImageUploadItem {
  title: string;
  files: UploadFile[];
  fileUploadData: FileUploadData;
}

export interface FormImages {
  [key: string]: ImageUploadItem;
  imgFrontWithPlate: ImageUploadItem;
  imgBackWithPlate: ImageUploadItem;
  imgSide: ImageUploadItem;
  imgInside: ImageUploadItem;
  imgSeatBelts: ImageUploadItem;
  imgSecurityKit: ImageUploadItem;
  imgExtinguisher: ImageUploadItem;
  imgSpareTires: ImageUploadItem;
}

export interface BaseTransportVehiclePhotoForm {
  id: string | null;
  supplierTransportVehicleId: string | null;
  images: FormImages;
  observations: string | null;
}

export interface TransportVehiclePhotoForm extends BaseTransportVehiclePhotoForm {}

export interface VehiclePhotoFormProps {
  showDrawerForm: boolean;
  selectedVehiclePhoto: VehiclePhotoTransform;
}
