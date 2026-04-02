import { ref, watch } from 'vue';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  OperationLocationData,
  Service,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useConfigurationModule } from '@/modules/negotiations/supplier/register/configuration-module/composables/useConfigurationModule';

export function useServiceList(selectedLocation: OperationLocationData) {
  const { isTransportSubClassification } = useConfigurationModule();

  const isLoading = ref<boolean>(false);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<Service[]>([]);

  const columns = [
    {
      title: 'Código',
      dataIndex: 'code',
      key: 'code',
    },
    {
      title: 'Nombre del servicio',
      dataIndex: 'service_name',
      key: 'service_name',
    },
    {
      title: 'Ciudad',
      dataIndex: 'city',
      key: 'city',
      align: 'center',
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
      align: 'center',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const onChange = (page: number, perSize: number) => {
    fetchServiceListData(page, perSize);
  };

  const handleShowComponent = () => {
    console.log('handleShowComponent');
  };

  const fetchServiceListData = async (page: number = 1, pageSize: number = 10) => {
    console.log(page, pageSize);
    data.value = [];
    isLoading.value = true;

    await sleep(1000);

    for (let i = 1; i <= 5; i++) {
      data.value.push({
        code: `LIT00${i}`,
        service_name: `Servicio ${i} - ${isTransportSubClassification() ? 'Transporte' : 'Entradas'}`,
        city: 'Lima',
        status: true,
      });
    }

    isLoading.value = false;
  };

  const sleep = (ms: number) => {
    return new Promise((resolve) => setTimeout(resolve, ms));
  };

  watch(
    () => selectedLocation,
    () => {
      fetchServiceListData();
    },
    { deep: true }
  );

  return {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    handleShowComponent,
  };
}
