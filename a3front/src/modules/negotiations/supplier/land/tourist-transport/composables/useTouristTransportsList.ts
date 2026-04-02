import { computed, onMounted, type Ref, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import {
  type SuppliersTransportResponseInterface,
  SupplierStatus,
} from '@/modules/negotiations/supplier/land/tourist-transport/interfaces';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSearchTouristTransportFiltersStore } from '@/modules/negotiations/supplier/store/search-tourist-transport-filters.store';
import { useSupplierRouteAction } from '@/modules/negotiations/supplier/composables/useSupplierRouteAction';

interface InjectedProps {
  isLoading: Ref<boolean>;
}

export const useTouristTransportsList = () => {
  const { handleEditSupplier } = useSupplierRouteAction();

  const injectedProps: InjectedProps = {
    isLoading: ref(false),
  };
  const searchFiltersStore = useSearchTouristTransportFiltersStore();
  const { operationLocation, type_unit_transport_id, status, name } =
    storeToRefs(searchFiltersStore);

  const { isLoading } = injectedProps;

  const columns = [
    {
      title: 'Creado',
      dataIndex: 'user',
      key: 'user',
      align: 'left',
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
      align: 'center',
    },
    {
      title: 'Codigo',
      dataIndex: 'code',
      key: 'code',
      align: 'center',
    },
    {
      title: 'Nombre del proveedor',
      dataIndex: 'name',
      key: 'name',
      align: 'center',
      width: 250,
    },
    {
      title: 'RUC/DNI',
      dataIndex: 'document',
      key: 'document',
      align: 'center',
      width: 250,
    },
    {
      title: 'Tipo y cantidad de unidades',
      dataIndex: 'quantity_type_unit_transports',
      key: 'quantity_type_unit_transports',
      align: 'center',
      width: 250,
    },
    {
      title: 'Opciones',
      dataIndex: 'options',
      key: 'options',
      align: 'center',
    },
  ];

  const data = ref<SuppliersTransportResponseInterface[]>([]);
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const isValidOperationLocation = computed(() => {
    return operationLocation.value.country_id !== null && operationLocation.value.state_id !== null;
  });

  const fetchSuppliersData = async (page: number = 1, pageSize: number = 4) => {
    if (!isValidOperationLocation.value) {
      console.warn('Fetching suppliers data skipped: Invalid operation location');
      return;
    }

    isLoading.value = true;

    try {
      const request = {
        perPage: pageSize,
        page,
        name: name.value ?? undefined,
        status: status.value ?? undefined,
        type_unit_transport_id: getRequestTypeUnitTransportId(),
        operationLocation: {
          country_id: operationLocation.value.country_id,
          state_id: operationLocation.value.state_id,
          city_id: operationLocation.value.city_id,
          zone_id: operationLocation.value.zone_id,
        },
      };

      const response = await supplierApi.post('suppliers-transport', request);

      data.value = response.data.data;

      pagination.value = {
        current: response.data.pagination.current_page,
        pageSize: response.data.pagination.per_page,
        total: response.data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching suppliers data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const getRequestTypeUnitTransportId = () => {
    return type_unit_transport_id.value.filter((item) => item !== null && item !== -1);
  };

  const handleTableChange = (pagination: PaginationInterface) => {
    fetchSuppliersData(pagination.current, pagination.pageSize);
  };

  const onChange = (page: number, perSize: number) => {
    fetchSuppliersData(page, perSize);
  };

  const getProgressColor = computed(() => {
    return (percent: number) => {
      if (percent <= 99) {
        return '#FF3B3B'; // Rojo
      } else {
        return '#07DF81'; // Verde
      }
    };
  });

  const isActiveSupplier = (status: SupplierStatus) => {
    return status === SupplierStatus.ACTIVE;
  };

  const isPendingDeactivationSupplier = (status: SupplierStatus) => {
    return status === SupplierStatus.PENDING_DEACTIVATION;
  };

  const showAllOptions = (percentage: number): boolean => {
    return percentage === 100;
  };

  const goToEdit = (supplierId: number) => {
    handleEditSupplier(supplierId);
  };

  onMounted(() => {
    if (isValidOperationLocation.value) {
      fetchSuppliersData();
    }
  });

  watch(
    () => [operationLocation, type_unit_transport_id, status, name],
    () => {
      if (isValidOperationLocation.value) {
        fetchSuppliersData();
      }
    },
    { deep: true }
  );

  return {
    columns,
    data,
    pagination,
    isLoading,
    onChange,
    handleTableChange,
    getProgressColor,
    isActiveSupplier,
    isPendingDeactivationSupplier,
    showAllOptions,
    goToEdit,
  };
};
