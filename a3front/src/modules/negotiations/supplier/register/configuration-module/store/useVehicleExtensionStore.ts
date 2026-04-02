import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import type { SelectOption } from '@/modules/negotiations/supplier/interfaces';
import type {
  ExtensionForm,
  VehicleExtensionFormStore,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export const useVehicleExtensionStore = defineStore('vehicleExtensionStore', () => {
  const typeVehicleDocuments = ref<SelectOption[]>([]);

  const extensionFormData: ExtensionForm = {
    id: null,
    extensionDateRange: [],
    typeVehicleDocumentId: null,
    reason: null,
  };

  const initFormData: VehicleExtensionFormStore = {
    applyDateAll: false,
    extensions: [extensionFormData],
  };

  const formState = reactive<VehicleExtensionFormStore>(structuredClone(initFormData));

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

    typeVehicleDocuments.value = mapItemsToOptions(data.data);
  };

  return {
    formState,
    typeVehicleDocuments,
    isUpdate,
    addExtension,
    deleteExtension,
    fetchTypeVehicleDocuments,
    resetFormState,
    applyDateAllExtensions,
    setIsUpdate,
  };
});
