import { inject, onMounted, type Ref, ref, watch } from 'vue';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { supplierApi, supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSearchSupplierTicketsFiltersStore } from '@/modules/negotiations/supplier/tickets/store/searchSupplierTicketsFilters.store';
import { storeToRefs } from 'pinia';
import { debounce } from 'lodash';
import useNotification from '@/quotes/composables/useNotification';
interface InjectedProps {
  isLoading: Ref<boolean>;
}

export const useTicketsList = () => {
  const injectedProps: InjectedProps = inject('injectedProps') || {
    isLoading: ref(false),
  };
  const searchSupplierTicketsFilters = useSearchSupplierTicketsFiltersStore();
  const { state, codeOrName, belongsToState, status } = storeToRefs(searchSupplierTicketsFilters);
  const { showErrorNotification, showSuccessNotification } = useNotification();

  const { isLoading } = injectedProps;

  const columns = [
    { title: 'Creado', dataIndex: 'created', key: 'created', sorter: true },
    { title: 'Estado', dataIndex: 'status', key: 'status', sorter: true },
    { title: 'Código', dataIndex: 'code', key: 'code', sorter: true },
    {
      title: 'Nombre del proveedor',
      key: 'supplier_name',
      dataIndex: 'supplier_name',
      sorter: true,
    },
    { title: 'RUC/DNI', key: 'document', dataIndex: 'document', sorter: true },
    {
      title: 'Pertenece al estado',
      key: 'state',
      dataIndex: 'state',
      align: 'center',
      sorter: true,
    },
    {
      title: 'Método de pago',
      key: 'payment_method',
      dataIndex: 'payment_method',
      align: 'center',
      sorter: true,
    },
    { title: 'Tipo de entrada', key: 'type', dataIndex: 'type', align: 'center', sorter: true },
    { title: 'Acciones', key: 'action', align: 'center' },
  ];

  const data = ref<[]>([]);
  const isLoadingReport = ref<boolean>(false);
  const statesResources = ref<[]>([]);
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
    showSizeChanger: false,
  });

  const fetchTicketsData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;
    try {
      let url = `supplier-tickets?page=${page}&per_page=${pageSize}`;

      if (codeOrName !== null && codeOrName.value !== '' && codeOrName.value !== null) {
        url += `&codeOrName=${codeOrName.value}`;
      }

      if (belongsToState !== null && belongsToState.value !== null) {
        url += `&belongsToState=${belongsToState.value}`;
      }

      if (status !== null && status.value !== null) {
        url += `&status=${status.value}`;
      }

      if (state !== null && state.value !== null) {
        url += `&state=${state.value}`;
      }

      const response = await supplierApi.get(url);
      data.value = response.data.data.data;
      pagination.value = {
        current: response.data.data.pagination.current_page,
        pageSize: response.data.data.pagination.per_page,
        total: response.data.data.pagination.total,
        showSizeChanger: false,
      };
    } catch (error) {
      isLoading.value = false;
      console.error('Error fetching suppliers Ticket data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const fetchStatesData = async () => {
    try {
      let url = `support/resources?keys[]=states&country_id=89`;

      const response = await supportApi.get(url);

      statesResources.value = response.data.data.states;
    } catch (error) {
      console.error('Error fetching states data:', error);
    }
  };

  const onChange = (page: number, perSize: number) => {
    fetchTicketsData(page, perSize);
  };

  const downloadReport = async () => {
    try {
      isLoadingReport.value = true;
      const response = await supplierApi.get('supplier-ticket/reports', {
        responseType: 'blob',
        headers: {
          Accept: '*/*',
        },
      });

      const fileBlob = new Blob([response.data], { type: 'application/octet-stream' });

      const customFileName = 'lista-de-proveedores.xlsx';

      const link = document.createElement('a');

      link.href = URL.createObjectURL(fileBlob);
      link.setAttribute('download', customFileName);

      document.body.appendChild(link);
      link.click();

      document.body.removeChild(link);

      showSuccessNotification('Archivo descargado correctamente');
    } catch (error) {
      isLoadingReport.value = false;
      console.error('Error al descargar el archivo:', error);
      showErrorNotification('Error al descargar el archivo');
    } finally {
      isLoadingReport.value = false;
    }
  };

  onMounted(() => {
    fetchTicketsData();
    fetchStatesData();
  });

  watch(
    [codeOrName],
    debounce(() => {
      fetchTicketsData();
    }, 500),
    { deep: true }
  );

  watch(
    [belongsToState, status, state],
    () => {
      fetchTicketsData();
    },
    { deep: true }
  );

  return {
    columns,
    data,
    statesResources,
    pagination,
    isLoading,
    onChange,
    downloadReport,
    isLoadingReport,
  };
};
