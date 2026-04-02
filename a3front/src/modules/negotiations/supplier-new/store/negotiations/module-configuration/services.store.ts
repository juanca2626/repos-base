import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useServiceStore = defineStore('serviceStore', () => {
  const columns = ref<any>([
    {
      title: 'Código',
      dataIndex: 'code',
      sorter: true,
      width: '10%',
      forSummary: true,
    },
    {
      title: 'Nombre del servicio',
      dataIndex: 'service_name',
      sorter: true,
      width: '20%',
      forSummary: true,
    },
    {
      title: 'Tipo de servicio',
      dataIndex: 'pax',
      sorter: true,
      width: '15%',
      forSummary: true,
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      sorter: true,
      width: '10%',
      forSummary: false,
    },
    {
      title: 'Acciones',
      dataIndex: 'actions',
      width: '10%',
      align: 'center',
      forSummary: false,
    },
  ]);
  const currentPage = ref<number>(1);
  const pageSize = ref<number>(10);
  const total = ref<number>(0);
  const sourceData = ref<any>([]);
  const loading = ref<boolean>(false);
  const searchQuery = ref<string>('');

  return { columns, currentPage, pageSize, total, sourceData, loading, searchQuery };
});
