import { computed, ref } from 'vue';
import { emit as emitBus } from '@/modules/negotiations/api/eventBus';
import { DOCUMENT_ENTITY_MAPPING } from '@/modules/negotiations/supplier/register/configuration-module/constants/document-entity-mapping';
import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
import { useDocumentUtils } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useDocumentUtils';
import { ReviewEntityEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/review-entity-enum';
import { handleError } from '@/modules/negotiations/api/responseApi';
import type { TransportDocumentResponseType } from '@/modules/negotiations/supplier/register/configuration-module/types';

export const useDocumentFormUtils = (typeTechnicalSheet: TypeTechnicalSheetEnum) => {
  type TransportDocumentResponse = TransportDocumentResponseType<typeof typeTechnicalSheet>;

  const isLoading = ref<boolean>(false);

  const isTransportVehicle = computed(() => {
    return typeTechnicalSheet === TypeTechnicalSheetEnum.TRANSPORT_VEHICLE;
  });

  const documentEntity = DOCUMENT_ENTITY_MAPPING[typeTechnicalSheet];

  const resource = documentEntity.resource;

  const { fetchDocument, downloadDocument } = useDocumentUtils(resource);

  const reviewEntity = isTransportVehicle.value
    ? ReviewEntityEnum.VEHICLE_DOCUMENT
    : ReviewEntityEnum.DRIVER_DOCUMENT;

  const reloadList = () => {
    const event = isTransportVehicle.value
      ? 'reloadTransportVehicleList'
      : 'reloadTransportDriverList';

    emitBus(event);
  };

  const handleDownload = async (filename: string, documentId?: string | null) => {
    if (!documentId) return;

    try {
      isLoading.value = true;
      await downloadDocument(documentId, filename);
    } catch (error: any) {
      handleError(error);
      console.error(`Error downloading ${resource}:`, error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleShow = async (
    id: string,
    setDataToForm: (data: TransportDocumentResponse) => void
  ) => {
    try {
      isLoading.value = true;

      const data = await fetchDocument<TransportDocumentResponse>(id);
      setDataToForm(data);
    } catch (error: any) {
      handleError(error);
      console.error(`Error show ${resource}:`, error);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isTransportVehicle,
    documentEntity,
    resource,
    reviewEntity,
    isLoading,
    fetchDocument,
    reloadList,
    handleDownload,
    handleShow,
  };
};
