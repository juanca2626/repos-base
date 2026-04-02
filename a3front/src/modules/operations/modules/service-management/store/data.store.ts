import { defineStore } from 'pinia';
import { ref } from 'vue';
import { notification } from 'ant-design-vue';

import { useTableStore } from '@operations/modules/service-management/store/table.store';
import { useFormStore } from '@operations/modules/service-management/store/form.store';
import { useSelectionStore } from '@operations/shared/stores/selection.store';

import {
  fetchPreferredProviders,
  fetchProviderByTerm,
  fetchServices,
  fetchIndicators,
  fetchAdditionals,
  nestServices,
  unnestServices,
  assignment,
  updateAssignment as updateAssignmentApi,
  removeAssignment as removeAssignmentApi,
  createServiceOrder as createServiceOrderApi,
  createReturnApi,
  fetchNotesByFile,
  fetchTrp,
  fetchZones,
} from '@operations/modules/service-management/api/serviceManagementApi';
import dayjs from 'dayjs';
import utc from 'dayjs/plugin/utc';
import { useRadioStore } from '@/modules/operations/modules/service-management/store/radio.store';
import { useDateStore } from './date.store';
import type { SimplifiedNote } from '../../providers/interfaces/note';
import { useAdditionalServiceStore } from './additional-service.store';
import { useBooleans } from '@/composables/useBooleans';
dayjs.extend(utc);

const { setValue } = useBooleans();

