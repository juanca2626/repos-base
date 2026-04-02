import { ref } from 'vue';
import { storeToRefs } from 'pinia';
import dayjs from 'dayjs';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useOperationLocationsTab } from '@/modules/negotiations/supplier/store/operation-locations-tab.store';

export const useTouristTransportListPage = () => {
  const operationLocationsTabStore = useOperationLocationsTab();
  const { activeKey, data: dataLocation } = storeToRefs(operationLocationsTabStore);

  // Estado del modal
  const modalVisible = ref<boolean>(false);

  const showModal = () => {
    modalVisible.value = true;
  };

  const getTransportDefaultFileName = () => {
    const existsLocation = dataLocation.value.find((row) => row.ids === activeKey.value);
    return `Proveedores transporte - ${existsLocation?.display_name ?? ''} ${dayjs().year()}`;
  };

  const handleDownload = async (fileName: string, format: string, extension: string) => {
    const response = await supplierApi.get('suppliers-transport/report', {
      responseType: 'blob',
    });

    const fileBlob = new Blob([response.data]);
    const link = document.createElement('a');
    link.href = URL.createObjectURL(fileBlob);
    link.download = `${fileName}.${extension}`;
    link.click();
    modalVisible.value = false;
  };

  return {
    modalVisible,
    showModal,
    handleDownload,
    getTransportDefaultFileName,
  };
};
