import type { UploadFile } from 'ant-design-vue';
import type { FileUploadData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export interface UploadPhotoProps {
  fileUploadData: FileUploadData;
  imageKey: string;
  fileList: UploadFile[];
  showRemoveIcon?: boolean;
  disabledUpload?: boolean;
  onUpload?: (file: UploadFile, imageKey: string) => void;
  onRemove?: (imageKey: string) => void;
}

export interface UploadPhotoEmits {
  (event: 'update:fileList', fileList: UploadFile[]): void;
}
