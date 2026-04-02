import { onMounted, reactive, ref, watch } from 'vue';
import type { FormInstance, Rule } from 'ant-design-vue';
import type { FormSeriesProgramsInterface } from '../interfaces/form-series-programs.interface';
import type { SeriesProgramsResponseInterface } from '../interfaces/series-programs-response.interface';
import requestAurora from '@/utils/requestAurora';

import moment from 'moment/moment';
import { handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { seriesProgramsStore } from '@/modules/series-tracking/series-programs/store/seriesPrograms.store';
import { on } from '@/modules/negotiations/api/eventBus';

type EmitType = (
  event: 'handlerShowDrawer' | 'updateFilters',
  ...args: (string | number)[]
) => void;

interface SeriesProgramFormPropsInterface {
  modelValue: string | number;
  label: string;
  placeholder: string[];
  format: string;
  showDrawer: boolean;
}

export const useSeriesProgramsForm = (props: SeriesProgramFormPropsInterface, emit: EmitType) => {
  const formRef = ref<FormInstance>();
  const isLoading = ref(false);
  const formDrawer = ref<boolean>(props.showDrawer);

  const formState = reactive<FormSeriesProgramsInterface>({
    salidaId: null,
    programaId: null,
    fileNumber: '',
    passengersCount: null,
    groupName: '',
    clienteId: null,
    especialistaId: null,
    machuPicchuEntry: '',
    observations: '',
    method: 'POST',
  });

  const departures = ref([]);
  const programs = ref([]);
  const clients = ref([]);
  const specialists = ref([]);
  const loadingClients = ref(false);
  const loadingSpecialists = ref(false);
  let clientDebounce: number | undefined;
  let specialistsDebounce: number | undefined;

  const rules: Record<string, Rule[]> = {
    salidaId: [{ required: true, message: 'Por favor seleccione una salida' }],
    programaId: [{ required: true, message: 'Por favor seleccione un programa' }],
    fileNumber: [{ required: true, message: 'Por favor ingrese el número de file' }],
    passengersCount: [{ required: true, message: 'Por favor ingrese la cantidad de pasajeros' }],
    groupName: [{ required: true, message: 'Por favor ingrese el nombre del grupo o pasajero' }],
    clienteId: [{ required: true, message: 'Por favor seleccione un cliente' }],
    especialistaId: [{ required: true, message: 'Por favor seleccione un especialista' }],
  };

  const fetchDepartures = async () => {
    try {
      const { data } = await requestAurora.get('api/series/departures');
      departures.value = data.data.map((item: any) => ({ value: item.id, label: item.name }));
    } catch (error) {
      console.error('Error fetching departures:', error);
    }
  };

  const fetchPrograms = async (salidaId: number) => {
    try {
      const { data } = await requestAurora.get(
        `api/series/departure-programs?serie_departure_id=${salidaId}`
      );
      programs.value = data.data.map((item: any) => ({
        value: item.id,
        label: item.name + ' - ' + moment(item.date).format('DD/MM/YYYY'),
      }));
    } catch (error) {
      console.error('Error fetching programs:', error);
    }
  };

  const fetchClients = async (query: string) => {
    if (!query) {
      clients.value = [];
      return;
    }
    loadingClients.value = true;
    try {
      const { data } = await requestAurora.get(
        `api/clients/selectBox/by/name?query=${query}&limit=50`
      );
      clients.value = data.data.map((item: any) => ({ value: item.id, label: item.name }));
    } catch (error) {
      console.error('Error fetching clients:', error);
      clients.value = [];
    } finally {
      loadingClients.value = false;
    }
  };

  const handleClientSearch = (query: string) => {
    if (clientDebounce) clearTimeout(clientDebounce);
    clientDebounce = window.setTimeout(() => {
      fetchClients(query);
    }, 500);
  };

  const fetchSpecialists = async (query: string) => {
    if (!query) {
      specialists.value = [];
      return;
    }
    loadingSpecialists.value = true;
    try {
      const { data } = await requestAurora.get(
        `api/user/search/executive/filter?queryCustom=${query}`
      );
      specialists.value = data.data.map((item: any) => ({
        value: item.id,
        label: item.code + ' - ' + item.name,
      }));
    } catch (error) {
      console.error('Error fetching specialists:', error);
      specialists.value = [];
    } finally {
      loadingSpecialists.value = false;
    }
  };

  const handleSpecialistsSearch = (query: string) => {
    if (specialistsDebounce) clearTimeout(specialistsDebounce);
    specialistsDebounce = window.setTimeout(() => {
      fetchSpecialists(query);
    }, 500);
  };

  const handleDeparturesChange = (salidaId: number) => {
    formState.programaId = null;
    programs.value = [];
    if (salidaId) {
      fetchPrograms(salidaId);
    }
  };

  const saveForm = async () => {
    await formRef.value?.validate();
    try {
      isLoading.value = true;
      const store = seriesProgramsStore();
      const payload = {
        serie_departure_program_id: formState.programaId,
        file: formState.fileNumber,
        passenger_group_name: formState.groupName,
        qty_passengers: formState.passengersCount,
        client_id: formState.clienteId,
        user_id: formState.especialistaId,
        ticket_mapi: formState.machuPicchuEntry,
        observation: formState.observations,
      };

      let request;
      const url = 'api/series/tracking-controls';

      if (formState.method === 'PUT') {
        request = requestAurora.put(`${url}/${formState.id}`, payload);
      } else {
        request = requestAurora.post(url, payload);
      }
      const { response } = await request;
      handleSuccessResponse(response);
      cancelForm();
      store.updateFilters({ from: '', to: '' });
    } catch (error) {
      console.log('Error de validación o guardado:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const cancelForm = () => {
    formRef.value?.resetFields();
    close();
  };

  const close = () => {
    formDrawer.value = false;
    emit('handlerShowDrawer', false);
  };

  on('editSeriesProgram', (item: SeriesProgramsResponseInterface) => {
    formState.id = item.id;
    formState.salidaId = item.departure_program.serie_departure_id;
    formState.programaId = item.serie_departure_program_id;
    formState.fileNumber = String(item.file);
    formState.passengersCount = item.qty_passengers;
    formState.groupName = item.passenger_group_name;
    formState.clienteId = item.client_id;
    formState.especialistaId = item.user_id;
    formState.machuPicchuEntry = item.ticket_mapi;
    formState.observations = item.observation;
    formState.method = 'PUT';

    if (item.departure_program.serie_departure_id) {
      fetchPrograms(item.departure_program.serie_departure_id);
    }
    console.log(item);
    clients.value = [{ value: item.client_id, label: item.client }];
    specialists.value = [{ value: item.user.id, label: item.user.name }];

    formDrawer.value = true;
  });

  watch(
    () => props.showDrawer,
    (newVal) => {
      formDrawer.value = newVal;
    }
  );

  onMounted(() => {
    fetchDepartures();
  });

  return {
    formRef,
    formState,
    rules,
    departures,
    programs,
    clients,
    specialists,
    loadingClients,
    loadingSpecialists,
    handleClientSearch,
    handleSpecialistsSearch,
    handleDeparturesChange,
    saveForm,
    cancelForm,
    isLoading,
    formDrawer,
  };
};
