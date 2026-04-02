import { defineStore } from 'pinia';
// import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { ref } from 'vue';
import { fetchServices } from '@operations/modules/service-management/api/serviceManagementApi';

// Función genérica para manejar las peticiones API y errores
const fetchData = async <T>(fetchFunction: () => Promise<{ data: T }>): Promise<T | null> => {
  try {
    const response = await fetchFunction();
    return response.data;
  } catch (error) {
    console.error('Error fetching data:', error);
    // Puedes implementar notificaciones o logging adicional aquí
    return null;
  }
};

export const useFilterStore = defineStore('filterStore', () => {
  const name = ref('');
  const status = ref(null);
  const page = ref(1);
  const pageSize = ref(10);
  const lastUpdated = ref(Date.now());

  function setName(newName: string) {
    name.value = newName;
    page.value = 1;
    pageSize.value = 10;
    updateLastUpdated();
  }

  function setStatus(newStatus: boolean | null) {
    status.value = newStatus;
    page.value = 1;
    pageSize.value = 10;
    updateLastUpdated();
  }

  function updateLastUpdated() {
    lastUpdated.value = Date.now();
  }

  function updatePagination(newPage: number, newPageSize: number) {
    page.value = newPage;
    pageSize.value = newPageSize;
    updateLastUpdated();
  }

  function clearFilters() {
    name.value = '';
    status.value = null;
    page.value = 1;
    pageSize.value = 10;
  }

  async function getData() {
    const filterParams = {
      name: name.value,
      status: status.value,
      page: page.value,
      pageSize: pageSize.value,
    };
    try {
      const response: any = await fetchData(() => fetchServices(filterParams));
      return response?.data;
    } catch (error) {
      console.error('Error en getData:', error);
      throw error;
    }
  }

  return {
    name,
    status,
    page,
    pageSize,
    lastUpdated,
    setName,
    setStatus,
    updatePagination,
    clearFilters,
    getData,
  };
});
