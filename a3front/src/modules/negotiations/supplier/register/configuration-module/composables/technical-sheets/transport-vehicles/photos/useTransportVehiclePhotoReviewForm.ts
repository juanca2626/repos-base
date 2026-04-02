import { onMounted, reactive, ref, watch } from 'vue';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import type {
  TransportVehiclePhotoReviewForm,
  VehiclePhotoFormProps,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { createFormImages } from '@/modules/negotiations/supplier/register/configuration-module/constants/form-vehicle-photo';
import { useShowVehiclePhoto } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/photos/useShowVehiclePhoto';
import { useProcessReview } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useProcessReview';
import { ReviewEntityEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/review-entity-enum';

export const useTransportVehiclePhotoReviewForm = (
  emit: DrawerEmitTypeInterface,
  props: VehiclePhotoFormProps
) => {
  const { showVehiclePhoto } = useShowVehiclePhoto();

  const isLoading = ref<boolean>(false);

  const initFormState: TransportVehiclePhotoReviewForm = {
    id: null,
    observations: null,
    status: null,
    supplierTransportVehicleId: null,
    images: createFormImages(),
  };

  const formState = reactive<TransportVehiclePhotoReviewForm>({ ...initFormState });

  const handleClose = (): void => {
    resetForm();
    initForm();
    emit('update:showDrawerForm', false);
  };

  const {
    formRefObservation,
    commonFormRules: formRules,
    handleRejected,
    handleApproved,
    resetFields,
  } = useProcessReview({
    reviewEntity: ReviewEntityEnum.VEHICLE_PHOTO,
    formState,
    isLoading,
    handleClose,
  });

  const initForm = (): void => {
    Object.assign(formState, structuredClone(initFormState));
  };

  const resetForm = () => {
    resetFields();
  };

  const loadVehiclePhotoData = async () => {
    try {
      isLoading.value = true;

      await showVehiclePhoto(formState, props.selectedVehiclePhoto.id);
    } catch (error: any) {
      handleError(error);
      console.error('Error show vehicle photo:', error);
    } finally {
      isLoading.value = false;
    }
  };

  watch(
    () => props.showDrawerForm,
    (value) => {
      if (value) {
        loadVehiclePhotoData();
      }
    }
  );

  onMounted(async () => {
    initForm();
  });

  return {
    formState,
    isLoading,
    formRules,
    formRefObservation,
    handleClose,
    handleRejected,
    handleApproved,
  };
};
