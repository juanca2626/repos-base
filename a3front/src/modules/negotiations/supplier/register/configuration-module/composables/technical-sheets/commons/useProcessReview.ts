import type { Rule } from 'ant-design-vue/es/form';
import { ref } from 'vue';
import type { ProcessReviewInputs } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { emit as emitBus } from '@/modules/negotiations/api/eventBus';
import ObservationFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/ObservationFormComponent.vue';
import { isApproved as isApprovedDocument } from '@/modules/negotiations/supplier/register/configuration-module/helpers/vehicleDocumentStatusHelper';
import { isApproved as isApprovedPhoto } from '@/modules/negotiations/supplier/register/configuration-module/helpers/vehiclePhotoStatusHelper';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';
import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { ReviewEntityEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/review-entity-enum';

export const useProcessReview = (inputs: ProcessReviewInputs) => {
  const { reviewEntity, formState, isLoading, handleClose } = inputs;

  const reviewEntityMap = {
    [ReviewEntityEnum.VEHICLE_DOCUMENT]: {
      statusEnum: VehicleDocumentStatusEnum,
      resource: 'vehicle-documents',
    },
    [ReviewEntityEnum.VEHICLE_PHOTO]: {
      statusEnum: VehiclePhotoStatusEnum,
      resource: 'vehicle-photos',
    },
    [ReviewEntityEnum.DRIVER_DOCUMENT]: {
      statusEnum: DriverDocumentStatusEnum,
      resource: 'vehicle-driver-documents',
    },
  };

  const formRefObservation = ref<InstanceType<typeof ObservationFormComponent> | null>(null);
  const statusEnum = reviewEntityMap[reviewEntity].statusEnum;
  const resource = reviewEntityMap[reviewEntity].resource;

  type TypeofStatusEnum = typeof statusEnum;
  type StatusEnumType = TypeofStatusEnum[keyof TypeofStatusEnum];

  const commonFormRules: Record<string, Rule[]> = {
    observations: [
      {
        required: true,
        validator: (_: unknown, value: string) => {
          if (!value) {
            return Promise.reject('Debe ingresar las observaciones');
          }

          return Promise.resolve();
        },
        trigger: 'change',
      },
    ],
  };

  const handleApproved = async () => {
    handleReview(statusEnum.APPROVED);
  };

  const handleRejected = async () => {
    handleReview(statusEnum.REJECTED);
  };

  const handleReview = async (status: StatusEnumType) => {
    try {
      if (status === statusEnum.REJECTED) {
        await formRefObservation.value?.validate();
      }
      await submitReview(status);
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const resetFields = () => {
    formRefObservation.value?.resetFields();
  };

  const buildRequest = (status: StatusEnumType) => ({
    id: formState.id,
    [reviewEntity === ReviewEntityEnum.VEHICLE_PHOTO ? 'observation' : 'observations']:
      formState.observations,
    status,
  });

  const reloadList = () => {
    const event =
      reviewEntity === ReviewEntityEnum.DRIVER_DOCUMENT
        ? 'reloadTransportDriverList'
        : 'reloadTransportVehicleList';

    emitBus(event);
  };

  const submitReview = async (status: StatusEnumType) => {
    try {
      isLoading.value = true;

      const { data } = await technicalSheetApi.post(`${resource}/review`, buildRequest(status));

      handleSuccessResponse(data);
      reloadList();
      handleClose();
    } catch (error: any) {
      handleError(error);
      console.error(`Error save ${resource} review:`, error);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    commonFormRules,
    formRefObservation,
    isApprovedDocument,
    isApprovedPhoto,
    handleRejected,
    handleApproved,
    handleReview,
    resetFields,
  };
};
