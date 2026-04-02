// data.store.ts
import { defineStore } from 'pinia';
import { computed, reactive, ref } from 'vue';

import { useTableStore } from '@operations/modules/providers/store/table.store';
import { useFormStore } from '@operations/modules/providers/store/form.store';
import { useSelectionStore } from '@operations/shared/stores/selection.store';
import { useNoReportStore } from './noReports.store';
// import { useFormStore } from '@operations/modules/service-management/store/form.store';
import {
  fetchServices,
  fetchIndicators,
  fetchDriversByProvider,
  fetchVehiclesByProvider,
  fetchProcess,
  updateAssignment as updateAssignmentApi,
  confirmAssignments as confirmAssignmentsApi,
  createReport as createReportApi,
  fecthHistoryIncidents,
} from '@operations/modules/providers/api/providersApi';
import { notification } from 'ant-design-vue';
// @ts-ignore
import { getUserInfo } from '@/utils/auth.js';
import { useBooleans } from '@/composables/useBooleans';

export const useDataStore = defineStore('dataStore', () => {
  const userInfo = reactive(getUserInfo());
  const loading = ref(false);
  const error = ref<string | null>(null);

  const tableStore = useTableStore();
  const formStore = useFormStore();
  const selectionStore = useSelectionStore();
  const noReportStore = useNoReportStore();
  const { setValue } = useBooleans();

  // const { total, loading, error, meta } = storeToRefs(tableStore);

  const services = ref<any[]>([]);

  // Selección de chofer y vehículo
  const drivers = ref<any[]>([]);
  const vehicles = ref<any[]>([]);
  const incidents = ref<any[]>([]);
  const historyIncidents = ref<any[]>([]);
  const selectedDriverId = ref<string | null>(null);
  const selectedVehicleId = ref<string | null>(null);

  // Computed properties para obtener los objetos seleccionados
  const selectedDriver = computed(() => {
    return drivers.value.find((driver) => driver._id === selectedDriverId.value) || null;
  });

  const selectedVehicle = computed(() => {
    return vehicles.value.find((vehicle) => vehicle._id === selectedVehicleId.value) || null;
  });

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
          // Obtener los valores únicos de lang en caso de múltiples archivos
          const uniqueLangs = [...new Set(v.files.map((file: any) => file.file.lang))];

          const preAssignment = noReportStore.noReport
            ? { confirmation: { status: 'NoReport' } }
            : getAssigmentFromProvider(v);

          const matchedFlights = Array.isArray(v?.matched_flights) ? v.matched_flights : [];

          const matchedHotels = Array.isArray(v?.matched_hotels) ? v.matched_hotels : [];

          let mappedData: any = {
            id: v._id,
            matched_flights: matchedFlights,
            matched_hotels: matchedHotels,
            related_service_id: v.related_service_id,
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
            assignment: preAssignment,
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
        console.log('🚀 ~ getServices ~ fetchedData:', fetchedData);
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
      console.log('🚀 ~ getIndicators ~ response:', response);
      kpi.value = response.data;

      console.log('DATA KPI:', kpi.value);
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  // Confirmar / Cancelar asignaciones
  const confirmAssignments = async (selectedItems: any[], answer: boolean) => {
    try {
      loading.value = true;
      console.log('🚀 ~ confirmServices ~ selectedItems:', selectedItems);

      const payload = {
        assignments_id: selectedItems.map((v) => String(v.assignment._id)),
        answer,
      };

      await confirmAssignmentsApi(payload);

      notification.success({
        message: (answer ? 'Confirmación' : 'Cancelación') + ' exitosamente',
        description: answer
          ? 'Los servicios fueron confirmados correctamente.'
          : 'Los servicios fueron cancelados correctamente.',
      });

      setValue('isConfirmed', false);
      formStore.fetchServicesWithParams();
    } catch (error: unknown) {
      console.error('Error en la confirmación / cancelación de asignaciones:', error);

      // Notificar error
      notification.error({
        message: `Error en la ${answer ? 'confirmación' : 'cancelación'}`,
        description: `Hubo un problema al ${answer ? 'confirmar' : 'cancelar'} los servicios seleccionados.`,
      });
    } finally {
      loading.value = false;
      selectionStore.selectedItems = [];
    }
  };

  const getAssigmentFromProvider = (os: any) => {
    console.log('🚀 ~ getAssigmentFromProvider ~ os:', os);
    if (userInfo.type === 'TRP') {
      console.log('🚀 ~ getAssigmentFromProvider ~ userInfo:', userInfo);
      console.log(userInfo.username);
      return os.carrier.find((v: any) => v.provider.code === userInfo.username);
    } else {
      return os.guides.find((v: any) => v.provider.code === userInfo.username);
    }
  };

  const handleUnitAssignment = async (process: string, data: any) => {
    try {
      const driver = selectedDriver.value;
      const vehicle = selectedVehicle.value;
      if (!driver || !vehicle) {
        notification.warning({
          message: 'Asignación de chofer / unidad',
          description: 'Es necesario seleccionar un chofer y una unidad para seguir.',
        });
        return;
      }
      const { assignment } = data;
      await updateAssignmentApi(assignment._id, {
        driver_id: driver._id,
        vehicle_id: vehicle._id,
      });

      // Reiniciar las variables seleccionadas
      selectedDriverId.value = null;
      selectedVehicleId.value = null;

      // Notificar éxito
      notification.success({
        message: 'Asignación exitosa',
        description: 'El chofer y la unidad han sido asignados correctamente.',
      });

      loading.value = false;
      formStore.fetchServicesWithParams();

      // const response = await assignment(payload);
    } catch (error) {
      console.error('Error al actualizar el tipo de vehículo:', error);

      notification.error({
        message: 'Error al actualizar la asignación',
        description: 'Hubo un problema al actualizar la asignación. Por favor intenta nuevamente.',
      });
    } finally {
      loading.value = false;
    }
  };

  const handleCreateReport = async (data: any) => {
    try {
      const response = await createReportApi(data);
      console.log('🚀 ~ handleCreateReport ~ data:', data);
      console.log('🚀 ~ handleCreateReport ~ response:', response);
    } catch (error: unknown) {
      console.error('Error al crear el reporte:', error);

      // Notificar error
      notification.error({
        message: 'Error al crear el reporte',
        description: 'Hubo un problema al crear el reporte. Por favor intenta nuevamente.',
      });
    } finally {
      loading.value = false;
    }
  };

  // const getStatus = (guides: any) => {
  //   if (guides.length === 0) return '';
  //   console.log(guides);
  // };

  const getDriversByProvider = async (params: any) => {
    loading.value = true;
    error.value = null;

    try {
      const response: any = await fetchDriversByProvider(params);
      // if (response.data.length > 0) {
      //   const mappedDrivers = response.data.map((driver: any) => ({
      //     id: driver._id,
      //     name: driver.name,
      //     license: driver.license,
      //     provider: driver.provider,
      //     status: driver.status,
      //   }));
      // }
      drivers.value = response.data;
    } catch (err: unknown) {
      console.error('Error al obtener conductores:', err);
      error.value = 'Error fetching drivers';
    } finally {
      loading.value = false;
    }
  };

  const getVehiclesByProvider = async (params: any) => {
    loading.value = true;
    error.value = null;

    try {
      const response: any = await fetchVehiclesByProvider(params);
      // if (response?.data.length > 0) {
      //   const mappedVehicles = response.data.map((vehicle: any) => ({
      //     id: vehicle._id,
      //     plate: vehicle.plate,
      //     model: vehicle.model,
      //     type: vehicle.type,
      //     provider: vehicle.provider,
      //     capacity: vehicle.capacity,
      //   }));
      //   vehicles.value = mappedVehicles;
      // }
      vehicles.value = response.data;
    } catch (err: unknown) {
      console.error('Error al obtener vehículos:', err);
      error.value = 'Error fetching vehicles';
    } finally {
      loading.value = false;
    }
  };

  const getProcessIncidents = async (params: any) => {
    try {
      const response = await fetchProcess(params);
      incidents.value = response.data.data;
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const getHistoryIndicents = async (id: string) => {
    try {
      const response = await fecthHistoryIncidents(id);
      historyIncidents.value = response?.data?.incident_type ?? [];
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  return {
    userInfo,
    loading,
    error,
    services,
    kpi,
    drivers,
    vehicles,
    incidents,
    historyIncidents,
    selectedDriver,
    selectedVehicle,
    selectedDriverId,
    selectedVehicleId,
    getServices,
    getIndicators,
    getDriversByProvider,
    getVehiclesByProvider,
    getProcessIncidents,
    getHistoryIndicents,
    confirmAssignments,
    handleUnitAssignment,
    handleCreateReport,
  };
});
