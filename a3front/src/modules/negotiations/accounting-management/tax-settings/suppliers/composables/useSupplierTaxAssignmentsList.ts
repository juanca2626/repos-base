import { computed, inject, onMounted, ref, type Ref, watch } from 'vue';
import type { SupplierTaxAssignmentResponseInterface } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/supplier-tax-assignment-response.interface';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { useSupplierTaxAssignFilterStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierTaxAssignFilter.store';
import { useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';

export const useSupplierTaxAssignmentsList = () => {
  const assigned = ref<number[]>([]);
  const unassigned = ref<number[]>([]);
  const route = useRoute();
  const id = route.params.id as string;
  const supplierTaxAssignFilter = useSupplierTaxAssignFilterStore();
  const { name, supplierSubClassificationId, taxesSupplierClassificationId, page, pageSize } =
    storeToRefs(supplierTaxAssignFilter);

  interface InjectedProps {
    isLoading: Ref<boolean>;
  }
  const injectedProps: InjectedProps = inject('injectedProps') || {
    isLoading: ref(false),
  };
  const { isLoading } = injectedProps;

  const columns = [
    {
      title: 'Código',
      dataIndex: 'code',
      key: 'code',
      align: 'center',
    },
    {
      title: 'Nombre del proveedor',
      dataIndex: 'name',
      key: 'name',
      align: 'center',
    },
    {
      title: 'RUC/DNI',
      dataIndex: 'ruc',
      key: 'ruc',
      align: 'center',
      width: 250,
    },
  ];

  const dataList = ref<SupplierTaxAssignmentResponseInterface[]>([]);
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const selectedRowKeys = ref<number[]>([]);

  const fetchData = async () => {
    isLoading.value = true;
    try {
      const response = await supplierTaxAssignFilter.getData();
      dataList.value = response.data;
      pagination.value = {
        current: response.pagination.current_page,
        pageSize: response.pagination.per_page,
        total: response.pagination.total,
      };

      selectedRowKeys.value = dataList.value
        .filter((item) => item.assigned)
        .map((item) => item.sub_classification_supplier_id);
    } catch (error) {
      console.error('Error fetching IGV data:', error);
    } finally {
      isLoading.value = false; // Ocultar loader después de completar la solicitud
    }
  };

  const handleTableChange = (newPagination: PaginationInterface) => {
    supplierTaxAssignFilter.setPage(newPagination.current);
    supplierTaxAssignFilter.setPageSize(newPagination.pageSize);
  };

  const onChange = (newPage: number, newPageSize: number) => {
    supplierTaxAssignFilter.setPage(newPage);
    supplierTaxAssignFilter.setPageSize(newPageSize);
  };

  const updateAssignments = async () => {
    isLoading.value = true;
    try {
      await supportApi.post('assigned-supplier-taxes', {
        taxes_supplier_classification_id: Number(id),
        assigned: assigned.value,
        unassigned: unassigned.value,
      });
      isLoading.value = false;
      // Resetear unassigned después de una actualización exitosa
      unassigned.value = [];
    } catch (error) {
      isLoading.value = false;
      console.error('Error updating assignments:', error);
    }
  };

  const rowSelection = computed(() => ({
    selectedRowKeys: selectedRowKeys.value,
    onChange: (newSelectedRowKeys: number[]) => {
      const oldSelectedKeys = new Set(selectedRowKeys.value);
      const newSelectedKeysSet = new Set(newSelectedRowKeys);

      // Identificar nuevas asignaciones y desasignaciones
      const newlyAssigned = newSelectedRowKeys.filter((key) => !oldSelectedKeys.has(key));
      const newlyUnassigned = selectedRowKeys.value.filter((key) => !newSelectedKeysSet.has(key));

      // Actualizar assigned y unassigned
      assigned.value = [...assigned.value, ...newlyAssigned];
      unassigned.value = [...unassigned.value, ...newlyUnassigned];

      // Actualizar selectedRowKeys
      selectedRowKeys.value = newSelectedRowKeys;

      // Enviar la petición POST
      updateAssignments();
    },
  }));

  // Observar cambios en el store y ejecutar fetchData
  watch([name, supplierSubClassificationId, taxesSupplierClassificationId, page, pageSize], () => {
    if (supplierTaxAssignFilter.areRequiredFieldsPresent()) {
      fetchData();
    }
  });

  // Ejecutar fetchData al montar el componente solo si los campos obligatorios están presentes
  onMounted(() => {
    if (supplierTaxAssignFilter.areRequiredFieldsPresent()) {
      fetchData();
    }
  });

  return {
    columns,
    dataList,
    pagination,
    isLoading,
    rowSelection,
    handleTableChange,
    onChange,
  };
};
