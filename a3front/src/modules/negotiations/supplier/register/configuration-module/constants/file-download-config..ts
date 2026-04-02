import type { FormatsDownload } from '@/modules/negotiations/supplier/register/configuration-module/interfaces/file-download.interface';

export const EXCEL_FORMAT_DOWNLOAD: FormatsDownload = {
  value: 'excel',
  name: 'MS EXCEL',
  extension: 'xlsx',
};

export const PDF_FORMAT_DOWNLOAD: FormatsDownload = {
  value: 'pdf',
  name: 'PDF',
  extension: 'pdf',
};

export const COMMON_FORMATS_DOWNLOAD: FormatsDownload[] = [
  EXCEL_FORMAT_DOWNLOAD,
  PDF_FORMAT_DOWNLOAD,
];
