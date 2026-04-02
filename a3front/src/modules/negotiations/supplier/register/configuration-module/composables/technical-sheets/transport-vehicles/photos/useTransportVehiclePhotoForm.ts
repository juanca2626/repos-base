import { computed, onMounted, reactive, ref, watch } from 'vue';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import { useFileUploadManager } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useFileUploadManager';
import type {
  TransportVehiclePhotoForm,
  VehiclePhotoFormProps,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { emit as emitBus } from '@/modules/negotiations/api/eventBus';
import { toSnakeCase } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import { createFormImages } from '@/modules/negotiations/supplier/register/configuration-module/constants/form-vehicle-photo';
import { useShowVehiclePhoto } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/photos/useShowVehiclePhoto';
import { isApproved } from '@/modules/negotiations/supplier/register/configuration-module/helpers/vehiclePhotoStatusHelper';
import { useProcessReview } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useProcessReview';
import { ReviewEntityEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/review-entity-enum';

export const useTransportVehiclePhotoForm = (
  emit: DrawerEmitTypeInterface,
  props: VehiclePhotoFormProps
) => {
  const resource = 'vehicle-photos';

  const { selectedVehiclePhoto } = props;

  const isEditMode = computed(() => selectedVehiclePhoto.id != null);

  const { handleCustomUpload, validateUploadPhoto, showNotificationError, handleRemove } =
    useFileUploadManager();

  const { showVehiclePhoto } = useShowVehiclePhoto();

  const isLoading = ref<boolean>(false);
  const visibleAlert = ref<boolean>(true);

  const initFormState: TransportVehiclePhotoForm = {
    id: null,
    supplierTransportVehicleId: null,
    images: createFormImages(),
    observations: null,
  };

  const formState = reactive<TransportVehiclePhotoForm>({ ...initFormState });

  const handleClose = (): void => {
    resetForm();
    initData();
    emit('update:showDrawerForm', false);
  };

  const {
    formRefObservation,
    commonFormRules: formRules,
    handleRejected,
    resetFields,
  } = useProcessReview({
    reviewEntity: ReviewEntityEnum.VEHICLE_PHOTO,
    formState,
    isLoading,
    handleClose,
  });

  const updateFileStatus = (imageKey: string) => {
    if (formState.images[imageKey].files.length > 0) {
      formState.images[imageKey].files[0].status = 'done'; //hide loading
    }
  };

  const handleUploadPhoto = (data: any, imageKey: string) => {
    const validate = validateUploadPhoto(data.file);

    if (!validate) {
      formState.images[imageKey].files = [];
      return;
    }

    handleCustomUpload(data, formState.images[imageKey].fileUploadData, () =>
      updateFileStatus(imageKey)
    );
  };

  const handleRemovePhoto = (imageKey: string) => {
    handleRemove(formState.images[imageKey].fileUploadData);
  };

  const initForm = (): void => {
    Object.assign(formState, structuredClone(initFormState));
  };

  const resetForm = () => {
    resetFields();
  };

  const initData = (): void => {
    initForm();
    visibleAlert.value = true;
  };

  const prepareFormData = () => {
    const formData = new FormData();

    Object.keys(formState.images).forEach((imageKey) => {
      if (
        formState.images[imageKey].fileUploadData.isFileUploaded &&
        formState.images[imageKey].fileUploadData.uploadedFile
      ) {
        formData.append(
          toSnakeCase(imageKey),
          formState.images[imageKey].fileUploadData.uploadedFile
        );
      }
    });

    formData.append(
      'supplier_transport_vehicle_id',
      selectedVehiclePhoto.supplierTransportVehicleId!
    );

    if (isEditMode.value) {
      formData.append('_method', 'PUT');
    }

    return formData;
  };

  const validateAllPhotosUploaded = (): boolean => {
    return Object.values(formState.images).every((image) =>
      Boolean(image.fileUploadData.isFileUploaded)
    );
  };

  const saveForm = async () => {
    try {
      isLoading.value = true;

      const { data } = await technicalSheetApi.post(
        `${resource}/${selectedVehiclePhoto.id ?? ''}`,
        prepareFormData(),
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );

      if (data.success) {
        handleSuccessResponse(data);
        emitBus('reloadTransportVehicleList');
        handleClose();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save vehicle photo:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleSubmit = async () => {
    try {
      if (!validateAllPhotosUploaded()) {
        showNotificationError('Debe subir todas las imágenes');
        return;
      }
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const handleCloseAlert = () => {
    visibleAlert.value = false;
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

  const disabledActionButton = computed(() => {
    return (
      isLoading.value ||
      Object.values(formState.images).some((image) => Boolean(image.fileUploadData.uploading))
    );
  });

  watch(
    () => props.showDrawerForm,
    (value) => {
      if (value && isEditMode.value) {
        loadVehiclePhotoData();
      }
    }
  );

  onMounted(async () => {
    initData();
  });

  return {
    formState,
    isLoading,
    visibleAlert,
    isEditMode,
    formRules,
    formRefObservation,
    disabledActionButton,
    saveForm,
    handleClose,
    handleSubmit,
    handleCloseAlert,
    handleUploadPhoto,
    validateUploadPhoto,
    handleRemovePhoto,
    handleRejected,
    isApproved,
  };
};
