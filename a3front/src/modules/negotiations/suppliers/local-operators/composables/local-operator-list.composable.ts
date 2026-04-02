import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { watchDebounced } from '@vueuse/core';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { useLocalOperatorFilterStore } from '@/modules/negotiations/suppliers/local-operators/store/local-operator-filter.store';
import type { ApiListResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  SupplierLocalOperator,
  SupplierLocalOperatorResponse,
} from '@/modules/negotiations/suppliers/local-operators/interfaces';
import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';

export function useLocalOperatorList() {
  const localOperatorFilterStore = useLocalOperatorFilterStore();
  const { filterState, resetApplyFilters } = localOperatorFilterStore;
  const { applyFilters } = storeToRefs(localOperatorFilterStore);

  const isLoading = ref<boolean>(false);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<SupplierLocalOperator[]>([]);

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

      const { data } = await supplierApi.post<ApiListResponse<SupplierLocalOperatorResponse[]>>(
        `suppliers/${selectedClassificationId.value}`,
        {
          perPage: pageSize,
          page,
          search_term: filterState.searchTerm,
          status: filterState.status,
          country_state_keys: filterState.countryStateKeys,
          chains: filterState.chains,
        }
      );

      transformListData(data.data);

      pagination.value = {
        current: data.pagination.current_page,
        pageSize: data.pagination.per_page,
        total: data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching contact list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformListData = (responseData: SupplierLocalOperatorResponse[]) => {
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

  onMounted(async () => {
    await fetchListData();
  });

  watchDebounced(
    () => [filterState.searchTerm],
    async () => {
      await fetchListData();
    },
    { debounce: 500 }
  );

  watch(
    () => [applyFilters.value],
    async () => {
      if (applyFilters.value) {
        await fetchListData();
        resetApplyFilters();
      }
    }
  );

  return {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
  };
}
