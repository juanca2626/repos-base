import { computed, onMounted, reactive, ref, watch } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import { filterOption } from '@/modules/negotiations/supplier/register//helpers/supplierFormHelper';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import type {
  TransportDocumentFormProps,
  TransportDocumentForm,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type { TypeVehicleDocumentEnum as TypeVehicleDocumentEnumType } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';
import { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';
import { isApproved as isApprovedVehicle } from '@/modules/negotiations/supplier/register/configuration-module/helpers/vehicleDocumentStatusHelper';
import { isApproved as isApprovedDriver } from '@/modules/negotiations/supplier/register/configuration-module/helpers/driverDocumentStatusHelper';
import { useProcessReview } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useProcessReview';
import { useDocumentFormManager } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useDocumentFormManager';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import type {
  SelectedDocumentStatus,
  TransportDocumentResponseType,
} from '@/modules/negotiations/supplier/register/configuration-module/types';
import { useDocumentFormUtils } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useDocumentFormUtils';
import { DriverDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/driver-document-status.enum';
import { VehicleDocumentStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-document-status.enum';

export const useTransportDocumentForm = (
  emit: DrawerEmitTypeInterface,
  props: TransportDocumentFormProps<TypeTechnicalSheetEnum>
) => {
  const { typeTechnicalSheet } = props;

  type TransportDocumentResponse = TransportDocumentResponseType<typeof typeTechnicalSheet>;

  const isEditMode = computed(() => props.selectedDocument.id != null);
  const isLoadingForm = ref<boolean>(false);
  const formRefTransportDocument = ref<FormInstance | null>(null);

  const initFormState: TransportDocumentForm = {
    id: null,
    expirationDate: null,
    file: null,
    parentId: null,
    typeDocumentId: null,
    notApplicable: false,
    observations: null,
  };

  const formState = reactive<TransportDocumentForm>({ ...initFormState });

  const initForm = (): void => {
    Object.assign(formState, { ...initFormState });
  };

  const handleClose = (): void => {
    resetForm();
    initData();
    emit('update:showDrawerForm', false);
  };

  const {
    isTransportVehicle,
    resource,
    reviewEntity,
    isLoading: isLoadingUtil,
    handleDownload,
    reloadList,
    handleShow,
  } = useDocumentFormUtils(typeTechnicalSheet);

  const {
    formRefObservation,
    commonFormRules: formRulesObservation,
    handleRejected,
    resetFields: resetFieldsObservation,
  } = useProcessReview({
    reviewEntity,
    formState,
    isLoading: isLoadingForm,
    handleClose,
  });

  const {
    fileUploadData,
    formRules,
    handleCustomUpload,
    clearFileUploadData,
    validateUploadFile,
    disabledExpirationDate,
    handleRemoveFile,
  } = useDocumentFormManager(formState);

  const initData = (): void => {
    initForm();
    clearFileUploadData(fileUploadData);
  };

  const resetForm = () => {
    formRefTransportDocument.value?.resetFields();
    resetFieldsObservation();
  };

  const parentKey = isTransportVehicle.value
    ? 'supplier_transport_vehicle_id'
    : 'supplier_vehicle_driver_id';

  const typeDocumentKey = isTransportVehicle.value
    ? 'type_vehicle_document_id'
    : 'type_vehicle_driver_document_id';

  const prepareFormData = () => {
    const formData = new FormData();

    formData.append('expiration_date', formState.expirationDate!);

    if (isEditMode.value) {
      formData.append('_method', 'PUT');
    }

    if (formState.file) {
      formData.append('file', formState.file);
    }

    formData.append(parentKey, props.selectedDocument.parentId ?? '');
    formData.append(typeDocumentKey, props.selectedDocument.typeDocumentId?.toString() ?? '');

    return formData;
  };

  const saveForm = async () => {
    try {
      isLoadingForm.value = true;

      const { data } = await technicalSheetApi.post(
        `${resource}/${props.selectedDocument.id ?? ''}`,
        prepareFormData(),
        {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        }
      );

      if (data.success) {
        handleSuccessResponse(data);
        reloadList();
        handleClose();
      }
    } catch (error: any) {
      handleError(error);
      console.error(`Error save ${resource}:`, error);
    } finally {
      isLoadingForm.value = false;
    }
  };

  const updateNotApplicableStatus = async () => {
    try {
      isLoadingForm.value = true;

      const { data } = await technicalSheetApi.patch(
        `supplier-transport-vehicles/not-applicable-status/${props.selectedDocument.parentId}`,
        {
          type_vehicle_document_id: props.selectedDocument.typeDocumentId,
        }
      );

      if (data.success) {
        handleSuccessResponse(data);
        reloadList();
        handleClose();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error update status:', error);
    } finally {
      isLoadingForm.value = false;
    }
  };

  const handleSubmit = async () => {
    try {
      if (isTransportVehicle.value && formState.notApplicable) {
        await updateNotApplicableStatus();
      } else {
        await formRefTransportDocument.value?.validate();
        await saveForm();
      }
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const handleShowDocument = async () => {
    await handleShow(props.selectedDocument.id ?? '', setDataToForm);
  };

  const setDataToForm = (data: TransportDocumentResponse) => {
    formState.id = data.id;
    formState.expirationDate = data.expiration_date;

    formState.parentId = (data[parentKey as keyof TransportDocumentResponse] as string) || null;

    const typeDocumentValue = data[typeDocumentKey as keyof TransportDocumentResponse];
    formState.typeDocumentId = typeof typeDocumentValue === 'number' ? typeDocumentValue : null;

    fileUploadData.isFileUploaded = true;
    fileUploadData.fileSize = data.document.size;
    fileUploadData.filename = data.document.filename;
    fileUploadData.documentId = data.id;
  };

  const handleDownloadDocument = async () => {
    await handleDownload(fileUploadData.filename ?? '', props.selectedDocument.id);
  };

  const showNotApplicable = computed(() => {
    if (!isTransportVehicle.value) return false;

    const notApplicableTypes = [
      TypeVehicleDocumentEnum.INSPECTION_CERTIFICATE,
      TypeVehicleDocumentEnum.CIRCULATION_CARD,
    ];

    return notApplicableTypes.includes(
      props.selectedDocument.typeDocumentId as TypeVehicleDocumentEnumType
    );
  });

  const isLoading = computed(() => {
    return isLoadingForm.value || isLoadingUtil.value;
  });

  const isApproved = (status: SelectedDocumentStatus) => {
    if (isTransportVehicle.value) {
      return isApprovedVehicle(status as VehicleDocumentStatusEnum);
    }

    return isApprovedDriver(status as DriverDocumentStatusEnum);
  };

  const disabledActionButton = computed(() => {
    return isLoading.value || fileUploadData.uploading || false;
  });

  watch(
    () => props.showDrawerForm,
    (value) => {
      if (value && isEditMode.value) {
        handleShowDocument();
      }
    }
  );

  onMounted(async () => {
    initData();
  });

  return {
    formRefTransportDocument,
    formState,
    isLoading,
    formRules,
    formRulesObservation,
    formRefObservation,
    fileUploadData,
    showNotApplicable,
    isEditMode,
    isTransportVehicle,
    disabledActionButton,
    filterOption,
    saveForm,
    handleClose,
    handleSubmit,
    handleCustomUpload,
    handleRemoveFile,
    disabledExpirationDate,
    validateUploadFile,
    handleDownloadDocument,
    handleRejected,
    isApproved,
  };
};
