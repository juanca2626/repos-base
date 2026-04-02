//useTransportConfiguratorExpandedRow.ts
import { ref } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import dayjs from 'dayjs';
import { useTransportConfiguratorSettingStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorSettingStore';
import { useTransportConfiguratorSettingFilterStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorSettingFilter.store';
import { handleError as handleApiError } from '@/modules/negotiations/api/responseApi';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type {
  SettingDetail,
  SaveTransportConfigurationPayload,
  Location,
} from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator-setting.interface';

export const useTransportConfiguratorExpandedRow = () => {
  const store = useTransportConfiguratorSettingStore();
  const filterStore = useTransportConfiguratorSettingFilterStore();
  const formRef = ref<FormInstance>();

  // Estado locales
  const modeDrawwer_ = ref('add');
  const currentLocationId_ = ref(null);
  const currentLocation_ = ref({});
  const mode = ref('top');
  const isDrawerVisible = ref(false);
  const drawerTitle = ref('');
  const currentUnitType = ref('');
  const currentLocation = ref('');
  const currentUnitId = ref(null);
  const selectedPeriod = ref<[dayjs.Dayjs, dayjs.Dayjs]>([dayjs(), dayjs()]);
  const transferTypeConfigurations = ref<Record<string, TransferTypeConfiguration>>({
    Traslado: {
      type_transfer: 'TRANSFER',
      configurations: [] as SettingDetail[],
      active: 1,
      isVisible: true,
    },
    Tour: {
      type_transfer: 'TOUR',
      configurations: [] as SettingDetail[],
      active: 0,
      isVisible: true,
    },
  });
  const selectedLocations = ref<number[]>([]);
  const availableLocations = ref([]);
  const applyToOtherLocations = ref(false);
  const isLoading = ref(false);
  const settingDetails = ref<SettingDetail[]>([]); // Almacena todos los detalles de configuración obtenidos de la API

  // Función para cargar ubicaciones
  const loadAvailableLocations = (record) => {
    if (record && record.locations) {
      availableLocations.value = record.locations.map((location) => ({
        id: location.id,
        display_name: location.display_name,
        country_id: location.country_id,
        state_id: location.state_id,
        city_id: location.city_id,
        zone_id: location.zone_id,
      }));
    } else {
      availableLocations.value = [];
    }
  };

  // Función para construir el payload
  const constructPayload = (): SaveTransportConfigurationPayload => {
    // Busca el ID de la locación actual seleccionada basada en el nombre de la locación
    const currentLocationId = availableLocations.value.find(
      (loc) => loc.display_name === currentLocation.value
    )?.id;

    // Construye el payload que se enviará a la API
    const payload: SaveTransportConfigurationPayload = {
      unit_id: currentUnitId.value, // ID de la unidad de transporte actual
      settings: Object.entries(transferTypeConfigurations.value).map(([key, config]) => ({
        key: key,
        transfer_id: 1, // ID fijo del tipo de transferencia (puede ser hardcodeado o dinámico)
        transfer: { id: 1, name: 'Traslados en ciudad' }, // Objeto de transferencia con un ID y nombre fijo
        type_transfer: config.type_transfer, // Tipo de transferencia (ej. 'TRANSFER' o 'TOUR')
        date_from: selectedPeriod.value[0].format('YYYY-MM-DD'), // Fecha de inicio del rango de validez
        date_to: selectedPeriod.value[1].format('YYYY-MM-DD'), // Fecha de finalización del rango de validez
        status: config.active ? 1 : 0, // Estado de la configuración (activo o inactivo)
        setting_details: config.configurations.map((detail) => ({
          id: detail.id, // ID de la configuración (puede ser opcional)
          type_unit_transport_setting_id: detail.type_unit_transport_setting_id, // ID de la configuración de transporte
          minimum_capacity: detail.capacity.minimum, // Capacidad mínima de la configuración
          maximum_capacity: detail.capacity.maximum, // Capacidad máxima de la configuración
          representative_quantity: detail.representative_quantity, // Cantidad de representantes de la unidad
          trunk_car_quantity: detail.trunk_car_quantity, // Cantidad de maleteros
          trunk_representative_quantity: detail.trunk_representative_quantity, // Cantidad de representantes del maletero
          quantity_guides: detail.quantity_guides, // Cantidad de guías
          quantity_units_required: detail.quantity_units_required, // Cantidad de unidades requeridas
        })),
        type_unit_transport_location_id: currentLocationId, // ID de la locación de transporte
      })),
      copy_location: selectedLocations.value, // Lista de IDs de locaciones a las que se copiará la configuración
    };

    return payload;
  };

  // Definición de columnas anidadas para la tabla interna
  const nestedColumns = [
    {
      title: 'Periodo de validez',
      dataIndex: 'validityPeriod',
      key: 'validityPeriod',
      width: 80,
      fixed: 'left',
    },
    { title: 'Actividad', dataIndex: 'Activity', key: 'activity', width: 45, fixed: 'left' },
    {
      title: 'Capacidad Mínima',
      dataIndex: 'minCapacity',
      key: 'minCapacity',
      width: 40,
      align: 'center',
    },
    {
      title: 'Capacidad Máxima',
      dataIndex: 'maxCapacity',
      key: 'maxCapacity',
      width: 40,
      align: 'center',
    },
    {
      title: 'Representante de Unidad',
      dataIndex: 'unitRepresentative',
      key: 'unitRepresentative',
      width: 55,
      align: 'center',
    },
    {
      title: 'Maletero',
      dataIndex: 'trunkCarQuantity',
      key: 'trunkCarQuantity',
      align: 'center',
      width: 55,
    },
    {
      title: 'Representante del maletero',
      dataIndex: 'trunkRepresentativeQuantity',
      key: 'trunkRepresentativeQuantity',
      align: 'center',
      width: 55,
    },
    {
      title: 'Guía',
      dataIndex: 'quantityGuides',
      key: 'quantityGuides',
      align: 'center',
      width: 55,
    },
    {
      title: 'Acciones',
      dataIndex: 'actions',
      key: 'actions',
      align: 'center',
      width: 50,
      fixed: 'right',
    },
  ];

  // Definición de columnas anidadas para la tabla de configuración - drawer
  const drawerColumns = [
    {
      title: 'Capacidad Mínima - Máxima',
      dataIndex: 'capacityRange',
      key: 'capacityRange',
      align: 'center',
    },
    { title: 'Rep. Unidad', dataIndex: 'repUnit', key: 'repUnit', align: 'center' },
    { title: 'Maletero', dataIndex: 'trunk', key: 'trunk', align: 'center' },
    { title: 'Rep. Maletero', dataIndex: 'repTrunk', key: 'repTrunk', align: 'center' },
    { title: 'Guía', dataIndex: 'guide', key: 'guide', align: 'center' },
    { title: ' ', dataIndex: 'actions', key: 'actions', align: 'center' },
  ];

  const openDrawer = () => {
    isDrawerVisible.value = true;
  };

  const closeDrawer = () => {
    isDrawerVisible.value = false;
  };

  const drawerForAddConfiguration = (
    location: object, // Sede seleccionada
    record: object, // Unidad de transporte seleccionada
    recordDetails: Array, // Detalles de la configuración vacio
    initialize = false, // Indica si se está inicializando el drawer
    drawerMode: 'add' | 'edit' | 'clone' = 'add' // Modo de creación del drawer
  ) => {
    isDrawerVisible.value = true;
    drawerTitle.value =
      drawerMode === 'edit'
        ? 'Editar configuración'
        : drawerMode === 'clone'
          ? 'Clonar configuración'
          : 'Agregar configuración';
    currentUnitType.value = record.name;
    currentLocation.value = location.display_name;
    currentLocationId_.value = location.id;
    currentLocation_.value = location;

    modeDrawwer_.value = drawerMode;

    selectedPeriod.value = [dayjs(), dayjs()];
    if (drawerMode === 'edit') {
      if (recordDetails.length > 0) {
        const [dateFrom_, dateTo_] = recordDetails[0].validityPeriod.split(' - ');
        const dateFromParsed = dayjs(dateFrom_, 'DD/MM/YYYY');
        const dateToParsed = dayjs(dateTo_, 'DD/MM/YYYY');
        selectedPeriod.value = [dateFromParsed, dateToParsed];
      }
    }

    selectedLocations.value = [];
    currentUnitId.value = record.id;
    loadAvailableLocations(record);

    let transfer_configurations_ = [];
    let tours_configurations_ = [];

    console.log('recordDetails');
    console.log(recordDetails);

    if (drawerMode !== 'add') {
      recordDetails.forEach((rd) => {
        if (rd.activity === 'TRANSFER') {
          transfer_configurations_.push({
            id: rd.id,
            capacity: { minimum: rd.minCapacity, maximum: rd.maxCapacity },
            representative_quantity: rd.unitRepresentative,
            trunk_car_quantity: rd.trunkCarQuantity,
            trunk_representative_quantity: rd.trunkRepresentativeQuantity,
            quantity_guides: rd.quantityGuides,
            quantity_units_required: 0,
            type_unit_transport_setting_id: rd.setting_id,
          });
        } else {
          tours_configurations_.push({
            id: rd.id,
            capacity: { minimum: rd.minCapacity, maximum: rd.maxCapacity },
            representative_quantity: rd.unitRepresentative,
            trunk_car_quantity: rd.trunkCarQuantity,
            trunk_representative_quantity: rd.trunkRepresentativeQuantity,
            quantity_guides: rd.quantityGuides,
            quantity_units_required: 0,
            type_unit_transport_setting_id: rd.setting_id,
          });
        }
      });
    } else {
      transfer_configurations_ = Array.from({ length: 2 }, (_, index) => ({
        id: null,
        capacity: { minimum: index + 1, maximum: index + 1 },
        representative_quantity: 0,
        trunk_car_quantity: 0,
        trunk_representative_quantity: 0,
        quantity_guides: 0,
        quantity_units_required: 0,
        type_unit_transport_setting_id: null,
      }));

      tours_configurations_ = Array.from({ length: 2 }, (_, index) => ({
        id: null,
        capacity: { minimum: index + 1, maximum: index + 1 },
        representative_quantity: 0,
        trunk_car_quantity: 0,
        trunk_representative_quantity: 0,
        quantity_guides: 0,
        quantity_units_required: 0,
        type_unit_transport_setting_id: null,
      }));
    }

    if (initialize) {
      transferTypeConfigurations.value = {
        Traslado: {
          type_transfer: 'TRANSFER',
          configurations: transfer_configurations_,
          active: true,
          isVisible: true,
        },
        Tour: {
          type_transfer: 'TOUR',
          configurations: tours_configurations_,
          active: true,
          isVisible: true,
        },
      };
    }
  };

  const addCapacityRange = (key: string) => {
    const config = transferTypeConfigurations.value[key];

    if (!config) {
      console.error('Configuración no encontrada para la clave:', key);
      return;
    }

    const lastConfig = config.configurations[config.configurations.length - 1];
    const nextMinCapacity = lastConfig?.capacity ? lastConfig.capacity.maximum + 1 : 0;
    const nextMaxCapacity = nextMinCapacity;

    // Crea un nuevo rango de capacidad con campos vacíos
    const newConfig: SettingDetail = {
      capacity: { minimum: nextMinCapacity, maximum: nextMaxCapacity },
      representative_quantity: 0,
      trunk_car_quantity: 0,
      trunk_representative_quantity: 0,
      quantity_guides: 0,
      quantity_units_required: 0,
    };

    // Añade la nueva configuración solo a la configuración específica
    config.configurations.push(newConfig);
  };

  const toggleTransferActive = (key: string, checked: boolean) => {
    const config = transferTypeConfigurations.value[key];
    if (config) {
      config.active = checked;
      console.log(`Nuevo estado de '${key}':`, config.active);
    }
  };

  const toggleVisibility = (key: string) => {
    transferTypeConfigurations.value[key].isVisible =
      !transferTypeConfigurations.value[key].isVisible;
  };

  // Función para eliminar una configuración
  const removeConfiguration = (index: number, type: string) => {
    transferTypeConfigurations.value[type].configurations.splice(index, 1);
  };

  // obtiene los detalles específicos de una fila expandida desde la API
  const fetchDataForRow = async (location: Location, filters: object) => {
    console.log(location);
    if (isLoading.value) return; // Evita llamadas adicionales si ya se está cargando
    isLoading.value = true;
    try {
      console.log(filters);
      let moreFilters = '';
      if (filters) {
        if (filters.date_from != null) {
          moreFilters += '&date_from=' + filters.date_from + '&date_to=' + filters.date_to;
        }
        if (filters.type_transfer != null) {
          moreFilters += '&type_transfer=' + filters.type_transfer;
        }
        if (filters.is_trunk != null && filters.is_trunk) {
          moreFilters += '&is_trunk=1';
        }
        if (filters.is_representative != null && filters.is_representative) {
          moreFilters += '&is_representative=1';
        }
        if (filters.is_trunk_representative != null && filters.is_trunk_representative) {
          moreFilters += '&is_trunk_representative=1';
        }
        if (filters.guides != null && filters.guides) {
          moreFilters += '&guides=1';
        }
      }

      const response = await supportApi.get(
        `unit-settings?status=1&location_id=${location.id}${moreFilters}`
      );
      const details = [];

      response.data.data.forEach((setting) => {
        // console.log(setting.id);
        setting.setting_details.forEach((setting_detail) => {
          details.push({
            setting_id: setting.id,
            createAt: setting.create_at,
            validityPeriod: `${dayjs(setting.date_from).format('DD/MM/YYYY')} - ${dayjs(
              setting.date_to
            ).format('DD/MM/YYYY')}`,
            activity: setting.type_transfer,
            minCapacity: setting_detail.capacity.minimum,
            maxCapacity: setting_detail.capacity.maximum,
            unitRepresentative: setting_detail.representative_quantity,
            trunkCarQuantity: setting_detail.trunk_car_quantity,
            trunkRepresentativeQuantity: setting_detail.trunk_representative_quantity,
            quantityGuides: setting_detail.quantity_guides,
            setting_detail_id: setting_detail.id,
            id: setting_detail.id,
          });
        });
      });

      // Agrupar configuraciones por periodo de validez
      const groupedByPeriod = {};
      details.forEach((item) => {
        if (!groupedByPeriod[item.validityPeriod]) {
          groupedByPeriod[item.validityPeriod] = [];
        }
        groupedByPeriod[item.validityPeriod].push(item);
      });

      // Mantener solo la primera configuración de cada grupo habilitada
      Object.keys(groupedByPeriod).forEach((period) => {
        groupedByPeriod[period].forEach((config, index) => {
          config.isFirstInSetting = index === 0; // Solo la primera configuración en el grupo está habilitada
        });
      });

      // Convertir el objeto agrupado de nuevo a una lista
      location.details = Object.values(groupedByPeriod).flat();
      settingDetails.value = location.details;
    } catch (error) {
      console.error('Error fetching data for row:', error);
      handleApiError(error);
    } finally {
      isLoading.value = false;
    }
  };

  // Acción: maneja el cambio de pestañas dentro de una fila expandida
  const handleTabChangeUnit = (key, record) => {
    if (record && record.locations && record.locations[key]) {
      const selectedLocation = record.locations[key];
      fetchDataForRow(selectedLocation, {});
    } else {
      console.error('Error: La ubicación seleccionada no es válida o no existe en el registro.');
    }
  };

  // Función para guardar cambios
  const saveChanges = async () => {
    try {
      // Paso 1: Validar los datos y construir el payload
      validateConfigurations();
      // Paso 2: Construir el payload
      const payload = constructPayload();
      console.log('Payload construido:', payload);

      await formRef.value?.validate();

      // Paso 3: Enviar el payload al API para guardar los cambios
      const success = await store.saveTransportConfiguration(
        currentLocationId_.value,
        payload,
        modeDrawwer_.value
      );

      if (success) {
        // Cierra el drawer después de guardar los cambios
        closeDrawer();
        await fetchDataForRow(currentLocation_.value, {});
        // Actualiza los presets del rango de fechas
        filterStore.setRangePresets(currentLocation_.value.details); // Pasamos los detalles actualizados
      }
    } catch (error) {
      handleApiError(error);
      return { success: false, message: 'Ocurrió un error al guardar las confuguraciones' };
    }
  };

  const validateConfigurations = () => {
    // Verificar si el período de fechas seleccionado es válido
    if (selectedPeriod.value.length !== 2 || !selectedPeriod.value[0] || !selectedPeriod.value[1]) {
      throw new Error('Error: El rango de fechas no está completo.');
    }

    // Verificar que al menos un tipo de traslado esté habilitado
    const isAnyTransferActive = Object.values(transferTypeConfigurations.value).some(
      (config) => config.active
    );
    if (!isAnyTransferActive) {
      throw new Error('Error: Debe haber al menos un tipo de traslado habilitado.');
    }
  };

  const editOrCloneSettingDetails = async (
    period: string,
    unitType: object,
    location: Location,
    mode: string
  ) => {
    const details_ = [];
    location.details.forEach((detail) => {
      if (detail.validityPeriod === period) {
        details_.push(detail);
      }
    });

    drawerForAddConfiguration(location, unitType, details_, true, mode);
  };

  return {
    mode,
    nestedColumns,
    drawerColumns,
    isDrawerVisible,
    drawerTitle,
    currentUnitType,
    currentLocation,
    currentUnitId,
    transferTypeConfigurations,
    selectedPeriod,
    toggleTransferActive,
    applyToOtherLocations,
    selectedLocations,
    availableLocations,
    drawerForAddConfiguration,
    addCapacityRange,
    openDrawer,
    closeDrawer,
    removeConfiguration,
    saveChanges,
    editOrCloneSettingDetails,
    formRef,
    toggleVisibility,
    handleTabChangeUnit,
    fetchDataForRow,
  };
};
