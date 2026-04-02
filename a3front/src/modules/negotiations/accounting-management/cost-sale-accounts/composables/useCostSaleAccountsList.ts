import { ref, onMounted, watch, inject, type Ref } from 'vue';
import { Modal } from 'ant-design-vue';
import { h } from 'vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import type { FiltersInputsInterface } from '@/modules/negotiations/accounting-management/cost-sale-accounts/interfaces/filters-inputs.interface';
import moment from 'moment';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { emit } from '@/modules/negotiations/api/eventBus';
import { storeToRefs } from 'pinia';
import type { CostSaleAccountsResponseInterface } from '@/modules/negotiations/accounting-management/cost-sale-accounts/interfaces/cost-sale-accounts-response.interface';
import { financialExpensesStore } from '@/modules/negotiations/accounting-management/financial-expenses/store/financialExpenses.store';

export const useCostSaleAccountsList = (props: { filters: FiltersInputsInterface }) => {
  const [modal, contextHolder] = Modal.useModal();

  const editSettingIgv = (record: CostSaleAccountsResponseInterface) => {
    emit('editSettingIgv', record);
  };

  interface InjectedProps {
    isLoading: Ref<boolean>;
  }

  const injectedProps: InjectedProps = inject('injectedProps') || {
    isLoading: ref(false),
  };

  const { isLoading } = injectedProps;

  const columns = [
    {
      title: 'Usuario',
      dataIndex: 'user',
      key: 'user',
      align: 'left',
    },
    {
      title: 'Classificación del servicio',
      dataIndex: 'classification',
      key: 'classification',
      align: 'center',
    },
    {
      title: 'Cuenta de costo',
      dataIndex: 'cost_account',
      key: 'cost_account',
      align: 'center',
    },
    {
      title: 'Cuenta de venta',
      dataIndex: 'sale_account',
      key: 'sale_account',
      align: 'center',
      width: 250,
    },
    {
      title: 'Período de validez',
      dataIndex: 'period',
      key: 'period',
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

  const data = ref<CostSaleAccountsResponseInterface[]>([]);
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const cancelDialog = ref({ disabled: false });

  const fetchIgvData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;
    try {
      const response = await supportApi.get('cost-sale-accounts', {
        params: {
          page,
          per_page: pageSize,
          service_classification_id: [props.filters.service_classification_id],
          cost_account: props.filters.cost_account,
          sale_account: props.filters.sale_account,
          date_from: props.filters.from ? moment(props.filters.from).format('YYYY-MM-DD') : '',
          date_to: props.filters.to ? moment(props.filters.to).format('YYYY-MM-DD') : '',
        },
      });
      data.value = response.data.data;
      pagination.value = {
        current: response.data.pagination.current_page,
        pageSize: response.data.pagination.per_page,
        total: response.data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching IGV data:', error);
    } finally {
      isLoading.value = false; // Ocultar loader después de completar la solicitud
    }
  };

  const handleTableChange = (pagination: PaginationInterface) => {
    fetchIgvData(pagination.current, pagination.pageSize);
  };

  const showPromiseConfirm = (id: string) => {
    modal.confirm({
      title: '¿Quieres eliminar el registro?',
      icon: h(ExclamationCircleOutlined),
      content: 'Al hacer clic en el botón Eliminar, se eliminará el registro',
      okText: 'Eliminar',
      cancelText: 'Cancelar',
      okType: 'primary',
      keyboard: false,
      cancelButtonProps: cancelDialog.value,
      async onOk() {
        try {
          cancelDialog.value.disabled = true;
          await supportApi.delete(`cost-sale-accounts/${id}`);
          await fetchIgvData();
        } catch (error) {
          console.error('Oops errors!', error);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
      onCancel() {},
    });
  };

  const onChange = (page: number, perSize: number) => {
    fetchIgvData(page, perSize);
  };

  const store = financialExpensesStore();
  const { filtersList } = storeToRefs(store);
  watch(
    () => [props.filters, filtersList.value],
    () => {
      fetchIgvData();
    },
    { deep: true }
  );

  onMounted(fetchIgvData);

  return {
    columns,
    data,
    pagination,
    isLoading,
    contextHolder,
    editSettingIgv,
    handleTableChange,
    showPromiseConfirm,
    onChange,
  };
};
