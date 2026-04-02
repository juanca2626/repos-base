import { computed, onMounted, reactive, watch } from 'vue';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import useNotification from '@/quotes/composables/useNotification';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { includes } from 'lodash-es';

export function useSupplierFormAccountingAccounts() {
  const { showErrorNotification, showSuccessNotification } = useNotification();

  interface AccountingAccount {
    id: number;
    name: string;
    account_number: string;
    actions?: string;
    editable?: boolean;
  }

  interface AccountingAccountForm {
    accounting_account_id: number;
    account_number?: string | null;
  }

  const state = reactive({
    accountingAccount: [],
    dataSource: [] as AccountingAccount[],
    dataSourceForm: [] as AccountingAccountForm[],
    selectedRowKeys: [],
    loading: false,
    columns: [
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
    ],
    idEdit: false,
    isLoadingButton: false,
  });

  const { formStateNegotiation, configSubClassification } = useSupplierFormStoreFacade();

  const handleEditable = (item: any) => {
    state.dataSource = state.dataSource.map((value: any) =>
      value.id === item.id ? { ...value, editable: true } : value
    );
  };

  const handleDelete = (item: any) => {
    state.dataSource = state.dataSource.map((value: any) =>
      value.id === item.id ? { ...value, editable: false } : value
    );
  };

  const handleOk = async () => {
    state.dataSourceForm = state.dataSource.map((item) => ({
      accounting_account_id: item.id,
      account_number: item.account_number || null,
    }));

    if (state.idEdit) {
      const attributes = {
        accounts: state.dataSourceForm,
      };
      await editApiAccountingAccounts(attributes);
    } else {
      const attributes = {
        sub_classification_supplier_id: getSubClassificationSupplierId(),
        accounts: state.dataSourceForm,
      };
      await storeApiAccountingAccounts(attributes);
    }

    await Promise.all([fetchData()]).finally(() => {
      state.loading = false;
    });
  };

  const handleCancel = async () => {
    await Promise.all([fetchData()]).finally(() => {
      state.loading = false;
    });
  };

  const getSubClassificationSupplierId = () => {
    return formStateNegotiation.classifications.find(
      (item: any) => item.supplier_sub_classification_id === configSubClassification.value
    )?.operations?.[0]?.sub_classification_supplier_id;
  };

  const fetchData = async () => {
    try {
      state.loading = true;
      state.idEdit = false;
      const response = await showApiAccountingAccounts();

      if (response.length > 0) {
        const values = await fetchResourcesData(false);
        const responseForm = response.map((item: any) => ({
          id: item.accounts.id,
          idAccount: item.id,
          name: item.accounts.name,
          account_number: item.accounts.number,
        }));

        const data = [
          ...responseForm,
          ...values.filter((item: any) => !responseForm.some((res: any) => res.id === item.id)),
        ].sort((a, b) => a.id - b.id);

        setDataSource(data);
      } else {
        await fetchResourcesData();
      }
    } catch (error) {
      console.error('Error fetching resource data:', error);
    }
  };

  const setDataSource = (data: any) => {
    state.dataSource = data.map((item: any) => ({
      ...item,
      editable: false,
    }));
  };

  const fetchResourcesData = async (isSetValue = true) => {
    try {
      const response = await supplierApi.get('supplier/accounting-account/resources', {
        params: {
          'keys[]': ['accountingAccount'],
        },
      });

      const { accountingAccount } = response.data.data;
      if (isSetValue) {
        setDataSource(accountingAccount);
      }

      return accountingAccount;
    } catch (error) {
      console.error('Error fetching resource data:', error);
    }
  };

  const storeApiAccountingAccounts = async (attributes: any) => {
    try {
      state.isLoadingButton = true;
      await supplierApi.post(`supplier/accounting-account`, attributes);
      showSuccessNotification('La cuenta contable se ha creado satisfactoriamente.');
    } catch (error) {
      showErrorNotification('Error al guardar la cuenta contable.');
      console.error('Error store accounting accounts data:', error);
    } finally {
      state.isLoadingButton = false;
    }
  };

  const editApiAccountingAccounts = async (attributes: any) => {
    try {
      const id = getSubClassificationSupplierId();
      state.isLoadingButton = true;
      await supplierApi.put(`supplier/accounting-account/${id}`, attributes);
      showSuccessNotification('La cuenta contable se ha actualizado satisfactoriamente.');
    } catch (error) {
      showErrorNotification('Error al guardar la cuenta contable.');
      console.error('Error accounting accounts information data:', error);
    } finally {
      state.isLoadingButton = false;
    }
  };

  const showApiAccountingAccounts = async () => {
    const id = getSubClassificationSupplierId();
    const { data } = await supplierApi.get(`supplier/accounting-account/${id}`);

    if (data.data.length > 0) {
      state.idEdit = true;
    }

    return data.data;
  };

  const onSetAccountNumber = async (e: any, item: any) => {
    state.dataSource = state.dataSource.map((value: any) =>
      value.id === item.id
        ? { ...value, account_number: e.target.value }
        : { ...value, account_number: value.account_number }
    );
  };

  const changeSubClassification = async (): Promise<void> => {
    state.dataSource = [];
    state.selectedRowKeys = [];
    await Promise.all([fetchData()]).finally(() => {
      state.loading = false;
    });
  };

  const onSelectChange = (selectedRowKeys: any) => {
    state.selectedRowKeys = selectedRowKeys;
  };

  const onDeleteBatch = async () => {
    try {
      const accounts = state.dataSource
        .filter((item) => includes(state.selectedRowKeys, item.id))
        .map((item) => item.idAccount);

      state.isLoadingButton = true;
      await supplierApi.post(`supplier/accounting-account/delete-batch`, {
        accounts: accounts,
      });

      await Promise.all([fetchData()]).finally(() => {
        state.loading = false;
        state.selectedRowKeys = [];
      });

      showSuccessNotification('Las cuenta contable se han eliminado satisfactoriamente.');
    } catch (error) {
      showErrorNotification('Error al eliminar las cuentas contables.');
      console.error('Error accounting accounts delete batch data:', error);
    } finally {
      state.isLoadingButton = false;
    }
  };

  onMounted(async () => {
    await Promise.all([fetchData()]).finally(() => {
      state.loading = false;
    });
  });

  watch(
    configSubClassification,
    async (newSubClassification) => {
      if (newSubClassification !== null) {
        await changeSubClassification();
      }
    },
    { deep: true }
  );

  const hasSelected = computed(() => state?.selectedRowKeys.length > 0);

  return {
    state,
    handleEditable,
    handleDelete,
    handleOk,
    handleCancel,
    hasSelected,
    onSelectChange,
    onSetAccountNumber,
    onDeleteBatch,
  };
}
