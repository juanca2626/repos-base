import { computed, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRouter } from 'vue-router';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { productConfigurationResourceService } from '@/modules/negotiations/products/shared/services/productConfigurationResourceService';
import type { SupplierPlaceOperation } from '@/modules/negotiations/products/general/interfaces/resources';
import type { BaseDrawerProps } from '../../base/interfaces';
import type { ProductSupplierOperatingLocation } from '@/modules/negotiations/products/configuration/shared/interfaces/product-supplier-operating-location.interface';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import type {
  Configuration,
  OperatingLocation,
} from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';
interface TrainFormState {
  placeOperationIds: string[];
  trainTypeIds: string[];
}

export function useTrainCreateDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const router = useRouter();
  const { fetchPlaceOperations } = productConfigurationResourceService;

  const supportResourcesStore = useSupportResourcesStore();
  const { trainTypes: storeTrainTypes } = storeToRefs(supportResourcesStore);

  const configurationStore = useConfigurationStore();

  const { productSupplierType } = useSelectedServiceType();

  const isLoading = ref<boolean>(false);
  const placeOperations = ref<SelectOption[]>([]);
  const placeOperationsData = ref<SupplierPlaceOperation[]>([]);

  const trainTypes = computed(() =>
    storeTrainTypes.value.map((item) => ({
      label: item.name,
      value: item.code,
    }))
  );

  const formState = reactive<TrainFormState>({
    placeOperationIds: [],
    trainTypeIds: [],
  });

  // Para trains, solo hay un paso, así que siempre es paso 1
  const stepNumber = ref<number>(1);

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
      // Si se seleccionó "Seleccionar todo", seleccionar todos los tipos
      formState.trainTypeIds = [...allTrainTypeValues, SELECT_ALL_VALUE];
    } else if (!hasSelectAll && previousHasSelectAll) {
      // Si se deseleccionó "Seleccionar todo", deseleccionar todos
      formState.trainTypeIds = stringValues.filter((val) => val !== SELECT_ALL_VALUE);
    } else if (hasSelectAll && stringValues.length === allTrainTypeValues.length + 1) {
      // Si todos están seleccionados, mantener "Seleccionar todo"
      formState.trainTypeIds = [...allTrainTypeValues, SELECT_ALL_VALUE];
    } else if (!hasSelectAll && selectedValues.length === allTrainTypeValues.length) {
      // Si se seleccionaron todos manualmente, agregar "Seleccionar todo"
      formState.trainTypeIds = [...stringValues, SELECT_ALL_VALUE];
    } else {
      // Actualización normal
      formState.trainTypeIds = stringValues.filter((val) => val !== SELECT_ALL_VALUE);
      // Si todos están seleccionados, agregar "Seleccionar todo"
      if (
        formState.trainTypeIds.length === allTrainTypeValues.length &&
        allTrainTypeValues.every((val) => formState.trainTypeIds.includes(val))
      ) {
        formState.trainTypeIds.push(SELECT_ALL_VALUE);
      }
    }
  };

  // Valor filtrado para mostrar solo los tipos reales (sin "Seleccionar todo")
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

  const buildTrainConfigurationRequest = (): Configuration => {
    const selectedPlaceOperations = getSelectedPlaceOperations();

    const validTrainTypeCodes = formState.trainTypeIds.filter(
      (id) => id !== SELECT_ALL_VALUE
    ) as string[];

    const operatingLocations: OperatingLocation[] = selectedPlaceOperations.map((location) => {
      const operatingLocation = buildOperatingLocation(location);

      return {
        location: operatingLocation,
        trainTypes: validTrainTypeCodes,
      };
    });

    return {
      operatingLocations,
    };
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;

      const request = buildTrainConfigurationRequest();

      const { items } = await configurationStore.saveConfiguration(
        productSupplierType.value,
        props.productSupplierId,
        request
      );

      if (items.length > 0) {
        await navigateToConfiguration();
      }
    } catch (error) {
      console.error('Error saving train configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const navigateToConfiguration = () => {
    const serviceId = props.productSupplierId;

    if (!serviceId) return;

    router.push({
      name: 'serviceConfiguration',
      params: { id: serviceId },
    });
  };

  const handleGoToConfiguration = async () => {
    await handleSave();
    handleClose();
  };

  const getSupportResources = async () => {
    try {
      isLoading.value = true;

      await supportResourcesStore.loadResources({
        serviceType: productSupplierType.value,
        keys: ['trainTypes'],
      });
    } catch (error) {
      console.error('Error fetching support resources data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const getPlaceOperations = async () => {
    try {
      if (!props.supplierOriginalId) return;

      isLoading.value = true;

      const { data } = await fetchPlaceOperations(props.supplierOriginalId);

      placeOperationsData.value = data;

      placeOperations.value = data.map((item) => {
        const locationName = [item.city ? item.city.name : '', item.state.name, item.country.name]
          .filter(Boolean)
          .join(', ');

        return {
          label: locationName,
          value: item.key,
        };
      });
    } catch (error) {
      console.error('Error fetching place operations:', error);
    } finally {
      isLoading.value = false;
    }
  };

  watch(
    () => props.showDrawerForm,
    async (newVal) => {
      if (newVal) {
        getPlaceOperations();
        getSupportResources();
      }
    }
  );

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
