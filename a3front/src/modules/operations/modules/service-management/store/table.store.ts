// store/table.store.ts
import { defineStore, storeToRefs } from 'pinia';
import { ref, computed } from 'vue';
import { useDataStore } from './data.store'; // Usamos el store de datos para la API

// import { fetchServices } from '@operations/modules/service-management/api/serviceManagementApi';

export const useTableStore = defineStore('tableStore', () => {
  const dataStore = useDataStore();
  const { services } = storeToRefs(dataStore);

  const total = ref(0); // Total de elementos disponibles en la API

  const meta = ref({
    total: 0,
    count: 0,
    page: 1,
    limit: 5,
    totalPages: 1,
  });

  const currentPage = ref(1); // Página actual
  const pageSize = ref(10); // Tamaño de página (items por página)
  const lastUpdated = ref(Date.now());
  // const data = ref<any[]>([]);
  const kpi = ref<any>(null); // KPI de la respuesta

  // Configuración de la paginación para la tabla de datos
  const pagination = computed(() => ({
    current: currentPage.value,
    pageSize: pageSize.value,
    total: total.value,
  }));

  function updateLastUpdated() {
    lastUpdated.value = Date.now();
  }

  const onChange = (page: number, perSize: number) => {
    currentPage.value = page;
    pageSize.value = perSize;
    updateLastUpdated();
  };

  const updateMeta = (metaData: any) => {
    meta.value = metaData;
    currentPage.value = metaData.page;
    pageSize.value = metaData.limit;
    total.value = metaData.total;
  };

  return {
    services, // Devolvemos los servicios obtenidos

    pagination,
    // data,
    kpi, // Exponemos KPI para su posible uso en la UI
    total,
    meta,
    lastUpdated,
    onChange,
    updateMeta,
  };
});
