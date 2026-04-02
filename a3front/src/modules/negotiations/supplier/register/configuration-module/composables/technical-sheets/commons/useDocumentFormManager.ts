import type { Rule } from 'ant-design-vue/es/form';
import dayjs, { Dayjs } from 'dayjs';
import { reactive, watch } from 'vue';
import type {
  FileUploadData,
  TransportDocumentForm,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useFileUploadManager } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useFileUploadManager';

export const useDocumentFormManager = (formState: TransportDocumentForm) => {
  const { maxSize, handleCustomUpload, handleRemove, clearFileUploadData, validateUploadFile } =
    useFileUploadManager();

  const fileUploadData = reactive<FileUploadData>({
    uploading: false,
    isFileUploaded: false,
    progress: 0,
    uploadedFile: null,
    uploadedSize: 0,
    fileSize: 0,
    uploadSpeed: 0,
    startTime: 0,
    uploadInterval: null,
  });

  const formRules: Record<string, Rule[]> = {
    expirationDate: [
      { required: true, message: 'Debe seleccionar la fecha de vencimiento', trigger: 'change' },
    ],
    file: [
      {
        required: true,
        validator: (_: unknown, value: File) => {
          if (!value) {
            return Promise.reject('Debe adjuntar el archivo');
          }

          if (value.size > maxSize) {
            return Promise.reject('El archivo no debe superar los 500 KB.');
          }

          return Promise.resolve();
        },
        trigger: 'change',
      },
    ],
  };

  const handleRemoveFile = () => {
    handleRemove(fileUploadData);
    formState.file = null;
  };

  const disabledExpirationDate = (current: Dayjs): boolean => {
    return current && (current.isBefore(dayjs(), 'day') || current.isSame(dayjs(), 'day'));
  };

  watch(
    () => fileUploadData.isFileUploaded,
    (isFileUploaded) => {
      if (isFileUploaded) {
        formState.file = fileUploadData.uploadedFile;
      }
    }
  );

  return {
    fileUploadData,
    formRules,
    handleCustomUpload,
    clearFileUploadData,
    validateUploadFile,
    disabledExpirationDate,
    handleRemoveFile,
  };
};
