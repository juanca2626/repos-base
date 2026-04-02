import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';

export const useDocumentUtils = (resource: string) => {
  const fetchDocument = async <T>(documentId: string): Promise<T> => {
    const response = await technicalSheetApi.get<ApiResponse<T>>(`${resource}/${documentId}`);
    return response.data.data as T;
  };

  const downloadDocument = async (documentId: string, filename: string) => {
    try {
      const response = await technicalSheetApi.get(`${resource}/download/${documentId}`, {
        responseType: 'blob',
      });
      const fileBlob = new Blob([response.data]);
      const link = document.createElement('a');
      link.href = URL.createObjectURL(fileBlob);
      link.download = filename;
      link.click();
    } catch (error: any) {
      console.error(`Error downloading document from ${resource}:`, error);
    }
  };

  return {
    fetchDocument,
    downloadDocument,
  };
};
