import { ref, onMounted, watch, inject, type Ref } from 'vue';
import { Modal } from 'ant-design-vue';
import { h } from 'vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import type { FiltersInputsInterface } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/filter-inputs.interface';
import moment from 'moment';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { emit } from '@/modules/negotiations/api/eventBus';
import { storeToRefs } from 'pinia';
import type { SupplierTaxResponseInterface } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/supplier-tax-response.interface';
import { filterSupplierTaxStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/filterSupplierTax.store';
import { useRouter } from 'vue-router';
import { useRouteUpdate } from '@/composables/useRouteUpdate';

export const useSupplierTaxList = (props: { filtersSupplierTax: FiltersInputsInterface }) => {
  const [modal, contextHolder] = Modal.useModal();
  const router = useRouter();
  const { updateCurrentRoute } = useRouteUpdate();

  const editSettingIgv = (record: SupplierTaxResponseInterface) => {
    emit('editTaxSupplierAssignLaw', record);
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
      title: 'Tipo de Ley',
      dataIndex: 'type_law',
      key: 'type_law',
      align: 'center',
    },
    {
      title: 'Clasificación del proveedor',
      dataIndex: 'supplier_sub_classification',
      key: 'supplier_sub_classification',
      align: 'left',
    },
    {
      title: 'Estado',
      dataIndex: 'status_assignment',
      key: 'status_assignment',
      align: 'center',
      width: 250,
    },
    {
      title: 'Período de validez de ley',
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

  const data = ref<SupplierTaxResponseInterface[]>([]);
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const cancelDialog = ref({ disabled: false });

  const fetchIgvData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;
    try {
      const response = await supportApi.get('taxes-suppliers', {
        params: {
          page,
          per_page: pageSize,
          supplier_sub_classification_id: props.filtersSupplierTax.supplier_sub_classification_id,
          date_from: props.filtersSupplierTax.from
            ? moment(props.filtersSupplierTax.from).format('YYYY-MM-DD')
            : '',
          date_to: props.filtersSupplierTax.to
            ? moment(props.filtersSupplierTax.to).format('YYYY-MM-DD')
            : '',
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
    console.log(id);
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
          await supportApi.delete(`taxes-suppliers/${id}`);
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

  const goToAssignments = (record: SupplierTaxResponseInterface) => {
    const path = `/configuration/${record.id}/assignments`;
    const routeName = 'taxSuppliersAssignments';
    updateCurrentRoute(path, routeName);
    router.push({ name: routeName, params: { id: record.id } });
  };

  const store = filterSupplierTaxStore();
  const { filtersList } = storeToRefs(store);
  watch(
    () => [props.filtersSupplierTax, filtersList.value],
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
    goToAssignments,
  };
};
