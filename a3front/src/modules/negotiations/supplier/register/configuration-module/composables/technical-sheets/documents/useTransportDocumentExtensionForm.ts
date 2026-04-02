import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import type {
  DocumentExtensionFormProps,
  DocumentExtensionInfo,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useDocumentExtensionStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useDocumentExtensionStore';
import type {
  DocumentExtensionFormResponse,
  DocumentExtensionFormRef,
} from '@/modules/negotiations/supplier/register/configuration-module/types';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { emit as emitBus } from '@/modules/negotiations/api/eventBus';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import { extensionFormDataMap } from '@/modules/negotiations/supplier/register/configuration-module/constants/extension-form-data-map';

export const useTransportDocumentExtensionForm = (
  emit: DrawerEmitTypeInterface,
  props: DocumentExtensionFormProps
) => {
  const extensionFormData = extensionFormDataMap[props.typeTechnicalSheet];

  const parentKey = extensionFormData.parentKey;

  const typeDocumentKey = extensionFormData.typeDocumentKey;

  const resource = extensionFormData.resource;

  const eventReloadList = extensionFormData.event;

  const formRefsDocumentExtension = ref<DocumentExtensionFormRef[]>([]);

  const documentExtensionStore = useDocumentExtensionStore();

  const { formState, addExtension, loadResources, resetFormState, setIsUpdate } =
    documentExtensionStore;

  const { isUpdate } = storeToRefs(documentExtensionStore);

  const isLoading = ref<boolean>(false);

  const showActiveExtensionNotify = ref<boolean>(false);

  const isTransportVehicle = computed(() => {
    return props.typeTechnicalSheet === TypeTechnicalSheetEnum.TRANSPORT_VEHICLE;
  });

  const documentExtensionInfo = reactive<DocumentExtensionInfo>({});

  const documentExtensionIds = ref<string[]>([]);

  const handleClose = (): void => {
    resetForm();
    initData();
    emit('update:showDrawerForm', false);
  };

  const initData = (): void => {
    resetFormState();
    setIsUpdate(false);
  };

  const resetForm = () => {
    formRefsDocumentExtension.value.map((form: DocumentExtensionFormRef) => form?.resetFields());
  };

  const validateAllForms = async () => {
    const validations = formRefsDocumentExtension.value.map((form: DocumentExtensionFormRef) =>
      form?.validateFields()
    );

    const results = await Promise.all(validations);

    return results.every((valid) => valid);
  };

  const buildRequest = () => {
    const request: Record<string, any> = {
      extensions: [],
    };

    request[parentKey] = props.parentId;
    request.extensions = formState.extensions.map((row) => {
      const extension: Record<string, any> = {
        id: row.id ?? undefined,
        date_from: row.extensionDateRange[0],
        date_to: row.extensionDateRange[1],
        reason: row.reason,
        delete: row.delete,
      };

      extension[typeDocumentKey] = row.typeDocumentId;

      return extension;
    });

    return request;
  };

  const saveForm = async () => {
    try {
      isLoading.value = true;
      let response;

      if (isUpdate.value) {
        response = await technicalSheetApi.put(`${resource}/bulk`, buildRequest());
      } else {
        response = await technicalSheetApi.post(`${resource}/bulk`, buildRequest());
      }

      const data = response.data;

      if (data.success) {
        handleSuccessResponse(data);
        emitBus(eventReloadList);
        if (!isUpdate.value) {
          handleExtensionSuccessNotify(data.data);
        }

        handleClose();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save document extension:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleSubmit = async () => {
    try {
      const isValid = await validateAllForms();

      if (isValid) {
        await saveForm();
      }
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const setDocumentExtensionInfo = () => {
    Object.assign(documentExtensionInfo, {
      driverFullName: !isTransportVehicle.value
        ? props.documentExtensionInfo.driverFullName
        : undefined,
      licensePlate: isTransportVehicle.value ? props.documentExtensionInfo.licensePlate : undefined,
      typeUnitCode: isTransportVehicle.value ? props.documentExtensionInfo.typeUnitCode : undefined,
    });
  };

  const handleExtensionSuccessNotify = (data: DocumentExtensionFormResponse) => {
    setDocumentExtensionInfo();
    documentExtensionIds.value = data.map((row) => row.id);
    showActiveExtensionNotify.value = true;
  };

  const bulkShowExtension = async () => {
    try {
      isLoading.value = true;

      const { data } = await technicalSheetApi.get<ApiResponse<DocumentExtensionFormResponse>>(
        `${resource}/bulk/${props.parentId}`
      );

      setDataToForm(data.data);
    } catch (error: any) {
      handleError(error);
      console.error('Error bulk show extension:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const setDataToForm = async (data: DocumentExtensionFormResponse) => {
    if (data.length === 0) return;

    setIsUpdate(true);

    formState.extensions = [];

    data.forEach((row) => {
      formState.extensions.push({
        id: row.id,
        extensionDateRange: [row.date_from, row.date_to],
        typeDocumentId:
          'type_vehicle_document_id' in row
            ? row.type_vehicle_document_id
            : row.type_vehicle_driver_document_id,
        reason: row.reason,
      });
    });
  };

  watch(
    () => props.showDrawerForm,
    (value) => {
      if (value) {
        bulkShowExtension();
      }
    }
  );

  onMounted(async () => {
    await loadResources(props.typeTechnicalSheet);
    initData();
  });

  return {
    formRefsDocumentExtension,
    formState,
    isLoading,
    showActiveExtensionNotify,
    documentExtensionIds,
    isUpdate,
    documentExtensionInfo,
    handleClose,
    handleSubmit,
    addExtension,
  };
};