export const useDataStore = defineStore('dataStore', () => {
  console.log('✅ data.store.ts CARGADO');

  const loading = ref(false);
  const error = ref<string | null>(null);
  const loadingModal = ref(false);
  const errorModal = ref<string | null>(null);

  const tableStore = useTableStore();
  const formStore = useFormStore();
  const radioStore = useRadioStore();
  const selectionStore = useSelectionStore();
  const dateStore = useDateStore();
  const addServiceStore = useAdditionalServiceStore();

  // const modalStore = useModalStore();
  // const { total, loading, error, meta } = storeToRefs(tableStore);

  const ZONES_CACHE_KEY = 'app_zones_cache';

  const services = ref<any[]>([]);

  //TODO: Asignación de proveedores
  const providers = ref<any[]>([]);
  const preferents = ref<any[]>([]);
  const sendServiceOrder = ref(false);
  const zones = ref<any[]>([]);
  const configTRP = ref<any[]>([]);

  const additionals = ref<any[]>([]);

  const notes = ref<{
    forFile: SimplifiedNote[];
    forService: SimplifiedNote[];
  }>({
    forFile: [],
    forService: [],
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
          const uniqueLangs = [...new Set(v.files.map((file: any) => file.file.lang))];

          const matchedFlights = Array.isArray(v?.matched_flights) ? v.matched_flights : [];

          const matchedHotels = Array.isArray(v?.matched_hotels) ? v.matched_hotels : [];

          let mappedData: any = {
            id: v._id,
            equivalence_id: v.equivalence_id,
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
            requires_carrier: v.requires_carrier,
            requires_guide: v.requires_guide,
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
              client: '--',
              lang: uniqueLangs, // Asignamos los valores únicos de lang
              is_vip: v.files?.[0]?.file?.is_vip,
              vip: v.files?.[0]?.file?.vip,
            };
          }
          // console.log('getPreferentsGUI ~ search:', search);
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
      console.log('🚀 ~ getIndicators ~ response:', response);
      kpi.value = response.data;
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const getZones = async () => {
    try {
      // Verificar si hay zones en localStorage
      const cachedZones = localStorage.getItem(ZONES_CACHE_KEY);

      if (cachedZones) {
        zones.value = JSON.parse(cachedZones);
        return;
      }

      // Si no hay cache, llamar al API
      const response = await fetchZones();
      zones.value = response.data;

      // Guardar en localStorage
      localStorage.setItem(ZONES_CACHE_KEY, JSON.stringify(response.data));
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const getTrp = async (payload: any) => {
    loading.value = true;

    try {
      const response = await fetchTrp(payload);
      const data = Array.isArray(response?.data) ? response.data : [];

      configTRP.value = data.map((trp: any) => ({
        type: trp.vehicle,
        paxs: trp.max,
      }));
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
      configTRP.value = []; // Asegurar array en caso de error
    } finally {
      loading.value = false;
    }
  };

  // Obtiene información de guías preferentes / bloqueados
  const getPreferentsGUI = async (params: any) => {
    if (params.length > 1) return;
    else {
      loadingModal.value = true;
      errorModal.value = null;

      //TODO: Del service encontrar el HEADQUARTER y GUIDELINE
      // service
      const { files, datetime_start, datetime_end, search } = params[0];

      try {
        const params: any = {
          type: 'gui',
          preferents: !search ? true : false,
          client: files?.[0]?.file.client.code,
          headquarter: 'lim',
          guideline: 'rep',
          datetime_start: convertDateFormat(datetime_start),
          datetime_end: convertDateFormat(datetime_end),
        };

        const response = await fetchPreferredProviders(params);

        console.log('object ~ getPreferentsGUI ~ response:', response);

        const fetchedData = response.data.map((v: any) => {
          const isBlockedValid =
            v.blocked && typeof v.blocked === 'object' && Object.keys(v.blocked).length > 0;
          const mappedData = {
            state: isBlockedValid ? 'error' : 'success',
            ...v,
          };
          return mappedData;
        });

        providers.value = fetchedData;
        preferents.value = fetchedData;
      } catch (err: unknown) {
        console.error('Error al obtener datos:', err);
        errorModal.value = 'Error fetching data';
      } finally {
        loadingModal.value = false;
      }
    }
  };

  const getGuidesByTerm = async (params: any) => {
    loadingModal.value = true;
    errorModal.value = null;

    try {
      const response = await fetchProviderByTerm('gui', params);
      const mapArray = adaptAndMerge(preferents.value, response.data);
      const fetchedData = mapArray.map((v: any) => {
        const isBlockedValid =
          v.blocked && typeof v.blocked === 'object' && Object.keys(v.blocked).length > 0;
        const mappedData = {
          state: isBlockedValid ? 'error' : 'success',
          ...v,
        };
        return mappedData;
      });

      providers.value = fetchedData;
      console.log('🚀 ~ getGuidesByTerm ~ providers.value:', providers.value);
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      errorModal.value = 'Error fetching data';
    } finally {
      loadingModal.value = false;
    }
  };

  const getCarriersByTerm = async (params: any) => {
    loadingModal.value = true;
    errorModal.value = null;

    try {
      const response = await fetchProviderByTerm('trp', params);
      const mapArray = adaptAndMerge(preferents.value, response.data);
      const fetchedData = mapArray.map((v: any) => {
        const isBlockedValid =
          v.blocked && typeof v.blocked === 'object' && Object.keys(v.blocked).length > 0;
        const mappedData = {
          state: isBlockedValid ? 'error' : 'success',
          ...v,
        };
        return mappedData;
      });

      providers.value = fetchedData;
      console.log('🚀 ~ getGuidesByTerm ~ providers.value:', providers.value);
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      errorModal.value = 'Error fetching data';
    } finally {
      loadingModal.value = false;
    }
  };

  const adaptAndMerge = (api1: any, api2: any): any[] => {
    const api1Map = new Map<string, any>(api1.map((item: any) => [item.code, item]));

    return api2.map((item: any) => {
      const api1Match = api1Map.get(item.code);

      // Eliminar Proxy si existe en `blocked`
      const blocked = api1Match?.blocked
        ? JSON.parse(JSON.stringify(api1Match.blocked))
        : undefined;
      console.log('🚀 ~ returnapi2.map ~ blocked:', blocked);

      return {
        // Datos de la API 1 si existen, de lo contrario, de la API 2
        _id: api1Match?._id || item._id,
        code: item.code,
        fullname: api1Match?.fullname || item.fullname,
        profiles: api1Match?.profiles || item.profiles,
        preferent: api1Match?.preferent || false,
        blocked,
        // Campos específicos de la API 2
        contract: item.contract,
        type: item.type,
      };
    });
  };

  const getCarriers = async (params: any) => {
    console.log('🚀 getCarriers', params);

    loadingModal.value = true;
    errorModal.value = null;

    try {
      providers.value = [];
    } catch (err: unknown) {
      console.error('Error al obtener datos:', err);
      errorModal.value = 'Error fetching data';
    } finally {
      loadingModal.value = false;
    }
  };

  const getAdditionals = async (operational_service_id: string) => {
    try {
      const response = await fetchAdditionals(operational_service_id);

      const isCarrierRequired = !!addServiceStore.is_required_carrier;
      const isGuideRequired = !!addServiceStore.is_required_guide;

      const fetchedData = response.data.map((v: any, index: number) => {
        return {
          ...v,

          // Asegurar que estos flags siempre existan como booleanos
          trp: typeof v.trp === 'boolean' ? v.trp : false,
          gui: typeof v.gui === 'boolean' ? v.gui : false,

          editable: index !== 0,
        };
      });

      // Si solo hay 1 ítem (el original), agregamos uno nuevo editable por defecto
      if (fetchedData.length === 1) {
        const additional = {
          editable: true,
          partial_paxs: 0,
          trp: isCarrierRequired && !isGuideRequired ? true : false,
          gui: isGuideRequired && !isCarrierRequired ? true : false,
        };

        fetchedData.push(additional);
      }

      additionals.value = fetchedData;
      return fetchedData;
    } catch (err: unknown) {
      console.error('❌ Error al obtener datos:', err);
      error.value = 'Error fetching data';
      return [];
    } finally {
      loading.value = false;
    }
  };

  const getNotes = async (file: string, equivalence_id: number | string) => {
    try {
      const response = await fetchNotesByFile(file);

      const payload = response.data || {};

      const matchedService = payload.for_service?.find(
        (v: any) => v.service_id === Number(equivalence_id)
      );

      const notesForService = matchedService?.notes || [];

      const filteredNotes = {
        forFile: payload.for_file || [],
        forService: notesForService,
      };

      notes.value = filteredNotes;
    } catch (err) {
      console.error('Error al obtener datos:', err);
      error.value = 'Error fetching data';
    } finally {
      loading.value = false;
    }
  };

  const handleProvidersAssignment = async (process: string, operational_services: any) => {
    // const items = selectionStore.getItemsByType('providerAssignment');

    const configTRPItem =
      process === 'trpAssignment' ? selectionStore.getItemsByType('configTRP') : [];

    // Asegurarte de que siempre trabajas con un array
    const operationalServicesArray = Array.isArray(operational_services)
      ? operational_services
      : [operational_services];

    // Extraer solo los `_id` de cada objeto
    const operational_services_id = operationalServicesArray.map((item: any) => item.id);

    // Crear el objeto parseProvider según el proceso
    let parseProvider: any;

    const parseProviders = selectionStore.selectedItems.map(({ item }) => ({
      code: item.code,
      profile: item.profiles[0], // Toma el primer perfil del array profiles
    }));

    switch (process) {
      case 'guiAssignment':
        parseProvider = parseProviders;
        break;

      case 'trpAssignment':
        parseProvider = [
          {
            code: radioStore.provider_code[0],
            vehicle_type: configTRPItem[0]?.type || radioStore.vehicle_type || '',
          },
        ];
        break;

      default:
        console.log('Proceso no reconocido:', process);
        return;
    }

    // Asegurar que sea un array
    const customProvider = Array.isArray(parseProvider) ? parseProvider : [parseProvider];

    if (!customProvider?.[0]?.code) {
      notification.error({
        message: 'Error en la asignación',
        description: 'No seleccionaste ningún proveedor.',
        duration: 4,
      });
      return;
    }

    // Crear el objeto payload
    const payload = {
      operational_services_id,
      providers: customProvider,
      service_order: sendServiceOrder.value,
    };

    try {
      await assignment(payload);

      // Notificar éxito
      notification.success({
        message: 'Asignación exitosa',
        description: 'Los proveedores fueron asignados correctamente.',
      });

      // Actualizar la lista de servicios
      await formStore.fetchServicesWithParams();
      setValue('isDeselect', true);
    } catch (error: unknown) {
      console.error('Error en la asignación de proveedores:', error);

      // Notificar error
      notification.error({
        message: 'Error en la asignación',
        description: 'Hubo un problema al asignar los proveedores.',
      });
    } finally {
      loading.value = false;
      selectionStore.selectedItems = [];
      sendServiceOrder.value = false;
      providers.value = [];
    }
  };

  const updateAssignment = async (process: string, data: any) => {
    // console.log('🚀 ~ updateAssignment ~ process:', process);
    // console.log('🚀 ~ updateAssignment ~ data:', data);
    try {
      const type = radioStore.vehicle_type;
      const validTypes = ['AUTO', 'VAN', 'SPC', 'SPL', 'MNB', 'BUS'];

      if (!type) {
        notification.warning({
          message: 'Notificación',
          description: 'Debe seleccionarse un tipo de vehículo.',
        });
        return;
      }

      if (!validTypes.includes(type)) {
        notification.warning({
          message: 'Notificación',
          description: 'El tipo de vehículo seleccionado no es válido.',
        });
        return;
      }

      const { assignment } = data;

      const { _id: assignmentId, provider } = assignment;
      await updateAssignmentApi(assignmentId, {
        providers: [{ code: provider.code, vehicle_type: type }],
      });
      notification.success({
        message: 'Notificación',
        description: 'El tipo de vehículo se actualizó correctamente.',
      });
      loading.value = false;
      selectionStore.selectedItems = [];
      formStore.fetchServicesWithParams();
      // modalStore.resetModal();
    } catch (error) {
      console.error('Error al actualizar el tipo de vehículo:', error);
      // notification.error({
      //   message: 'Error al actualizar el tipo de vehículo',
      //   description: 'Hubo un problema al actualizar el tipo de vehículo.',
      // });
    } finally {
      // loading.value = false;
      // selectionStore.selectedItems = [];
    }
  };

  const removeAssignment = async (process: string, data: any) => {
    try {
      const { _id: assignmentId } = data;
      await removeAssignmentApi(assignmentId);
      // notification.success({
      //   message: 'Asignación eliminada',
      //   description: 'La asignación se ha eliminado correctamente.',
      // });
      // selectionStore.selectedItems = [];
      formStore.fetchServicesWithParams();
    } catch (error) {
      console.error('Error al eliminar la asignación:', error);
      // notification.error({
      //   message: 'Error al eliminar la asignación',
      //   description: 'Hubo un problema al eliminar la asignación.',
      // });
    }
  };

  const convertDateFormat = (dateString: string): string => {
    // Convertir la fecha actual a un objeto Date
    const date = new Date(dateString);

    // Formatear la fecha al formato deseado: YYYY-MM-DDTHH:mm
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Meses van de 0-11, por eso se suma 1
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');

    return `${year}-${month}-${day}T${hours}:${minutes}`;
  };

  const handleNestServices = async (selectedItems: any[], data_trp: any) => {
    try {
      loading.value = true;

      const payload = {
        data_trp,
        operational_services_id: selectedItems.map((v) => String(v.id)),
      };

      await nestServices(payload);

      notification.success({
        message: 'Notificación',
        description: `Anidación de servicios realizada con éxito`,
      });

      formStore.fetchServicesWithParams();
    } catch (error: unknown) {
      console.error('Error en la asignación de proveedores:', error);

      notification.error({
        message: 'Error en la asignación',
        description: 'Hubo un problema al agrupar los servicios.',
      });
    } finally {
      loading.value = false;
      selectionStore.selectedItems = [];
    }
  };

  const handleUnnestServices = async (service: any, selectedItems: any[]) => {
    try {
      const operational_service_id = service.id;
      const equivalence_id = service.equivalence_id;

      const data_trp = {
        headquarter: service.service.city_in.code,
        client_code: service.client.code,
        date_in: service.datetime_start,
        service_type: service.service.type,
      };

      const files_id = selectedItems.map((v) => String(v.file._id));
      const payload = {
        operational_service_id,
        files_id,
        equivalence_id,
        data_trp,
      };

      await unnestServices(payload);

      notification.success({
        message: 'Notificación',
        description: `Desanidación de servicios realizada con éxito`,
      });

      formStore.fetchServicesWithParams();
    } catch (error: unknown) {
      console.error('Error en la asignación de proveedores:', error);

      // Notificar error
      notification.error({
        message: 'Error en la desasignación',
        description: 'Hubo un problema al desagrupar los servicios.',
      });
    } finally {
      loading.value = false;
      selectionStore.selectedItems = [];
    }
  };

  const updateServiceOrders = (
    serviceId: string,
    assignmentId: string,
    newServiceOrders: any[]
  ) => {
    const service = services.value.find((s) => s.id === serviceId);
    if (service) {
      // Buscar en gui
      const guiProvider = service.gui.find((g: any) => g._id === assignmentId);
      if (guiProvider) {
        guiProvider.service_orders = [...newServiceOrders];
        return;
      }

      // Buscar en trp
      const trpProvider = service.trp.find((t: any) => t._id === assignmentId);
      if (trpProvider) {
        trpProvider.service_orders = [...newServiceOrders];
      }
    }
  };

  // En handleOrderServices
  const handleOrderServices = async (selectedItems: any[]) => {
    try {
      loading.value = true;
      const unassignedItems = selectedItems.flatMap((item) =>
        [...item.gui, ...item.trp].filter((v) => v.service_orders.length === 0)
      );

      await Promise.all(
        unassignedItems.map(async (v) => {
          const { _id: assignmentId, assignment_type } = v;

          const response = await createServiceOrderApi({
            assignment_id: assignmentId,
            sent_by: 'YMM',
            assignment_type,
          });

          // Actualizar usando el método específico
          updateServiceOrders(v.service_id, assignmentId, [response]);

          notification.success({
            message: 'Orden de servicio enviada',
            description: `Se envió una orden de servicio a ${v.provider.code}`,
          });
        })
      );

      // Opcional: recargar todos los datos
      await formStore.fetchServicesWithParams();
    } catch (error: unknown) {
      console.error('Error en la asignación de proveedores:', error);
      notification.error({
        message: 'Error en el envío de órdenes de servicio',
        description: 'Hubo un problema en el envío.',
      });
    } finally {
      loading.value = false;
      selectionStore.selectedItems = [];
    }
  };

  /* INICIO: CREATE RETURN */
  // const formCreateReturn = ref({
  //   date01: dayjs(),
  //   date02: dayjs(),
  // });

  // const createReturnOld = async (process: string, operational_services: any) => {
  //   console.log('🚀 ~ createReturn ~ operational_services:', operational_services);
  //   console.log('🚀 ~ createReturn ~ process:', process);

  //   // Validar que ambos valores existan y sean válidos
  //   if (!formCreateReturn.value.date01 || !formCreateReturn.value.date02) {
  //     console.error('Faltan uno o ambos valores del formulario.');
  //     notification.error({
  //       message: 'Error en el formulario',
  //       description: 'Debe ingresar tanto la fecha como la hora.',
  //     });
  //     return;
  //   }

  //   const isDate01Valid = dayjs(formCreateReturn.value.date01).isValid();
  //   const isDate02Valid = dayjs(formCreateReturn.value.date02).isValid();

  //   if (!isDate01Valid || !isDate02Valid) {
  //     console.error('Uno o ambos valores del formulario no son válidos:', {
  //       date01: formCreateReturn.value.date01,
  //       date02: formCreateReturn.value.date02,
  //     });
  //     notification.error({
  //       message: 'Error en el formulario',
  //       description: 'La fecha y la hora deben ser válidas.',
  //     });
  //     return;
  //   }

  //   // Formatear los valores del formulario
  //   const formattedDate = formCreateReturn.value.date01.format('YYYY-MM-DD'); // Fecha en formato ISO
  //   console.log('🚀 ~ createReturn ~ formattedDate:', formattedDate);
  //   const formattedTime = formCreateReturn.value.date02.format('HH:mm'); // Hora en formato 24 horas
  //   console.log('🚀 ~ createReturn ~ formattedTime:', formattedTime);
  //   const formattedDatetime = `${formattedDate}T${formattedTime}`;
  //   console.log('🚀 ~ createReturn ~ formattedDatetime:', formattedDatetime);
  //   return;

  //   const items = selectionStore.getItemsByType('providerAssignment');
  //   const configTRPItem =
  //     process === 'trpAssignment' ? selectionStore.getItemsByType('configTRP') : [];
  //   let providers: any[] = [];

  //   switch (process) {
  //     case 'guiAssignment':
  //       providers = items.map((item: any) => ({
  //         code: item.code,
  //         profile: item.profiles?.[0] || 'unknown',
  //       }));
  //       break;

  //     case 'trpAssignment':
  //       providers = items.map((item: any) => ({
  //         code: item.code,
  //         vehicle_type: configTRPItem[0]?.type || '',
  //       }));
  //       break;

  //     default:
  //       console.log('otra cosa...');
  //       break;
  //   }

  //   // Asegurarte de que siempre trabajas con un array
  //   const operationalServicesArray = Array.isArray(operational_services)
  //     ? operational_services
  //     : [operational_services];

  //   // Extraer solo los `_id` de cada objeto
  //   const operational_services_id = operationalServicesArray.map((item: any) => item.id);
  //   // const plainProviders = JSON.parse(JSON.stringify(items));

  //   // Extraer el ID del servicio
  //   // const operational_services_id = [plainService.id];

  //   // Crear el objeto payload
  //   const payload = {
  //     operational_services_id,
  //     providers,
  //     service_order: sendServiceOrder.value, // Valor fijo según el formato deseado
  //   };

  //   try {
  //     const response = await assignment(payload);
  //     console.log('🚀 ~ handleGuideAssignment ~ response:', response);

  //     // Notificar éxito
  //     notification.success({
  //       message: 'Asignación exitosa',
  //       description: 'Los proveedores fueron asignados correctamente.',
  //     });

  //     // Llamada a fetchServicesWithParams
  //     formStore.fetchServicesWithParams();
  //   } catch (error: unknown) {
  //     console.error('Error en la asignación de proveedores:', error);

  //     // Notificar error
  //     notification.error({
  //       message: 'Error en la asignación',
  //       description: 'Hubo un problema al asignar los proveedores.',
  //     });
  //   } finally {
  //     loading.value = false;
  //     selectionStore.selectedItems = [];
  //   }
  // };
  /* FIN: CREATE RETURN */

  const createReturn = async (process: string, operational_services: any) => {
    if (process === 'createReturn') {
      try {
        // Validaciones
        if (!dateStore.date || !dateStore.time) {
          notification.error({
            message: 'Error',
            description: 'Debes seleccionar una fecha y hora para el retorno.',
          });
          return;
        }

        // Validar que la fecha sea válida (mayor al día actual)
        if (!dateStore.isValidDate) {
          notification.error({
            message: 'Error',
            description: 'La fecha seleccionada debe ser mayor al día actual.',
          });
          return;
        }

        const payload = {
          operational_service_id: operational_services.id,
          date: dateStore.formattedDate,
          time: dateStore.formattedTime,
        };

        await createReturnApi(payload);
        await formStore.fetchServicesWithParams();

        notification.success({
          message: 'Éxito',
          description: 'Fecha y hora de retorno guardadas correctamente.',
        });
      } catch (error) {
        console.log(error);
        notification.error({
          message: 'Error',
          description: 'Ocurrió un error al guardar el retorno.',
        });
      }
    }
  };

  const clearZonesCache = () => {
    localStorage.removeItem(ZONES_CACHE_KEY);
  };

  return {
    loading,
    error,
    loadingModal,
    errorModal,
    services,
    kpi,
    providers,
    sendServiceOrder,
    additionals,
    notes,
    zones,
    configTRP,
    getServices,
    getIndicators,
    getZones,
    getTrp,
    getPreferentsGUI,
    getGuidesByTerm,
    getCarriers,
    getCarriersByTerm,
    getAdditionals,
    getNotes,
    handleNestServices,
    handleUnnestServices,
    handleOrderServices,
    handleProvidersAssignment,
    updateAssignment,
    removeAssignment,
    createReturn,
    clearZonesCache,
  };
});
