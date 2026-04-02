import dayjs from 'dayjs';
import { defineStore } from 'pinia';
import { computed, reactive, ref } from 'vue';
import { useTableStore } from '@operations/modules/providers/store/table.store';
import { useDataStore } from '@operations/modules/providers/store/data.store';
import { useNoReportStore } from './noReports.store';
import { useProviderStore } from './providerStore';

export const useFormStore = defineStore('formStore', () => {
  const tableStore = useTableStore();
  const dataStore = useDataStore();
  const noReportStore = useNoReportStore();
  const providerStore = useProviderStore();

  // Estado reactivo principal
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const preConfirmation = providerStore.getContract === 'F' ? '2' : '1';

  // Filtros básicos para la búsqueda
  const formSearch = reactive({
    confirmation: preConfirmation,
    option: 'date',
    dateRange: [dayjs('2024-01-01'), dayjs('2025-12-31')],
    file_numbers: '',
    category: 'all',
    language: 'all',
    search_text: '',
  });

  // Parámetros dinámicos añadidos explícitamente
  const dynamicParams = reactive<Record<string, any>>({});

  // Limpia todos los parámetros dinámicos
  const clearDynamicParams = () => {
    Object.keys(dynamicParams).forEach((key) => {
      delete dynamicParams[key];
    });
  };

  // Actualiza parámetros dinámicos de manera controlada
  const updateExtraParams = (key: string, value: any) => {
    console.log('Updating Dynamic Params:', { key, value });
    clearDynamicParams(); // Limpia los parámetros dinámicos antes de agregar nuevos
    // if (key && value) {
    //   dynamicParams[key] = value; // Agregar parámetro dinámico
    // }
    dynamicParams[key] = true;
  };

  // Actualiza el rango de fechas en `formSearch`
  const updateDateRange = (startDate: dayjs.Dayjs, endDate: dayjs.Dayjs) => {
    formSearch.option = 'date';
    formSearch.dateRange = [startDate, endDate];
  };

  // Actualiza la búsqueda por file `formSearch`
  const updateFileNumber = (file: string) => {
    formSearch.option = 'file';
    formSearch.file_numbers = file;
  };

  // Computa todos los parámetros procesados (filtros + dinámicos)
  const processedParams = computed(() => {
    const {
      confirmation: confirmationBase,
      option,
      dateRange,
      file_numbers,
      search_text,
    } = formSearch;

    const confirmation = noReportStore.noReport ? '5' : confirmationBase;

    const params: Record<string, any> = {
      confirmation,
      search_text,
      ...(option === 'date'
        ? {
            start_date: dateRange[0].format('YYYY-MM-DD'),
            end_date: dateRange[1].format('YYYY-MM-DD'),
          }
        : { file_numbers }),
      ...dynamicParams, // Incluye los parámetros dinámicos explícitos
    };

    console.log('Processed Params:', params);
    return params;
  });

  // Genera una URL con los parámetros procesados
  const generateUrl = (baseUrl: string) => {
    const queryString = new URLSearchParams(processedParams.value).toString();
    return `${baseUrl}?${queryString}`;
  };

  // Proxy completo de `formSearch` y `dynamicParams` para observación en `watch`
  const fullFormSearch = computed(() => ({
    ...formSearch,
    ...dynamicParams,
  }));

  const resetFilters = () => {
    formSearch.confirmation = '2';
    formSearch.option = 'date';
    formSearch.dateRange = [dayjs('2024-01-01'), dayjs('2025-01-31')];
    formSearch.file_numbers = '';
    formSearch.category = 'all';
    formSearch.language = 'all';
    formSearch.search_text = '';

    // Reiniciar `formSearch` a sus valores por defecto
    // formSearch.option = 'date';
    // formSearch.dateRange = [dayjs('2024-01-01'), dayjs('2024-12-31')];
    // formSearch.file_numbers = '';
    // formSearch.category = 'all';
    // formSearch.language = 'all';
    // formSearch.search_text = '';

    // Limpiar los parámetros dinámicos
    clearDynamicParams();
    fetchServicesWithParams();
  };

  const fetchServicesWithParams = () => {
    // const pagination = {
    //   page: tableStore.pagination.current,
    //   limit: tableStore.pagination.pageSize,
    // };

    // // Utiliza processedParams combinado con paginación
    // const params = { ...processedParams.value, ...pagination };
    // dataStore.getServices(params);
    console.log('Fetching services with params...');
    if (isLoading.value) return; // Evita llamadas concurrentes
    isLoading.value = true;
    try {
      const pagination = {
        page: tableStore.pagination.current,
        limit: tableStore.pagination.pageSize,
      };

      // Utiliza processedParams combinado con paginación
      const params = { ...processedParams.value, ...pagination };
      dataStore.getServices(params);
      dataStore.getIndicators(params);
    } finally {
      isLoading.value = false;
    }
  };

  const handleClick = (action: string) => {
    noReportStore.setNoReport(false);
    formSearch.confirmation = '';

    switch (action) {
      case 'confirmed':
        formSearch.confirmation = '1';
        break;

      case 'unconfirmed':
        formSearch.confirmation = '2';
        break;

      case 'no_report':
        formSearch.confirmation = '1';
        noReportStore.setNoReport(true);
        break;

      default:
        console.warn(`Acción desconocida: ${action}`);
    }

    const url = generateUrl('/search');
    console.log('🚀 ~ handleClick ~ url:', url);
  };

  return {
    isLoading,
    error,
    formSearch,
    processedParams,
    updateDateRange,
    updateFileNumber,
    updateExtraParams,
    generateUrl,
    fullFormSearch,
    clearDynamicParams,
    resetFilters,
    fetchServicesWithParams,
    handleClick,
  };
});
