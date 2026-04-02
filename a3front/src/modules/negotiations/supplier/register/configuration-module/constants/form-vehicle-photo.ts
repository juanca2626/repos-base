import type {
  FileUploadData,
  FormImages,
} from 'src/modules/negotiations/supplier/register/configuration-module/interfaces';

export const vehiclePhotoFieldTitles = {
  imgFrontWithPlate: 'Delantera con placa',
  imgBackWithPlate: 'Trasera con placa',
  imgSide: 'Costado del vehículo',
  imgInside: 'Interior del vehículo *',
  imgSeatBelts: 'Cinturones',
  imgSecurityKit: 'Kit de seguridad **',
  imgExtinguisher: 'Extintor',
  imgSpareTires: 'Llantas de repuesto ***',
} as const;

const fileUploadData: FileUploadData = {
  uploading: false,
  isFileUploaded: false,
  progress: 0,
  uploadedFile: null,
  uploadedSize: 0,
  fileSize: 0,
  uploadSpeed: 0,
  startTime: 0,
  uploadInterval: null,
};

export function createFormImages(): FormImages {
  const fields = {} as FormImages;

  for (const key in vehiclePhotoFieldTitles) {
    fields[key as keyof FormImages] = {
      title: vehiclePhotoFieldTitles[key as keyof typeof vehiclePhotoFieldTitles],
      files: [],
      fileUploadData: { ...fileUploadData },
    };
  }

  return fields;
}
