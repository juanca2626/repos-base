import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { productConfigurationResourceService } from '@/modules/negotiations/products/shared/services/productConfigurationResourceService';
import type { SupplierPlaceOperation } from '@/modules/negotiations/products/general/interfaces/resources';
import type { BaseDrawerProps } from '../../base/interfaces';
import { productConfigurationTrainService } from '../services/productConfigurationTrainService';
import type { TrainConfigurationRequest, TrainConfigurationPayload } from '../interfaces';
import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
interface TrainFormState {
  placeOperationIds: string[];
  trainTypeIds: string[];
}

export function useTrainEditDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const configurationStore = useConfigurationStore();
  const { items } = storeToRefs(configurationStore);

  const supportResourcesStore = useSupportResourcesStore();
  const { trainTypes: supplierTrainTypes } = storeToRefs(supportResourcesStore);

  const { fetchPlaceOperations } = productConfigurationResourceService;

  const isLoading = ref<boolean>(false);
  const placeOperations = ref<SelectOption[]>([]);
  const placeOperationsData = ref<SupplierPlaceOperation[]>([]);
  const availablePlaceOperations = ref<SupplierPlaceOperation[]>([]);

  const formState = reactive<TrainFormState>({
    placeOperationIds: [],
    trainTypeIds: [],
  });

  const stepNumber = ref<number>(1);

  const trainTypes = computed(() => {
    return supplierTrainTypes.value.map((train) => {
      return {
        label: train.name,
        value: train.code,
      };
    });
  });

  const cancelButtonText = computed(() => {
    return 'Cancelar';
  });

  const nextButtonText = computed(() => {
    return 'Guardar';
  });

  const isNextButtonDisabled = computed(() => {
    const validTrainTypeIds = formState.trainTypeIds.filter((id) => id !== SELECT_ALL_VALUE);
    return formState.placeOperationIds.length === 0 || validTrainTypeIds.length === 0;
  });

  const resetData = () => {
    formState.placeOperationIds = [];
    formState.trainTypeIds = [];
  };

  const handleClose = (): void => {
    resetData();
    emit('update:showDrawerForm', false);
  };

  const handleCancelGoBack = (): void => {
    handleClose();
  };

  const isSelectedPlaceOperation = (value: string): boolean => {
    return formState.placeOperationIds.includes(value);
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
    return [{ value: SELECT_ALL_VALUE, label: 'Seleccionar todo' }, ...trainTypes.value];
  });

  const isAllTrainTypesSelected = computed(() => {
    return (
      trainTypes.value.length > 0 &&
      trainTypes.value.every((type) => formState.trainTypeIds.includes(String(type.value)))
    );
  });

  const handleTrainTypeChange = (selectedValues: (string | number)[]) => {
    const stringValues = selectedValues.map((val) => String(val));
    const hasSelectAll = stringValues.includes(SELECT_ALL_VALUE);
    const previousHasSelectAll = formState.trainTypeIds.includes(SELECT_ALL_VALUE);
    const allTrainTypeValues = trainTypes.value.map((type) => String(type.value));

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

  const displayTrainTypeIds = computed(() => {
    return formState.trainTypeIds.filter((id) => id !== SELECT_ALL_VALUE);
  });

  const buildOperatingLocation = (
    item: SupplierPlaceOperation
  ): ProductSupplierOperatingLocation => {
    const location: ProductSupplierOperatingLocation = {
      key: item.key,
      country: {
        code: item.country.code,
        name: item.country.name.toUpperCase(),
      },
      state: {
        code: item.state.code,
        name: item.state.name.toUpperCase(),
      },
    };

    if (item.city) {
      location.city = {
        code: item.city.code,
        name: item.city.name.toUpperCase(),
      };
    }

    return location;
  };

  const getSelectedPlaceOperations = (): SupplierPlaceOperation[] => {
    return placeOperationsData.value.filter((item) =>
      formState.placeOperationIds.includes(item.key)
    );
  };

  const buildTrainConfigurationRequest = (): TrainConfigurationRequest => {
    const selectedPlaceOperations = getSelectedPlaceOperations();
    const validTrainTypeCodes = formState.trainTypeIds.filter(
      (id) => id !== SELECT_ALL_VALUE
    ) as string[];

    const configurations: TrainConfigurationPayload[] = selectedPlaceOperations.map((location) => {
      const operatingLocation = buildOperatingLocation(location);

      return {
        operatingLocation,
        trainTypeCodes: validTrainTypeCodes,
      };
    });

    return {
      configurations,
    };
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;
      const request = buildTrainConfigurationRequest();

      const { success } = await productConfigurationTrainService.createTrainConfiguration(
        props.productSupplierId,
        request
      );

      if (success) {
        await configurationStore.loadConfiguration();
        clearFormState();
        handleClose();
      }
    } catch (error) {
      console.error('Error saving train configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleGoToConfiguration = async () => {
    await handleSave();
  };

  const loadAvailablePlaceOperations = async () => {
    if (!props.supplierOriginalId) {
      return;
    }

    try {
      isLoading.value = true;

      const { data } = await fetchPlaceOperations(props.supplierOriginalId);

      if (data) {
        placeOperationsData.value = data;

        const existingLocationKeys = new Set(items.value.map((item) => item.key));

        availablePlaceOperations.value = data.filter(
          (placeOperation) => !existingLocationKeys.has(placeOperation.key)
        );

        placeOperations.value = availablePlaceOperations.value.map((item) => {
          const locationName = [item.city ? item.city.name : '', item.state.name, item.country.name]
            .filter(Boolean)
            .join(', ');

          return {
            label: locationName,
            value: item.key,
          };
        });
      }
    } catch (error) {
      console.error('Error loading available place operations:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const clearFormState = () => {
    formState.placeOperationIds = [];
    formState.trainTypeIds = [];
  };

  watch(
    () => props.showDrawerForm,
    async (newVal) => {
      if (newVal) {
        await loadAvailablePlaceOperations();
      }
    }
  );

  onMounted(() => {});

  return {
    isLoading,
    formState,
    placeOperations,
    trainTypes,
    trainTypesWithSelectAll,
    displayTrainTypeIds,
    stepNumber,
    cancelButtonText,
    nextButtonText,
    isNextButtonDisabled,
    handleClose,
    handleCancelGoBack,
    isSelectedPlaceOperation,
    isSelectedTrainType,
    handleTrainTypeChange,
    SELECT_ALL_VALUE,
    handleGoToConfiguration,
  };
}
