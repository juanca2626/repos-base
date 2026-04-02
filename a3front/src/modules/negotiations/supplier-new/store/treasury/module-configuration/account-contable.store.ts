import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useSupplierPoliciesService } from '@/modules/negotiations/supplier-new/service/supplier-policies.service';
import { useSupplierAccountContableService } from '@/modules/negotiations/supplier-new/service/supplier-account-contable.service';

export const useAccountContableStore = defineStore('accountContableStore', () => {
  const columns = ref<any>([
    {
      title: 'ID',
      dataIndex: 'id',
      key: 'id',
      width: '15%',
    },
    {
      title: 'Descripción',
      dataIndex: 'name',
      width: '40%',
    },
    {
      title: 'N° de Cuenta:',
      dataIndex: 'account_number',
      key: 'account_number',
      width: '35%',
    },
    {
      title: 'Acciones',
      key: 'actions',
      dataIndex: 'actions',
      width: '10%',
    },
  ]);
  const currentPage = ref<number>(1);
  const pageSize = ref<number>(10);
  const total = ref<number>(0);
  const sourceData = ref<any>([]);
  const loading = ref<boolean>(false);
  const searchQuery = ref<string>('');

  const { getSupplierAccountingAccount } = useSupplierAccountContableService;

  return {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    getSupplierAccountingAccount,
  };
});
