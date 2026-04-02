import { ref, onMounted, watch, inject, type Ref } from 'vue';
import { Modal } from 'ant-design-vue';
import { h } from 'vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';
import moment from 'moment';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type { IgvRecordInterface } from '@/modules/negotiations/accounting-management/tax-settings/general/interfaces/igv-record.interface';
import { emit } from '@/modules/negotiations/api/eventBus';
import { taxSettingStore } from '@/modules/negotiations/accounting-management/tax-settings/general/store/taxSetting.store';
import { storeToRefs } from 'pinia';

export const useGeneralTaxList = (props: { filters: FilterDatesInterface }) => {
  const [modal, contextHolder] = Modal.useModal();

  const editSettingIgv = (record: IgvRecordInterface) => {
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
      title: 'IGV',
      dataIndex: 'igv',
      key: 'igv',
      align: 'center',
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

  const data = ref<IgvRecordInterface[]>([]);
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const cancelDialog = ref({ disabled: false });

  const fetchIgvData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;
    try {
      const response = await supportApi.get('general-taxes', {
        params: {
          page,
          per_page: pageSize,
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
          await supportApi.delete(`general-taxes/${id}`);
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

  const store = taxSettingStore();
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
