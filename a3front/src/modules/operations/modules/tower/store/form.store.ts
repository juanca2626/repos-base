import dayjs from 'dayjs';
import { defineStore } from 'pinia';
import { computed, reactive, ref } from 'vue';
import { useTableStore } from '@operations/modules/tower/store/table.store';
import { useDataStore } from '../../service-management/store/data.store';
// import { useDataStore } from '@operations/modules/tower/store/data.store';

export const useFormStore = defineStore('formStore', () => {
  const tableStore = useTableStore();
  const dataStore = useDataStore();

  // Estado reactivo principal
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  // Filtros básicos para la búsqueda
  const formSearch = reactive({
    monitored: 'all',
    city: 'all',
    option: 'date',
    dateRange: [dayjs('2024-01-01'), dayjs('2025-12-31')],
    file_numbers: '',
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
    if (key && value) {
      dynamicParams[key] = value; // Agregar parámetro dinámico
    }
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
    const { option, dateRange, file_numbers, category, language, search_text } = formSearch;

    const params: Record<string, any> = {
      search_text,
      ...(category !== 'all' && { category }),
      ...(language !== 'all' && { language }),
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
    // Reiniciar `formSearch` a sus valores por defecto
    formSearch.option = 'date';
    formSearch.dateRange = [dayjs('2024-01-01'), dayjs('2025-12-31')];
    formSearch.file_numbers = '';
    // formSearch.category = 'all';
    // formSearch.language = 'all';
    formSearch.search_text = '';

    // Limpiar los parámetros dinámicos
    clearDynamicParams();
  };

  const fetchServicesWithParams = () => {
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

  const handleClick = (action: string, type = null) => {
    console.log('🚀 ~ handleClick ~ type:', type);
    console.log('🚀 ~ handleClick ~ action:', action);
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
