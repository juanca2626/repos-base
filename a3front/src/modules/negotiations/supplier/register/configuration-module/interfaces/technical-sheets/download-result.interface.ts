import type { OperationLocationData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export interface DownloadResultForm {
  filename: string;
  format: string;
  extension: string;
  supplierBranchOfficeIds: number[];
}

export interface DownloadResultProps {
  showModal: boolean;
  locationData: OperationLocationData[];
  initialFilename: string;
  onDownload: (data: DownloadResultForm) => Promise<void>;
}
