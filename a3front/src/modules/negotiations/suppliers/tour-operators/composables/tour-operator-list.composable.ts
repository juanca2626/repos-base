import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { watchDebounced } from '@vueuse/core';
import { useRouter } from 'vue-router';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { useTourOperatorFilterStore } from '@/modules/negotiations/suppliers/tour-operators/store/tour-operator-filter.store';
import type { ApiListResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  SupplierTourOperator,
  SupplierTourOperatorResponse,
} from '@/modules/negotiations/suppliers/tour-operators/interfaces';
import { useDrawerStore } from '@/composables/useDrawerStore';
import { useInactiveResource } from '../../composables/inactive-resource.composable';
import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';
import { pushSupplierEditForm } from '@/modules/negotiations/suppliers/utils/push-supplier-edit-form';

export function useTourOperatorList() {
  const router = useRouter();
  const tourOperatorFilterStore = useTourOperatorFilterStore();
  const { filterState, resetApplyFilters } = tourOperatorFilterStore;
  const { applyFilters } = storeToRefs(tourOperatorFilterStore);
  const drawer = useDrawerStore();
  const { inactive } = useInactiveResource('tour-operators');

  const isLoading = ref<boolean>(false);
  const infoInactive = ref<SupplierTourOperator | null>(null);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<SupplierTourOperator[]>([]);

  const columns = [
    {
      title: 'Nombre del proveedor',
      dataIndex: 'businessName',
      key: 'businessName',
    },
    {
      title: 'Subtipo de proveedor',
      dataIndex: 'subClassificationName',
      key: 'subClassificationName',
    },
    {
      title: 'Estado',
      dataIndex: 'statusDescription',
      key: 'statusDescription',
    },
    {
      title: 'Ciudad',
      dataIndex: 'locationName',
      key: 'locationName',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      width: '10%',
      align: 'center',
    },
  ];

  const onChange = (page: number, perSize: number) => {
    fetchListData(page, perSize);
  };

  const fetchListData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;

    try {
      const selectedSupplierClassificationStore = useSelectedSupplierClassificationStore();
      const { selectedClassificationId } = storeToRefs(selectedSupplierClassificationStore);

      const { data } = await supplierApi.post<ApiListResponse<SupplierTourOperatorResponse[]>>(
        `suppliers/${selectedClassificationId.value}`,
        {
          perPage: pageSize,
          page,
          search_term: filterState.searchTerm,
          status: filterState.status,
          country_state_keys: filterState.countryStateKeys,
          chains: filterState.chains,
          services: filterState.services,
          operator_types: filterState.operatorTypes,
        }
      );

      transformListData(data.data);

      pagination.value = {
        current: data.pagination.current_page,
        pageSize: data.pagination.per_page,
        total: data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching tour operator list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformListData = (responseData: SupplierTourOperatorResponse[]) => {
    data.value = responseData.map((row) => {
      return {
        id: row.id,
        businessName: row.business_name,
        status: row.status,
        statusDescription: row.status_description,
        subClassificationName: row.sub_classification_name,
        locationName: row.location_name,
      };
    });
  };

  const refreshList = () => {
    fetchListData(pagination.value.current, pagination.value.pageSize);
  };

  // Handlers para las acciones
  const handleEdit = (id: number) => {
    void pushSupplierEditForm(router, id);
  };

  const handleInactive = (row: SupplierTourOperator) => {
    if (row.status === 'ACTIVE') {
      infoInactive.value = row;
      drawer.openDrawer();
    }
  };

  const handleClose = () => {
    drawer.closeDrawer();
    infoInactive.value = null;
  };

  const handleConfirmedInactive = async () => {
    if (!infoInactive.value) return;

    await inactive(infoInactive.value.id);
    infoInactive.value = null;
    refreshList();
    drawer.closeDrawer();
  };

  // Watchers
  onMounted(() => {
    fetchListData();
  });

  watchDebounced(
    () => [filterState.searchTerm],
    () => {
      fetchListData();
    },
    { debounce: 500 }
  );

  watch(
    () => [applyFilters.value],
    () => {
      if (applyFilters.value) {
        fetchListData();
        resetApplyFilters();
      }
    }
  );

  return {
    // Data
    data,
    columns,
    pagination,
    isLoading,
    infoInactive,
    drawer,

    // Methods
    onChange,
    refreshList,
    handleEdit,
    handleInactive,
    handleClose,
    handleConfirmedInactive,
  };
}
