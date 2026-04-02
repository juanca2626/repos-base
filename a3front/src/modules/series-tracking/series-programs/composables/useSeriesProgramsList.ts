import { computed, h, inject, onMounted, type Ref, ref, watch } from 'vue';
import { Modal } from 'ant-design-vue';
import { ExclamationCircleOutlined } from '@ant-design/icons-vue';
import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import requestAurora from '@/utils/requestAurora';
import { storeToRefs } from 'pinia';
import { seriesProgramsStore } from '@/modules/series-tracking/series-programs/store/seriesPrograms.store';
import { emit } from '@/modules/negotiations/api/eventBus';
import type { SeriesProgramsResponseInterface } from '../interfaces/series-programs-response.interface';

export const useSeriesProgramsList = (props: { filters: FilterDatesInterface }) => {
  const [modal, contextHolder] = Modal.useModal();
  const expandedRowKeys = ref<string[]>([]);
  const selectedDepartureId = ref<string | null>(null);
  const selectedClientId = ref<string | null>(null);

  const mapItems = (items: any[] = []) => {
    return items.map((it: any) => ({
      ...it,
      program: it?.departureProgram?.program?.name || '',
      client: it?.client?.name || '',
      specialist: it?.user?.name || '', // o user_name si usas otro campo
      tickets_mapi: it?.ticket_mapi || '',
      observations: it?.observation || '',
      nro_pax: it?.qty_passengers ?? 0,
    }));
  };
  interface InjectedProps {
    isLoading: Ref<boolean>;
  }

  const injectedProps: InjectedProps = inject('injectedProps') || {
    isLoading: ref(false),
  };

  const { isLoading } = injectedProps;

  // Tabla PADRE (departures)
  const departureColumns = [
    { title: 'Salida', dataIndex: 'departure', key: 'departure', align: 'left' },
    { title: 'Total Pax', dataIndex: 'total_pax', key: 'total_pax', align: 'center', width: 120 },
  ];

  // Tabla HIJA (items)
  const itemColumns = [
    { title: 'N° File', dataIndex: 'file', key: 'file', align: 'left', width: 100 },
    { title: 'Nombre Pax', dataIndex: 'name_pax', key: 'name_pax', align: 'center', width: 250 },
    { title: 'Programa', dataIndex: 'program', key: 'program', align: 'center', width: 250 },
    {
      title: 'N° de Pax',
      dataIndex: 'qty_passengers',
      key: 'qty_passengers',
      align: 'center',
      width: 90,
    },
    { title: 'Cliente', dataIndex: 'client', key: 'client', align: 'center', width: 250 },
    { title: 'Especialista', dataIndex: 'user', key: 'user', align: 'center', width: 250 },
    {
      title: 'Entradas Mapi',
      dataIndex: 'ticket_mapi',
      key: 'ticket_mapi',
      align: 'center',
      width: 180,
    },
    {
      title: 'Observaciones',
      dataIndex: 'observation',
      key: 'observation',
      align: 'center',
      width: 350,
    },
    { title: 'Opciones', dataIndex: 'options', key: 'options', align: 'center', width: 120 },
  ];

  // Grupos = dataSource de tabla padre
  const groups = ref<any[]>([]);

  const filteredGroups = computed(() => {
    let filtered = groups.value;

    if (selectedDepartureId.value) {
      filtered = filtered.filter((group) => departureRowKey(group) === selectedDepartureId.value);
    }

    if (selectedClientId.value) {
      filtered = filtered
        .map((group) => {
          const filteredItems = group.items.filter(
            (item: any) => item.client_id === selectedClientId.value
          );
          if (filteredItems.length > 0) {
            return { ...group, items: filteredItems };
          }
          return null;
        })
        .filter((group) => group !== null);
    }

    return filtered;
  });

  const departureOptions = computed(() =>
    groups.value.map((group) => ({
      value: departureRowKey(group),
      label: `Salida ${group.departure.name}`,
    }))
  );

  const clientOptions = computed(() => {
    const clients = new Map();
    groups.value.forEach((group) => {
      group.items.forEach((item: any) => {
        if (item.client) {
          clients.set(item.client.id, item.client.name);
        }
      });
    });
    return Array.from(clients, ([id, name]) => ({ value: id, label: name }));
  });

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const cancelDialog = ref({ disabled: false });

  const fetchTrackingGrouped = async (page = 1, pageSize = 10) => {
    isLoading.value = true;
    try {
      const response = await requestAurora.get('/api/series/tracking-controls', {
        params: {
          page,
          per_page: pageSize,
        },
      });

      if (!response.data?.success) {
        groups.value = [];
        return;
      }

      // Laravel paginator dentro de data
      const paginator = response.data;

      groups.value = (paginator?.data || []).map((group: any) => {
        const mapped = mapItems(group?.items || []);
        return {
          ...group,
          items: group?.items || [],
          total_pax: mapped.reduce((sum, it) => sum + (Number(it?.nro_pax) || 0), 0),
        };
      });
    } catch (error: any) {
      console.error('Error fetching series tracking controls:', error);
      groups.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  // Opciones: delete (ejemplo). Ajusta endpoint real
  const showPromiseConfirm = (id: string) => {
    modal.confirm({
      title: '¿Quieres eliminar el registro?',
      icon: h(ExclamationCircleOutlined),
      content: 'Al hacer clic en el botón Eliminar, se eliminará el registro',
      okText: 'Eliminar',
      cancelText: 'Cancelar',
      okType: 'primary',
      keyboard: false,
      cancelButtonProps: cancelDialog.value,
      async onOk() {
        try {
          cancelDialog.value.disabled = true;
          await requestAurora.delete(`/api/series/tracking-controls/${id}`);
          await fetchTrackingGrouped(pagination.value.current, pagination.value.pageSize);
        } finally {
          cancelDialog.value.disabled = false;
        }
      },
    });
  };

  const editSeriesProgram = (record: SeriesProgramsResponseInterface) => {
    emit('editSeriesProgram', record);
  };

  // row-key para tabla padre
  const departureRowKey = (record: any) => record?.departure?.id ?? record?.items?.[0]?.id;

  const toggleAllRows = () => {
    if (expandedRowKeys.value.length === groups.value.length) {
      expandedRowKeys.value = [];
    } else {
      expandedRowKeys.value = groups.value.map((group) => departureRowKey(group));
    }
  };

  const store = seriesProgramsStore();

  const { filtersList } = storeToRefs(store);
  watch(
    () => [props.filters, filtersList.value],
    () => {
      fetchTrackingGrouped();
    },
    { deep: true }
  );

  onMounted(() => fetchTrackingGrouped());

  return {
    departureColumns,
    itemColumns,
    isLoading,
    contextHolder,
    showPromiseConfirm,
    departureRowKey,
    mapItems,
    editSeriesProgram,
    expandedRowKeys,
    toggleAllRows,
    selectedDepartureId,
    filteredGroups,
    departureOptions,
    selectedClientId,
    clientOptions,
  };
};
