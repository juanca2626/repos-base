import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type { SelectOption } from '@/modules/negotiations/supplier/interfaces';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import type {
  DocumentExtensionForm,
  DocumentExtensionFormStore,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useDocumentExtensionStore = defineStore('documentExtensionStore', () => {
  const typeDocuments = ref<SelectOption[]>([]);

  const extensionFormData: DocumentExtensionForm = {
    id: null,
    extensionDateRange: [],
    typeDocumentId: null,
    reason: null,
  };

  const initFormData: DocumentExtensionFormStore = {
    applyDateAll: false,
    extensions: [extensionFormData],
  };

  const formState = reactive<DocumentExtensionFormStore>(structuredClone(initFormData));

  const isUpdate = ref<boolean>(false);

  const resetFormState = (): void => {
    Object.assign(formState, structuredClone(initFormData));
  };

  const setIsUpdate = (value: boolean): void => {
    isUpdate.value = value;
  };

  const addExtension = () => {
    formState.extensions.push({
      ...extensionFormData,
    });
  };

  const deleteExtension = (index: number) => {
    if (formState.extensions[index].id) {
      formState.extensions[index].delete = true;
    } else {
      formState.extensions.splice(index, 1);
    }
  };

  const applyDateAllExtensions = () => {
    if (formState.applyDateAll) {
      formState.extensions.forEach((row: any, index: number) => {
        if (index > 0) {
          row.extensionDateRange = [...formState.extensions[0].extensionDateRange];
        }
      });
    }
  };

  const fetchTypeVehicleDocuments = async () => {
    const { data } = await technicalSheetApi.get('type-vehicle-documents');

    typeDocuments.value = mapItemsToOptions(data.data);
  };

  const fetchTypeVehicleDriverDocuments = async () => {
    const { data } = await technicalSheetApi.get('supplier-vehicle-drivers/resources', {
      params: {
        keys: ['type_vehicle_driver_documents'],
      },
    });

    typeDocuments.value = mapItemsToOptions(data.data.type_vehicle_driver_documents);
  };

  const isTransportVehicle = (typeTechnicalSheet: TypeTechnicalSheetEnum) => {
    return typeTechnicalSheet === TypeTechnicalSheetEnum.TRANSPORT_VEHICLE;
  };

  const loadResources = async (typeTechnicalSheet: TypeTechnicalSheetEnum) => {
    if (isTransportVehicle(typeTechnicalSheet)) {
      await fetchTypeVehicleDocuments();
    } else {
      await fetchTypeVehicleDriverDocuments();
    }
  };

  return {
    formState,
    typeDocuments,
    isUpdate,
    addExtension,
    deleteExtension,
    loadResources,
    resetFormState,
    applyDateAllExtensions,
    setIsUpdate,
  };
});
