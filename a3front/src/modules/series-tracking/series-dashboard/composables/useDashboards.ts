import { reactive, ref } from 'vue';
import requestAurora from '@/utils/requestAurora';
import moment from 'moment';
import type { FormSeriesProgramsInterface } from '@/modules/series/series-programs/interfaces/form-series-programs.interface';

const CLIENT_USER_TYPE = 'client';
const ADMIN_USER_TYPE = 'user';

const mapAdminData = (salida: any) => ({
  id: salida.departure.id,
  numero: salida.departure.name,
  total_pax: salida.items.reduce((total, item) => total + item.qty_passengers, 0),
  data: salida.items.map((item: any) => ({
    id: item.id,
    file: item.file,
    pax: item.qty_passengers,
    group: item.passenger_group_name,
    programa: item.departure_program.program.name,
    fechaSalida: moment(item.departure_program.date).format('DD/MM/YYYY'),
    client: item.client.name,
    specialist: item.user.name,
    status: item.ticket_mapi,
    obs: item.observation,
  })),
});

const mapClientData = (item: any) => ({
  id: item.departure.id,
  numero: item.departure.name,
  totalPax: item.total_qty_passengers,
  programs: item.programs || [],
});

export function useDashboards() {
  const dashboardsClient = ref<any[]>([]);
  const dashboards = ref<any[]>([]);
  const loading = ref(true);
  const error = ref<string | null>(null);
  const loadingDepartures = ref(false);
  const loadingPrograms = ref(false);
  const departures = ref([]);
  const programs = ref([]);
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

  const handleError = (err: any, message: string) => {
    console.error(message, err);
    dashboards.value = [];
    error.value = message;
  };

  const resetState = () => {
    loading.value = true;
    error.value = null;
    dashboards.value = [];
  };

  const fetchDepartures = async () => {
    try {
      loadingDepartures.value = true;
      const { data } = await requestAurora.get('api/series/departures');
      departures.value = data.data.map((item: any) => ({ value: item.id, label: item.name }));
    } catch (err) {
      console.error('Error fetching departures:', err);
    } finally {
      loadingDepartures.value = false;
    }
  };

  const fetchPrograms = async (salidaId: number) => {
    try {
      loadingPrograms.value = true;
      const { data } = await requestAurora.get(
        `api/series/departure-programs?serie_departure_id=${salidaId}`
      );
      programs.value = data.data.map((item: any) => ({
        value: item.id,
        label: `${item.name} - ${moment(item.date).format('DD/MM/YYYY')}`,
      }));
    } catch (err) {
      console.error('Error fetching programs:', err);
    } finally {
      loadingPrograms.value = false;
    }
  };

  const handleDeparturesChange = (salidaId: number) => {
    formState.programaId = null;
    programs.value = [];
    if (salidaId) {
      fetchPrograms(salidaId);
    }
  };

  const getApiConfig = (userType: string, programaId: number | null) => {
    if (userType === CLIENT_USER_TYPE) {
      return {
        url: `api/series/tracking-controls/client`,
        params: {},
        mapper: mapClientData,
      };
    }

    if (userType === ADMIN_USER_TYPE) {
      return {
        url: 'api/series/tracking-controls',
        params: programaId ? { serie_departure_program_id: programaId } : {},
        mapper: mapAdminData,
      };
    }

    throw new Error('Invalid user type');
  };

  const fetchTrackingClientControls = async () => {
    resetState();
    try {
      const { url, params, mapper } = getApiConfig('client', null);

      const { data } = await requestAurora.get(url, { params });
      const apiData: any[] = data?.data || [];

      dashboardsClient.value = apiData.map(mapper);
    } catch (err) {
      handleError(err, 'Failed to filter tracking controls');
    } finally {
      loading.value = false;
    }
  };

  const fetchTrackingControls = async (programaId: number | null = null) => {
    resetState();
    try {
      const { url, params, mapper } = getApiConfig('user', programaId);

      const { data } = await requestAurora.get(url, { params });
      const apiData: any[] = data?.data || [];

      dashboards.value = apiData.map(mapper);
    } catch (err) {
      handleError(err, 'Failed to filter tracking controls');
    } finally {
      loading.value = false;
    }
  };

  return {
    dashboards,
    dashboardsClient,
    loading,
    error,
    loadingDepartures,
    loadingPrograms,
    departures,
    formState,
    programs,
    fetchTrackingClientControls,
    fetchTrackingControls,
    fetchDepartures,
    fetchPrograms,
    handleDeparturesChange,
  };
}
