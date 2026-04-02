import { computed, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import type { BaseDrawerProps } from '../../base/interfaces';
import { productConfigurationTrainService } from '../services/productConfigurationTrainService';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

interface TrainEditTypeFormState {
  locationKey: string;
  trainTypeIds: string[];
}

export function useTrainEditTypeDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const navigationStore = useNavigationStore();
  const { activeTabKey } = storeToRefs(navigationStore);

  const configurationStore = useConfigurationStore();
  const { items } = storeToRefs(configurationStore);

  const supportResourcesStore = useSupportResourcesStore();
  const { trainTypes: supplierTrainTypes } = storeToRefs(supportResourcesStore);

  const isLoading = ref<boolean>(false);
  const availableTrainTypes = ref<SelectOption[]>([]);
  const selectedLocation = ref<ProductSupplierOperatingLocation | null>(null);

  const trainTypes = computed(() => {
    return supplierTrainTypes.value.map((train) => {
      return {
        label: train.name,
        value: train.code,
      };
    });
  });

  const formState = reactive<TrainEditTypeFormState>({
    locationKey: '',
    trainTypeIds: [],
  });

  const stepNumber = ref<number>(1);

  const cancelButtonText = computed(() => {
    return 'Cancelar';
  });

  const nextButtonText = computed(() => {
    return 'Guardar';
  });

  const isNextButtonDisabled = computed(() => {
    const validTrainTypeIds = formState.trainTypeIds.filter((id) => id !== SELECT_ALL_VALUE);
    return validTrainTypeIds.length === 0;
  });

  const resetData = () => {
    formState.locationKey = '';
    formState.trainTypeIds = [];
  };

  const handleClose = (): void => {
    resetData();
    emit('update:showDrawerForm', false);
  };

  const handleCancelGoBack = (): void => {
    handleClose();
  };

  const isSelectedTrainType = (value: string | number): boolean => {
    const stringValue = String(value);
    if (stringValue === SELECT_ALL_VALUE) {
      return isAllTrainTypesSelected.value;
    }
    return formState.trainTypeIds.includes(stringValue);
  };

  const SELECT_ALL_VALUE = '__SELECT_ALL__';

  const trainTypesWithSelectAll = computed(() => {
    return [{ value: SELECT_ALL_VALUE, label: 'Seleccionar todo' }, ...availableTrainTypes.value];
  });

  const isAllTrainTypesSelected = computed(() => {
    return (
      availableTrainTypes.value.length > 0 &&
      availableTrainTypes.value.every((type) => formState.trainTypeIds.includes(String(type.value)))
    );
  });

  const hasNoMoreTrainTypes = computed(() => {
    return availableTrainTypes.value.length === 0;
  });

  const handleTrainTypeChange = (selectedValues: (string | number)[]) => {
    const stringValues = selectedValues.map((val) => String(val));
    const hasSelectAll = stringValues.includes(SELECT_ALL_VALUE);
    const previousHasSelectAll = formState.trainTypeIds.includes(SELECT_ALL_VALUE);
    const allTrainTypeValues = availableTrainTypes.value.map((type) => String(type.value));

    if (hasSelectAll && !previousHasSelectAll) {
      formState.trainTypeIds = [...allTrainTypeValues, SELECT_ALL_VALUE];
    } else if (!hasSelectAll && previousHasSelectAll) {
      formState.trainTypeIds = stringValues.filter((val) => val !== SELECT_ALL_VALUE);
    } else if (hasSelectAll && stringValues.length === allTrainTypeValues.length + 1) {
      formState.trainTypeIds = [...allTrainTypeValues, SELECT_ALL_VALUE];
    } else if (!hasSelectAll && selectedValues.length === allTrainTypeValues.length) {
      formState.trainTypeIds = [...stringValues, SELECT_ALL_VALUE];
    } else {
      formState.trainTypeIds = stringValues.filter((val) => val !== SELECT_ALL_VALUE);
      if (
        formState.trainTypeIds.length === allTrainTypeValues.length &&
        allTrainTypeValues.every((val) => formState.trainTypeIds.includes(val))
      ) {
        formState.trainTypeIds.push(SELECT_ALL_VALUE);
      }
    }
  };

  const getConfigurationId = (): string | null => {
    if (!formState.locationKey) {
      return null;
    }

    const configuration = items.value.find((item) => item.key === formState.locationKey);

    if (!configuration) {
      return null;
    }

    return configuration.id;
  };

  const getConfiguredTrainTypeCodes = (): Set<string> => {
    if (!activeTabKey.value) {
      return new Set<string>();
    }

    const location = items.value.find((item) => item.key === activeTabKey.value);

    if (!location) {
      return new Set<string>();
    }

    const rawData = location.raw as Record<string, unknown>;
    if (rawData && typeof rawData === 'object' && 'trainTypeCodes' in rawData) {
      return new Set(rawData.trainTypeCodes as string[]);
    }

    return new Set<string>();
  };

  /**
   * Filtra los tipos de tren disponibles (los que NO están configurados)
   */
  const loadAvailableTrainTypes = () => {
    if (!activeTabKey.value) {
      availableTrainTypes.value = [];
      return;
    }

    // Obtener los trainTypeCodes ya configurados para esta ubicación
    const configuredTrainTypeCodes = getConfiguredTrainTypeCodes();

    // Filtrar solo los tipos de tren que NO están configurados
    availableTrainTypes.value = trainTypes.value.filter(
      (trainType) => !configuredTrainTypeCodes.has(String(trainType.value))
    );
  };

  /**
   * Inicializa la ubicación activa y carga los tipos de tren disponibles
   */
  const initializeLocation = () => {
    if (!activeTabKey.value) {
      selectedLocation.value = null;
      formState.locationKey = '';
      return;
    }

    // Buscar la ubicación en el store
    const location = items.value.find((loc: any) => loc.key === activeTabKey.value);

    if (location) {
      if (location.raw) {
        const rawData = location.raw as Record<string, unknown>;
        if (rawData && typeof rawData === 'object' && 'operatingLocation' in rawData) {
          selectedLocation.value = rawData.operatingLocation as ProductSupplierOperatingLocation;
        }
      }
      formState.locationKey = location.key;
    }

    // console.log('selectedLocation', selectedLocation.value);
    // Cargar tipos de tren disponibles
    loadAvailableTrainTypes();
  };

  /**
   * Construye el request para actualizar los tipos de tren
   * Incluye los tipos existentes + los nuevos seleccionados
   */
  const buildUpdateTrainTypesRequest = (): string[] => {
    const selectedTrainTypeCodes = formState.trainTypeIds
      .filter((id) => id !== SELECT_ALL_VALUE)
      .map((id) => String(id));

    // Obtener los trainTypeCodes ya configurados
    const existingTrainTypeCodes = Array.from(getConfiguredTrainTypeCodes());

    // Combinar los existentes con los nuevos seleccionados
    const allTrainTypeCodes = [...new Set([...existingTrainTypeCodes, ...selectedTrainTypeCodes])];

    return allTrainTypeCodes;
  };

  const handleSave = async (): Promise<void> => {
    const configurationId = getConfigurationId();

    if (!configurationId) {
      console.error('No se encontró configurationId para la configuración');
      return;
    }

    try {
      isLoading.value = true;
      const trainTypeCodes = buildUpdateTrainTypesRequest();

      const { success } = await productConfigurationTrainService.updateTrainTypes(
        configurationId,
        trainTypeCodes
      );

      if (success) {
        await configurationStore.loadConfiguration();
        clearFormState();
        handleClose();
      }
    } catch (error) {
      console.error('Error saving train types:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleGoToConfiguration = async () => {
    await handleSave();
  };

  const getLocationDisplayName = (): string => {
    if (!selectedLocation.value) return '';
    const parts = [
      selectedLocation.value.city?.name,
      selectedLocation.value.state.name,
      selectedLocation.value.country.name,
    ].filter(Boolean);
    return parts.join(', ');
  };

  const clearFormState = () => {
    formState.locationKey = '';
    formState.trainTypeIds = [];
  };

  // Cargar datos cuando se abre el drawer
  watch(
    () => props.showDrawerForm,
    async (isOpen) => {
      if (isOpen) {
        initializeLocation();
      }
    },
    { immediate: true }
  );

  return {
    isLoading,
    formState,
    trainTypes,
    trainTypesWithSelectAll,
    stepNumber,
    selectedLocation,
    cancelButtonText,
    nextButtonText,
    isNextButtonDisabled,
    hasNoMoreTrainTypes,
    handleClose,
    handleCancelGoBack,
    isSelectedTrainType,
    handleTrainTypeChange,
    SELECT_ALL_VALUE,
    handleGoToConfiguration,
    getLocationDisplayName,
  };
}
