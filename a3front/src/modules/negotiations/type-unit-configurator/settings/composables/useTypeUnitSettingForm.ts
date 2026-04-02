import { computed, onMounted, reactive, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useTypeUnitSettingStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingStore';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  CountryLocation,
  ResultByLocation,
  TypeUnitSettingForm,
  TypeUnitTransport,
  TypeUnitTransportTransferResponse,
} from '@/modules/negotiations/type-unit-configurator/settings/interfaces';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SelectOption } from '@/modules/negotiations/type-unit-configurator/interfaces';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import {
  joinKeyOperationLocation,
  parseKeyOperationLocation,
} from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import { useTypeUnitSettingResourceStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingResourceStore';
import {
  buildPeriodDateRange,
  buildPeriodYears,
  currentYear,
} from '@/modules/negotiations/type-unit-configurator/helpers/periodHelper';
import type { FormInstance, Rule } from 'ant-design-vue/es/form';
import { filterOption } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import { emit } from '@/modules/negotiations/api/eventBus';
import { useNotifications } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/commons/useNotifications';
import { useTypeUnitSettingListStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingListStore';
import { on } from '@/modules/negotiations/api/eventBus';
import { parseErrors } from '@/modules/negotiations/helpers/responseApiHelper';

export const useTypeUnitSettingForm = () => {
  const resource = 'unit-settings';
  const COUNTRY_ID_PE = 89;
  const formRefTypeUnitSetting = ref<FormInstance | null>(null);
  const isLoading = ref<boolean>(false);
  const isEditMode = ref<boolean>(false);
  const typeUnits = ref<SelectOption[]>([]);
  const countryLocations = ref<SelectOption[]>([]);
  const applyToOtherLocations = ref<boolean>(false);
  const cloneSelectedLocations = ref<string[]>([]);
  const showSettingSaveResult = ref<boolean>(false);
  const resultsByLocation = ref<ResultByLocation[]>([]);

  const initFormState: TypeUnitSettingForm = {
    locationKey: '',
    periodYear: currentYear,
    transferId: null,
    settingDetails: [],
  };

  const formState = reactive<TypeUnitSettingForm>({
    ...initFormState,
  });

  const typeUnitSettingStore = useTypeUnitSettingStore();
  const { setShowDrawerForm } = typeUnitSettingStore;
  const { showDrawerForm } = storeToRefs(typeUnitSettingStore);

  const { transfers, isLoading: isLoadingResource } = storeToRefs(
    useTypeUnitSettingResourceStore()
  );

  const { showNotificationError, showNotificationSuccess } = useNotifications();

  const { transferData } = storeToRefs(useTypeUnitSettingListStore());

  const resetForm = () => {
    formRefTypeUnitSetting.value?.resetFields();
  };

  const initForm = () => {
    Object.assign(formState, structuredClone(initFormState));

    Array.from({ length: 2 }, () => addSettingDetail());
  };

  const periodYears = ref<SelectOption[]>(buildPeriodYears());

  const formRules: Record<string, Rule[]> = {
    locationKey: [
      { required: true, message: 'Debe seleccionar el lugar de operación', trigger: 'change' },
    ],
    periodYear: [{ required: true, message: 'Debe seleccionar el periodo', trigger: 'change' }],
    transferId: [
      { required: true, message: 'Debe seleccionar el tipo de traslado', trigger: 'change' },
    ],
  };

  const detailColumns = [
    {
      title: 'Tipo Unidad',
      dataIndex: 'typeUnit',
      key: 'typeUnit',
      align: 'center',
    },
    {
      title: 'Capacidades Mínima - Máxima',
      dataIndex: 'capacityRange',
      key: 'capacityRange',
      align: 'center',
    },
    {
      title: 'Cant. Unidades',
      dataIndex: 'quantityUnitsRequired',
      key: 'quantityUnitsRequired',
      align: 'center',
    },
    {
      title: 'Rep. Unidad',
      dataIndex: 'representativeQuantity',
      key: 'representativeQuantity',
      align: 'center',
    },
    { title: 'Maletero', dataIndex: 'trunkCarQuantity', key: 'trunkCarQuantity', align: 'center' },
    {
      title: 'Rep. Maletero',
      dataIndex: 'trunkRepresentativeQuantity',
      key: 'trunkRepresentativeQuantity',
      align: 'center',
    },
    { title: 'Guía', dataIndex: 'quantityGuides', key: 'quantityGuides', align: 'center' },
    { title: '', dataIndex: 'actions', key: 'actions', align: 'center' },
  ];

  const resetData = (): void => {
    isEditMode.value = false;
    applyToOtherLocations.value = false;
    cloneSelectedLocations.value = [];
  };

  const handleClose = (): void => {
    resetForm();
    initForm();
    setShowDrawerForm(false);
    resetData();
  };

  const setTypeUnits = async (data: TypeUnitTransport[]) => {
    typeUnits.value = data.map((row) => {
      return {
        label: row.code,
        value: row.id,
      };
    });
  };

  const fetchTypeUnits = async () => {
    try {
      const { data } = await supportApi.get<ApiResponse<TypeUnitTransport[]>>('units/all');
      setTypeUnits(data.data);
    } catch (error: any) {
      console.error('Error fetching type units:', error);
      handleError(error);
    }
  };

  const fetchSupportResources = async () => {
    try {
      const { data } = await supportApi.get<
        ApiResponse<{
          country_locations: CountryLocation[];
        }>
      >('support/resources', {
        params: {
          keys: ['country_locations'],
          country_id: COUNTRY_ID_PE,
        },
      });
      mapCountryLocations(data.data.country_locations);
    } catch (error: any) {
      console.error('Error fetching support resources:', error);
      handleError(error);
    }
  };

  const loadResources = async () => {
    isLoading.value = true;

    await Promise.all([fetchTypeUnits(), fetchSupportResources()]);

    isLoading.value = false;
  };

  const mapCountryLocations = async (data: CountryLocation[]) => {
    countryLocations.value = data.map((location) => ({
      label: location.location_name,
      value: joinKeyOperationLocation(
        '-',
        location.country_id,
        location.state_id,
        location.city_id || undefined,
        location.zone_id || undefined
      ),
    }));
  };

  const countryLocationsToClone = computed(() => {
    return countryLocations.value.map((location) => ({
      label: location.label,
      value: location.value,
      disabled: location.value === formState.locationKey,
    }));
  });

  const handleCountryLocations = () => {
    cloneSelectedLocations.value = cloneSelectedLocations.value.filter(
      (locationKey) => locationKey !== formState.locationKey
    );
  };

  const addSettingDetail = () => {
    const lastConfig = formState.settingDetails[formState.settingDetails.length - 1];
    const nextMinCapacity = lastConfig?.maximumCapacity ? lastConfig.maximumCapacity + 1 : 1;
    const nextMaxCapacity = nextMinCapacity;

    formState.settingDetails.push({
      typeUnitTransportId: null,
      minimumCapacity: nextMinCapacity,
      maximumCapacity: nextMaxCapacity,
      representativeQuantity: 0,
      trunkCarQuantity: 0,
      trunkRepresentativeQuantity: 0,
      quantityGuides: 0,
      quantityUnitsRequired: 1,
    });
  };

  const deleteSettingDetail = (index: number) => {
    formState.settingDetails.splice(index, 1);
  };

  const buildSettingDetailsRequest = () => {
    return {
      setting_details: formState.settingDetails.map((row) => {
        return {
          id: row.id,
          type_unit_transport_id: row.typeUnitTransportId,
          minimum_capacity: row.minimumCapacity,
          maximum_capacity: row.maximumCapacity,
          representative_quantity: row.representativeQuantity,
          trunk_car_quantity: row.trunkCarQuantity,
          trunk_representative_quantity: row.trunkRepresentativeQuantity,
          quantity_guides: row.quantityGuides,
          quantity_units_required: row.quantityUnitsRequired,
        };
      }),
    };
  };

  const buildBaseRequestData = () => {
    const { dateFrom, dateTo } = buildPeriodDateRange(formState.periodYear);
    const { countryId, stateId, cityId, zoneId } = parseKeyOperationLocation(
      formState.locationKey,
      '-'
    );

    return {
      country_id: countryId,
      state_id: stateId,
      city_id: cityId,
      zone_id: zoneId,
      date_from: dateFrom,
      date_to: dateTo,
      transfer_id: formState.transferId,
    };
  };

  const buildRequestData = () => {
    const details = buildSettingDetailsRequest();

    return isEditMode.value ? { ...details } : { ...buildBaseRequestData(), ...details };
  };

  const buildClonedRequest = (baseRequest: any, locationKey: string) => {
    const { countryId, stateId, cityId, zoneId } = parseKeyOperationLocation(locationKey, '-');

    return {
      ...baseRequest,
      country_id: countryId,
      state_id: stateId,
      city_id: cityId,
      zone_id: zoneId,
    };
  };

  const isSettingWithCloning = computed(
    () => applyToOtherLocations.value && cloneSelectedLocations.value.length > 0
  );

  const completeSaveForm = (data?: any) => {
    if (data) {
      handleSuccessResponse(data);
    }
    emit('reloadTypeUnitSettingList');
    handleClose();
  };

  const saveSetting = async (method: 'post' | 'put', url: string) => {
    const requestData = buildRequestData();

    const { data } = await supportApi[method]<ApiResponse<TypeUnitTransportTransferResponse>>(
      url,
      requestData
    );

    if (data.success) {
      completeSaveForm(data);
    }
  };

  const updateSetting = async () => {
    await saveSetting('put', `${resource}/transfers/${formState.id}/details`);
  };

  const createSingleSetting = async () => {
    await saveSetting('post', `${resource}`);
  };

  const findLocationFullName = (locationKey: string): string => {
    const location = countryLocations.value.find((row) => row.value === locationKey);

    return location?.label || '';
  };

  const processResponses = () => {
    const countSuccess = resultsByLocation.value.filter((row) => row.errors.length === 0).length;
    const countErrors = resultsByLocation.value.length - countSuccess;

    if (countErrors === 0) {
      showNotificationSuccess('Todas las configuraciones se guardaron correctamente.');
    } else if (countSuccess === 0) {
      showNotificationError('No se pudo guardar ninguna configuración.');
      showSettingSaveResult.value = true;
    } else {
      showNotificationError('Algunas configuraciones no se pudieron guardar.');
      showSettingSaveResult.value = true;
    }

    if (countSuccess > 0) {
      completeSaveForm();
    }
  };

  const setResultsByLocation = (locationKey: string, success: boolean = true, error?: any) => {
    if (success) {
      resultsByLocation.value.push({
        location: findLocationFullName(locationKey),
        message: 'Configuración registrada correctamente',
        errors: [],
      });

      return;
    }

    resultsByLocation.value.push({
      location: findLocationFullName(locationKey),
      message: 'No se pudo guardar la configuración',
      errors: parseErrors(error),
    });
  };

  const createSettingsWithCloning = async () => {
    const mainRequest = buildRequestData();
    const currentLocationKeys: string[] = [formState.locationKey, ...cloneSelectedLocations.value];
    resultsByLocation.value = [];

    for (const locationKey of currentLocationKeys) {
      try {
        const { data } = await supportApi.post<ApiResponse<TypeUnitTransportTransferResponse>>(
          `${resource}`,
          buildClonedRequest(mainRequest, locationKey)
        );

        if (data.success) {
          setResultsByLocation(locationKey);
        }
      } catch (error: any) {
        setResultsByLocation(locationKey, false, error);
      }
    }

    processResponses();
  };

  const createSettings = async () => {
    if (isSettingWithCloning.value) {
      await createSettingsWithCloning();
    } else {
      await createSingleSetting();
    }
  };

  const saveForm = async () => {
    try {
      isLoading.value = true;

      if (isEditMode.value) {
        await updateSetting();
      } else {
        await createSettings();
      }
    } catch (error: any) {
      handleError(error);
      console.error('Error save type unit setting:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const extraValidations = () => {
    if (formState.settingDetails.length === 0) {
      return {
        success: false,
        message: 'Debe agregar al menos una configuración.',
      };
    }

    const typeUnitsEmpty = formState.settingDetails.some((row) => !row.typeUnitTransportId);

    if (typeUnitsEmpty) {
      return {
        success: false,
        message: 'El campo tipo unidad es obligatorio.',
      };
    }

    return {
      success: true,
      message: '',
    };
  };

  const handleSubmit = async () => {
    try {
      await formRefTypeUnitSetting.value?.validate();

      const validation = extraValidations();

      if (!validation.success) {
        showNotificationError(validation.message);
        return;
      }

      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const isLoadingForm = computed(() => isLoading.value || isLoadingResource.value);

  const setTransferDataToForm = () => {
    const record = transferData.value;

    Object.assign(formState, {
      id: record.id,
      locationKey: null,
      periodYear: null,
      transferId: record.transferId,
      settingDetails: record.settingDetails.map((row) => {
        return {
          id: row.id,
          typeUnitTransportId: row.typeUnitTransport.id,
          minimumCapacity: row.minimumCapacity,
          maximumCapacity: row.maximumCapacity,
          representativeQuantity: row.representativeQuantity,
          trunkCarQuantity: row.trunkCarQuantity,
          trunkRepresentativeQuantity: row.trunkRepresentativeQuantity,
          quantityGuides: row.quantityGuides,
          quantityUnitsRequired: row.quantityUnitsRequired,
        };
      }),
    });
  };

  on('editTypeUnitSetting', () => {
    setTransferDataToForm();
    setShowDrawerForm(true);
    isEditMode.value = true;
  });

  onMounted(async () => {
    initForm();
    await loadResources();
  });

  return {
    showDrawerForm,
    isLoadingForm,
    isEditMode,
    typeUnits,
    countryLocations,
    transfers,
    periodYears,
    formState,
    formRefTypeUnitSetting,
    formRules,
    detailColumns,
    applyToOtherLocations,
    cloneSelectedLocations,
    countryLocationsToClone,
    resultsByLocation,
    showSettingSaveResult,
    handleClose,
    handleSubmit,
    filterOption,
    addSettingDetail,
    deleteSettingDetail,
    handleCountryLocations,
  };
};
