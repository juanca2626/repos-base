import { onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { watchDebounced } from '@vueuse/core';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { useLodgeFilterStore } from '@/modules/negotiations/suppliers/lodges/store/lodge-filter.store';
import type { ApiListResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  SupplierList,
  SupplierListResponse,
} from '@/modules/negotiations/suppliers/interfaces';
import { useDrawerStore } from '@/composables/useDrawerStore';
import { useSupplierService } from '@/modules/negotiations/suppliers/services/supplier.service';
import { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';
import { pushSupplierEditForm } from '@/modules/negotiations/suppliers/utils/push-supplier-edit-form';

export function useLodgeList() {
  const lodgeFilterStore = useLodgeFilterStore();
  const { filterState, resetApplyFilters } = lodgeFilterStore;
  const { applyFilters } = storeToRefs(lodgeFilterStore);

  const { inactivateSupplier } = useSupplierService;

  const drawerStore = useDrawerStore();

  const { isOpen: isOpenDrawer } = storeToRefs(drawerStore);
  const { openDrawer, closeDrawer } = drawerStore;

  const supplierListRow = ref<SupplierList | null>(null);

  const isLoading = ref<boolean>(false);

  const router = useRouter();

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<SupplierList[]>([]);

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

      const { data } = await supplierApi.get<ApiListResponse<SupplierListResponse[]>>(
        `suppliers/${selectedClassificationId.value}`,
        {
          params: {
            perPage: pageSize,
            page,
            searchTerm: filterState.searchTerm || undefined,
            statuses: filterState.statuses,
            countryStateKeys: filterState.countryStateKeys,
            chainIds: filterState.chainIds,
          },
        }
      );

      transformListData(data.data);

      pagination.value = {
        current: data.pagination.current_page,
        pageSize: data.pagination.per_page,
        total: data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformListData = (responseData: SupplierListResponse[]) => {
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

  const reloadList = () => {
    fetchListData(pagination.value.current, pagination.value.pageSize);
  };

  const handleCloseDrawer = () => {
    closeDrawer();
    supplierListRow.value = null;
  };

  const handleEdit = (id: number) => {
    void pushSupplierEditForm(router, id);
  };

  const handleInactivate = (row: SupplierList) => {
    if (!isActive(row.status)) return;

    supplierListRow.value = row;
    openDrawer();
  };

  const onInactivateSupplier = async () => {
    if (!supplierListRow.value) return;
    await inactivateSupplier(supplierListRow.value.id);
    reloadList();
    handleCloseDrawer();
  };

  const isActive = (status: string) => {
    return status === SupplierStatusEnum.ACTIVE;
  };

  const isInactive = (status: string) => {
    return status === SupplierStatusEnum.INACTIVE;
  };

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
    data,
    columns,
    pagination,
    isLoading,
    isOpenDrawer,
    supplierListRow,
    onChange,
    handleCloseDrawer,
    handleEdit,
    handleInactivate,
    onInactivateSupplier,
    isInactive,
    isActive,
  };
}
