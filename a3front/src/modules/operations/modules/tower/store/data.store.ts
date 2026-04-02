import { defineStore } from 'pinia';
import { ref } from 'vue';

import { useTableStore } from '@operations/modules/tower/store/table.store';
// import { useFormStore } from '@operations/modules/tower/store/form.store';
// import { useSelectionStore } from '@operations/shared/stores/selection.store';

import {
  fetchServices,
  fetchIndicators,
  fetchMonitorings,
  addMonitoring as addMonitoringApi,
} from '@operations/modules/tower/api/towerApi';

export const useDataStore = defineStore('dataStore', () => {
  const loading = ref(false);
  const error = ref<string | null>(null);

  const tableStore = useTableStore();
  // const formStore = useFormStore();
  // const selectionStore = useSelectionStore();
  // const { total, loading, error, meta } = storeToRefs(tableStore);

  const services = ref<any[]>([]);

  //TODO: Asignación de proveedores
  const providers = ref<any[]>([]);
  const sendServiceOrder = ref(false);

  const additionals = ref<any[]>([]);

  // const data = ref<any[]>([]);
  const kpi = ref<any>(null);

  // Función para obtener los servicios
  // const getServices = async (params: any) => {
  //   const response = await fetchServices(params);
  //   console.log('🚀 ~ getServices ~ data:', response.data);
  //   if (response) services.value = response.data;
  // };

  // Función para obtener los datos de la API con paginación
  const getServices = async (params: any) => {
    loading.value = true;
    error.value = null;

    try {
      const response: any = await fetchServices(params);
      if (response?.data) {
        const fetchedData = response.data.map((v: any) => {
          const uniqueLangs = [...new Set(v.files.map((file: any) => file.file.lang))];

          const matchedFlights = Array.isArray(v?.matched_flights) ? v.matched_flights : [];

          const matchedHotels =
            v?.matched_hotels && typeof v.matched_hotels === 'object'
              ? Object.values(v.matched_hotels)
              : [];

          let mappedData: any = {
            id: v._id,
            equivalence_id: v.equivalence_id,
            matched_flights: matchedFlights,
            matched_hotels: matchedHotels,
            related_service_id: v.related_service_id,
            monitoring: v.monitoring,
            datetime_start: v.datetime_start,
            datetime_end: v.datetime_end,
            files: v.files,
            category: v.category,
            service: v.service,
            service_short_description: v.service?.short_description || '',
            service_long_description: v.service?.long_description || '',
            partial_paxs: v.partial_paxs,
            total_paxs: v.total_paxs,
            required_guide_profiles: v.required_guide_profiles || [],
            gui: v.guides,
            required_vehicle_type: v.required_vehicle_type || null,
            trp: v.carrier,
            routes: v.routes,
          };

          if (v.files.length === 1) {
            // Caso con un solo archivo
            const { file } = v.files[0];
            mappedData = {
              ...mappedData,
              client: file.client || '--',
              lang: [file.lang], // Convertimos lang en un array para mantener consistencia
              is_vip: file.is_vip,
              vip: file.vip,
            };
          } else {
            // Caso con múltiples archivos
            mappedData = {
              ...mappedData,
              client: '-',
              lang: uniqueLangs, // Asignamos los valores únicos de lang
              is_vip: v.files?.[0]?.file?.is_vip,
              vip: v.files?.[0]?.file?.vip,
            };
          }
          return mappedData;
        });
        services.value = fetchedData;

        tableStore.updateMeta(response.meta);
      }
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const getIndicators = async (params: any) => {
    try {
      const response = await fetchIndicators(params);
      kpi.value = response.data;
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const monitorings = ref<any[]>([]);
  const getMonitorings = async (operationalServiceId: string) => {
    try {
      const response = await fetchMonitorings(operationalServiceId);
      // kpi.value = response.data;
      monitorings.value = response.data;
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const addMonitoring = async (payload: any) => {
    try {
      const response = await addMonitoringApi(payload);
      return response.data;
      // kpi.value = response.data;
      // monitorings.value = response.data;
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  return {
    loading,
    error,
    services,
    kpi,
    providers,
    sendServiceOrder,
    additionals,
    monitorings,
    getServices,
    getIndicators,
    getMonitorings,
    addMonitoring,
  };
});
