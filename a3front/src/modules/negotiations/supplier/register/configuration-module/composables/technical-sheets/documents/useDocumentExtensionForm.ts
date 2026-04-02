import { ref } from 'vue';
import dayjs, { Dayjs } from 'dayjs';
import { storeToRefs } from 'pinia';
import type { FormInstance } from 'ant-design-vue';
import { useDocumentExtensionStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useDocumentExtensionStore';
import { useNotifications } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useNotifications';

export const useDocumentExtensionForm = () => {
  const formRefDocumentExtension = ref<FormInstance | null>(null);

  const documentExtensionStore = useDocumentExtensionStore();
  const { typeDocuments } = storeToRefs(documentExtensionStore);

  const { formState, deleteExtension, applyDateAllExtensions } = documentExtensionStore;

  const { showNotificationError } = useNotifications();

  const disabledExtensionDateRange = (current: Dayjs): boolean => {
    return current && current.isBefore(dayjs(), 'day');
  };

  const formRules = {
    extensionDateRange: [{ required: true, message: 'Debe seleccionar la fecha de vigencia' }],
    typeDocumentId: [{ required: true, message: 'Debe seleccionar el tipo de documento' }],
    reason: [{ required: true, message: 'Debe ingresar el motivo de prorroga' }],
  };

  const validateFields = async () => {
    return formRefDocumentExtension.value?.validate();
  };

  const resetFields = () => {
    formRefDocumentExtension.value?.resetFields();
  };

  const handleTypeDocument = (index: number) => {
    const exists = formState.extensions.some((row, i) => {
      return i !== index && row.typeDocumentId === formState.extensions[index].typeDocumentId;
    });

    if (exists) {
      showNotificationError(`El tipo de documento seleccionado ya ha sido agregado a la lista.`);
      formState.extensions[index].typeDocumentId = null;
    }
  };

  return {
    formRefDocumentExtension,
    formRules,
    formState,
    typeDocuments,
    resetFields,
    disabledExtensionDateRange,
    validateFields,
    deleteExtension,
    applyDateAllExtensions,
    handleTypeDocument,
  };
};
