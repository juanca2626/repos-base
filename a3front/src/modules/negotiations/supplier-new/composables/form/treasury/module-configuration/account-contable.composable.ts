import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import debounce from 'lodash.debounce';
import { useAccountContableStore } from '@/modules/negotiations/supplier-new/store/treasury/module-configuration/account-contable.store';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

export function useAccountContableComposable() {
  const { subClassificationSupplierId } = useSupplierGlobalComposable();

  const serviceStore = useAccountContableStore();

  const { columns, currentPage, pageSize, total, sourceData, loading, searchQuery } =
    storeToRefs(serviceStore);

  const { getSupplierAccountingAccount } = serviceStore;

  const fetchPoliciesListData = async (
    page: number = 1,
    perPage: number = 10,
    searchQuery: string = ''
  ) => {
    loading.value = true;

    const { data, pagination } = await getSupplierAccountingAccount(
      subClassificationSupplierId.value
    );

    sourceData.value = data;
    // currentPage.value = pagination.current_page;
    // pageSize.value = pagination.per_page;
    // total.value = pagination.total;
    loading.value = false;
  };

  onMounted(async () => {
    await fetchPoliciesListData();
  });

  const handleChangePagination = async (page: number, size: number): Promise<void> => {
    currentPage.value = page;
    pageSize.value = size;

    await fetchPoliciesListData(page, size, searchQuery.value);
  };

  const handleSearch = debounce(async ({ target }: any): Promise<void> => {
    searchQuery.value = target.value;
    await fetchPoliciesListData(currentPage.value, pageSize.value, searchQuery.value);
  }, 300);

  return {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    handleChangePagination,
    handleSearch,
  };
}
