import { notification } from 'ant-design-vue';
import type { FileUploadData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import {
  allowedFileTypes,
  allowedPhotoTypes,
} from '@/modules/negotiations/supplier/register/configuration-module/constants/file-upload';

export const useFileUploadManager = () => {
  const byteToKb = 1024;
  const baseSize = 500;
  const maxSize = baseSize * byteToKb; // 500 KB en bytes

  const updateProgress = (uploadData: FileUploadData, progressUploadedSize: number) => {
    const elapsedTime = (Date.now() - (uploadData.startTime ?? 0)) / 1000; // Tiempo transcurrido en segundos
    const convertKb = progressUploadedSize / byteToKb; // convierte bytes a kilobytes

    uploadData.uploadedSize = progressUploadedSize;
    uploadData.progress = Math.round((progressUploadedSize / uploadData.fileSize) * 100);
    uploadData.uploadSpeed = parseFloat((convertKb / elapsedTime).toFixed(1)); // KB/sec
  };

  const setFileUploadData = (uploadData: FileUploadData, file: File) => {
    uploadData.uploading = true;
    uploadData.uploadedFile = file;
    uploadData.fileSize = file.size;
    uploadData.uploadedSize = 0;
    uploadData.uploadSpeed = 0;
    uploadData.startTime = Date.now();
  };

  const initFileUploadData = (uploadData: FileUploadData) => {
    uploadData.uploading = false;
    uploadData.isFileUploaded = false;
    uploadData.progress = 0;
    uploadData.uploadedFile = null;
    uploadData.uploadedSize = 0;
    uploadData.fileSize = 0;
    uploadData.uploadSpeed = 0;
    uploadData.startTime = 0;
  };

  const handleCustomUpload = (
    { file }: { file: File },
    uploadData: FileUploadData,
    updateFileStatus?: (file: File) => void
  ) => {
    setFileUploadData(uploadData, file);
    const uploadedPercentage = 0.1; // Simular 10% de subida
    let progressUploadedSize = 0;

    clearUploadInterval(uploadData);

    uploadData.uploadInterval = setInterval(() => {
      // cantidad de bytes que se han subido hasta el momento
      progressUploadedSize += uploadData.fileSize * uploadedPercentage;

      if (progressUploadedSize > uploadData.fileSize) {
        progressUploadedSize = uploadData.fileSize;
      }

      updateProgress(uploadData, progressUploadedSize);

      if (progressUploadedSize >= uploadData.fileSize) {
        clearInterval(uploadData.uploadInterval!);
        uploadData.uploading = false;
        uploadData.isFileUploaded = true;

        if (updateFileStatus) {
          updateFileStatus(file);
        }
      }
    }, 300);
  };

  const handleRemove = (uploadData: FileUploadData) => {
    clearFileUploadData(uploadData);
  };

  const clearFileUploadData = (uploadData: FileUploadData) => {
    clearUploadInterval(uploadData);
    initFileUploadData(uploadData);
  };

  const clearUploadInterval = (uploadData: FileUploadData) => {
    if (uploadData.uploadInterval) {
      clearInterval(uploadData.uploadInterval);
      uploadData.uploadInterval = null;
    }
  };

  const validateFile = (file: File, allowedTypes: string[], errorMessage: string) => {
    if (file.size > maxSize) {
      showNotificationError(`El archivo no debe superar los ${baseSize} KB.`);
      return false;
    }

    if (!allowedTypes.includes(file.type)) {
      showNotificationError(`${errorMessage}: ${file.type}`);
      return false;
    }

    return true;
  };

  const validateUploadFile = (file: File) => {
    return validateFile(file, allowedFileTypes, 'Tipo de archivo no permitido');
  };

  const validateUploadPhoto = (file: File) => {
    return validateFile(file, allowedPhotoTypes, 'Tipo de foto no permitida');
  };

  const showNotificationError = (description: string) => {
    notification.error({
      message: 'Aurora',
      description: description,
    });
  };

  return {
    maxSize,
    handleCustomUpload,
    handleRemove,
    clearFileUploadData,
    validateUploadFile,
    updateProgress,
    setFileUploadData,
    validateUploadPhoto,
    showNotificationError,
  };
};
