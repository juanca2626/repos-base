//useTransportConfiguratorList.ts
import { ref, h, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { notification } from 'ant-design-vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { useTransportConfiguratorStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorStore';
import { useTransportConfiguratorSettingStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorSettingStore';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  UnitTransportConfigurator,
  Location,
} from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';
import ColumnTitle from '@/components/global/ColumnTitleComponent.vue';
import { useTransportConfiguratorFilterStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorFilter.store';

export const useTransportConfiguratorList = () => {
  // Acceder al store de configuraciones de transporte para el manejo principal
  const transportConfiguratorStore = useTransportConfiguratorStore();
  // Acceso a los datos de unidades desde el store existente
  const { units } = storeToRefs(transportConfiguratorStore);

  // Acceder al nuevo store de configuración de transporte para manejar los detalles
  const transportConfiguratorSettingStore = useTransportConfiguratorSettingStore();
  // Estados y acciones del nuevo store
  const { expandedRowKeys } = transportConfiguratorSettingStore;

  // Acceder al store de filtros para control de paginación y actualización
  const transportConfiguratorFilterStore = useTransportConfiguratorFilterStore();
  const { lastUpdated, page, pageSize } = storeToRefs(transportConfiguratorFilterStore);

  const isLoading = ref(false);
  const total = ref(0);
  const expandedRows = ref(new Set<number>());

  // Configuración de la paginación para la tabla de datos
  const pagination = computed<PaginationInterface>(() => ({
    current: page.value,
    pageSize: pageSize.value,
    total: total.value,
  }));

  // Definición de las columnas de la tabla
  const columns = ref([
    {
      title: () => h(ColumnTitle, { title: 'ESTADO', subtitle: '' }),
      dataIndex: 'status',
      key: 'status',
      width: 100,
      align: 'center',
    },
    {
      title: () => h(ColumnTitle, { title: 'CODIGO', subtitle: '' }),
      dataIndex: 'code',
      key: 'code',
      width: 70,
      align: 'center',
    },
    {
      title: () => h(ColumnTitle, { title: 'TIPO DE UNIDAD', subtitle: '' }),
      dataIndex: 'name',
      key: 'name',
      width: 120,
    },
    {
      title: () => h(ColumnTitle, { title: 'MALETERO', subtitle: 'Segundo uso' }),
      dataIndex: 'is_trunk',
      key: 'is_trunk',
      width: 80,
      align: 'center',
    },
    {
      title: () =>
        h(ColumnTitle, {
          title: 'SEDES DE ACTIVIDADES',
          subtitle: '(Las cantidades es en relación al ultimo periodo configurado)',
        }),
      dataIndex: 'locations',
      key: 'locations',
      width: 400,
      align: 'center',
      ellipsis: true,
    },
    {
      title: () => h(ColumnTitle, { title: 'ACCIONES', subtitle: '' }),
      dataIndex: 'actions',
      key: 'actions',
      width: 120,
      align: 'center',
    },
  ]);

  // Función para obtener datos actualizados de la API cuando se monta el componente
  const fetchData = async () => {
    isLoading.value = true;
    try {
      const response = await transportConfiguratorFilterStore.getData();
      console.log('LISTADO API Response:', response.data);
      const fetchedData = response.data.map((unit) => ({
        id: unit.id,
        code: unit.code,
        name: unit.name,
        status: unit.status === 1,
        is_trunk: unit.is_trunk === 1,
        locations: unit.locations
          ? unit.locations.map((loc) => ({
              id: loc.id,
              country_id: loc.country_id,
              state_id: loc.state_id,
              city_id: loc.city_id,
              zone_id: loc.zone_id,
              display_name: loc.display_name,
              quantity: loc.quantity,
            }))
          : [], // Asegúrate de que siempre sea un array
        created_at: unit.created_at,
        isValid: true, // Suponemos que todas las unidades obtenidas son válidas por ahora
      }));
      transportConfiguratorStore.units = fetchedData; // Reemplaza los datos antiguos completamente
      pagination.value = {
        current: response.pagination.current_page,
        pageSize: response.pagination.per_page,
      };
      total.value = response.pagination.total;
    } catch (error) {
      console.error('Error fetching data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  // Función para obtener la clase de un tag de sede basado en su estado y cantidad
  const getSedeTagClass = (sede: Location, estadoGeneral: string) => {
    if (estadoGeneral === 'Desactivado' && sede.quantity === 0) {
      return 'pending-light';
    } else if (estadoGeneral === 'Desactivado') {
      return 'disabled-dark';
    } else {
      if (sede.quantity > 0) {
        return 'inprogress-light';
      } else {
        return 'pending-light';
      }
    }
  };

  const updateUnitAndSave = async (unit: UnitTransportConfigurator) => {
    try {
      // Encuentra el índice de la unidad en el store
      const index = units.value.findIndex((u) => u.id === unit.id);

      // Actualiza la unidad en el store
      transportConfiguratorStore.updateUnit(unit, index);

      // Envía los cambios a la API
      const response = await transportConfiguratorStore.saveUnitsToApi([unit]);

      if (response.success) {
        notification.success({
          message: 'Éxito',
          description: 'La unidad se actualizó correctamente.',
        });
      } else {
        notification.error({
          message: 'Error',
          description: 'No se pudo actualizar la unidad.',
        });
      }
    } catch (error) {
      console.error('Error al guardar el cambio del switch:', error);
      notification.error({
        message: 'Error',
        description: 'Ocurrió un error al actualizar la unidad.',
      });
    }
  };

  // Función para manejar el evento de ver más información sobre una unidad
  const handleViewMore = (record: UnitTransportConfigurator) => {
    console.log('Ver más:', record);
  };

  const handleDeleteDetail = async (records) => {
    try {
      const units_settings_details_ids = [];
      records.forEach((record) => {
        units_settings_details_ids.push(record.setting_detail_id);
      });
      await supportApi.post('unit-settings/setting-details/delete-multiple', {
        ids: units_settings_details_ids,
      });
    } catch (error) {
      console.error('Error deleting detail:', error);
    }
  };

  const handleDeleteRow = async (record) => {
    try {
      await supportApi.post(`units/delete-multiple`, { ids: [record.id] });
      fetchData();
    } catch (error) {
      console.error('Error al eliminar la unidad:', error);
    }
  };

  const handleDelete = async (records) => {
    try {
      const units_ids = [];
      records.forEach((record) => {
        units_ids.push(record.id);
      });
      await supportApi.post(`units/delete-multiple`, { ids: units_ids });
      fetchData();
    } catch (error) {
      console.error('Error al eliminar la unidad:', error);
    }
  };

  const handleMore = (record: UnitTransportConfigurator) => {
    console.log('Ver más:', record);
  };

  const handleEdit = (record: UnitTransportConfigurator) => {
    const formattedLocationsBySelect = record.locations.map((location: Location) => ({
      value: [location.country_id, location.state_id, location.city_id, location.zone_id]
        .filter((id) => id !== null && id !== undefined)
        .join(','), // Une los valores con comas
    }));

    const formattedLocations = formattedLocationsBySelect.map(
      (location: Location) => location.value
    );
    const unitToEdit = {
      ...record,
      locations: formattedLocations,
    };
    transportConfiguratorStore.openDrawer(unitToEdit);
  };

  const onChange = (page: number, perSize: number) => {
    transportConfiguratorFilterStore.updatePagination(page, perSize);
  };
  return {
    isLoading,
    data: units,
    pagination,
    columns,
    expandedRows,
    expandedRowKeys,
    lastUpdated,
    getSedeTagClass,
    handleViewMore,
    handleEdit,
    handleDeleteRow,
    handleDelete,
    handleMore,
    onChange,
    fetchData,
    handleDeleteDetail,
    updateUnitAndSave,
  };
};
