import type {
  ImageKey,
  BaseTransportVehiclePhotoForm,
  VehiclePhotoDetailResponse,
  VehiclePhotoFieldCollection,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useVehiclePhotoUtils } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/photos/useVehiclePhotoUtils';
import {
  sleep,
  toSnakeCase,
} from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

export const useShowVehiclePhoto = () => {
  const { fetchVehiclePhoto, fetchPhotoFromUrl } = useVehiclePhotoUtils();

  const showVehiclePhoto = async (
    formState: BaseTransportVehiclePhotoForm,
    vehiclePhotoId?: string
  ) => {
    const data = await fetchVehiclePhoto(vehiclePhotoId ?? '');
    await setDataToForm(formState, data);
    await sleep(1000);
  };

  const setDataToForm = async (
    formState: BaseTransportVehiclePhotoForm,
    data: VehiclePhotoDetailResponse
  ) => {
    formState.id = data.id;
    formState.supplierTransportVehicleId = data.supplier_transport_vehicle_id;

    const imageKeys = generateImageKeys(formState);

    const validImages = filterValidImages(imageKeys, data);

    const fetchedImages = await fetchImageData(validImages, data);

    updateImageFormState(formState, fetchedImages);
  };

  const generateImageKeys = (formState: BaseTransportVehiclePhotoForm) => {
    return Object.keys(formState.images).map((imageKey) => ({
      mainKey: imageKey,
      snakeKey: toSnakeCase(imageKey),
    }));
  };

  const filterValidImages = (imageKeys: ImageKey[], data: VehiclePhotoDetailResponse) => {
    return imageKeys.filter(({ snakeKey }) => data[snakeKey as keyof VehiclePhotoFieldCollection]);
  };

  const fetchImageData = async (validImages: ImageKey[], data: VehiclePhotoDetailResponse) => {
    return await Promise.all(
      validImages.map(async ({ mainKey, snakeKey }) => {
        const imageData = data[snakeKey as keyof VehiclePhotoFieldCollection] ?? null;
        const imageUrl = imageData ? await fetchPhotoFromUrl(imageData.name) : undefined;

        return {
          imageKey: mainKey,
          imageName: imageData?.name,
          imageUrl,
        };
      })
    );
  };

  const updateImageFormState = (
    formState: BaseTransportVehiclePhotoForm,
    fetchedImages: { imageKey: string; imageName?: string; imageUrl?: string }[]
  ) => {
    fetchedImages.forEach(({ imageKey, imageName, imageUrl }) => {
      if (imageName) {
        formState.images[imageKey].files = [
          {
            uid: imageName,
            name: imageName,
            status: 'done',
            url: imageUrl,
          },
        ];

        formState.images[imageKey].fileUploadData.isFileUploaded = true;
      }
    });
  };

  return {
    showVehiclePhoto,
  };
};
