import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import { computed, onMounted, ref } from 'vue';
import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

export function useSupplierList() {
  const { selectedClassificationId, supplierInfo } = useSupplier();

  const isLoading = ref<boolean>(false);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<any[]>([]);

  const columns = [
    {
      title: 'Nombre del proveedor',
      dataIndex: 'name',
      key: 'name',
      sorter: true,
    },
    {
      title: 'Subtipo de proveedor',
      dataIndex: 'classificationName',
      key: 'classificationName',
      sorter: true,
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
      align: 'center',
      sorter: true,
    },
    {
      title: 'Ciudad',
      dataIndex: 'cityName',
      key: 'cityName',
      align: 'center',
      sorter: true,
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
    console.log(page, pageSize);
    data.value = [];
    isLoading.value = true;

    await sleep(1000);

    for (let i = 1; i <= 10; i++) {
      data.value.push({
        id: i,
        name: `Proveedor 00${i}`,
        classificationName: classificationName.value,
        status: 'Estado',
        cityName: 'Lima',
      });
    }

    isLoading.value = false;
  };

  const classificationName = computed(() => {
    if (selectedClassificationId.value)
      return supplierInfo[selectedClassificationId.value]?.classificationName;
    return null;
  });

  const sleep = (ms: number) => {
    return new Promise((resolve) => setTimeout(resolve, ms));
  };

  onMounted(() => {
    fetchListData();
  });

  return {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
  };
}
