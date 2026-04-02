import { computed, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { productConfigurationResourceService } from '@/modules/negotiations/products/shared/services/productConfigurationResourceService';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import type { SupplierPlaceOperation } from '@/modules/negotiations/products/general/interfaces/resources';
import type {
  ProductSupplierOperatingLocation,
  ProductSupplierBehaviorMode,
} from '@/modules/negotiations/products/configuration/interfaces';
import { ProductSupplierBehaviorModeEnum } from '@/modules/negotiations/products/configuration/drawers/generic/enums/product-supplier-behavior-mode.enum';
import { createConfigurationService } from '../services/createConfigurationService';
import type { CreateConfigurationRequest, ConfigurationPayload } from '../interfaces';
import type { BaseDrawerProps } from '../../base/interfaces';
import { useStepDrawer } from './useStepDrawer';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

interface EditFormState {
  placeOperationIds: string[];
  supplierCategoryCodes: string[];
  isGeneral: boolean;
  type: string;
  behaviorMatrix: Record<string, ProductSupplierBehaviorMode>;
}

export function useEditDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const { stepNumber, cancelButtonText, nextButtonText, handleGoBack, resetSteps } =
    useStepDrawer(emit);

  const configurationStore = useConfigurationStore();
  const { items } = storeToRefs(configurationStore);

  const supportResourcesStore = useSupportResourcesStore();
  const { supplierCategories: storeSupplierCategories } = storeToRefs(supportResourcesStore);

  const { fetchPlaceOperations } = productConfigurationResourceService;

  const isLoading = ref<boolean>(false);
  const availablePlaceOperations = ref<SupplierPlaceOperation[]>([]);
  const placeOperations = ref<SelectOption[]>([]);

  // Usar categorías del store en lugar de hacer llamada al API
  const supplierCategories = computed(() => {
    return storeSupplierCategories.value.map((item) => ({
      label: item.name,
      value: item.code,
    }));
  });

  const formState = reactive<EditFormState>({
    placeOperationIds: [],
    supplierCategoryCodes: [],
    isGeneral: false,
    type: '1',
    behaviorMatrix: {},
  });

  const resetData = () => {
    resetSteps();
    formState.placeOperationIds = [];
    formState.supplierCategoryCodes = [];
    formState.isGeneral = false;
    formState.type = '1';
    formState.behaviorMatrix = {};
  };

  const handleClose = (): void => {
    resetData();
    emit('update:showDrawerForm', false);
  };

  const isSelectedPlaceOperation = (value: string): boolean => {
    return formState.placeOperationIds.includes(value);
  };

  const isSelectedSupplierCategory = (value: string): boolean => {
    return formState.supplierCategoryCodes.includes(value);
  };

  const isSupplierCategoryDisabled = (value: string): boolean => {
    const hasNone = formState.supplierCategoryCodes.includes('none');
    const hasOtherCategories = formState.supplierCategoryCodes.some((code) => code !== 'none');

    if (hasNone && value !== 'none') {
      return true;
    }

    if (hasOtherCategories && value === 'none') {
      return true;
    }

    return false;
  };

  const handleSupplierCategoryChange = (values: string[]): void => {
    const hasNone = values.includes('none');
    const hasOtherCategories = values.some((code) => code !== 'none');

    if (hasNone && hasOtherCategories) {
      formState.supplierCategoryCodes = ['none'];
      return;
    }

    if (hasOtherCategories && values.includes('none')) {
      formState.supplierCategoryCodes = values.filter((code) => code !== 'none');
      return;
    }

    formState.supplierCategoryCodes = values;
  };

  const supplierCategoriesWithDisabled = computed(() => {
    return supplierCategories.value.map((category: any) => ({
      ...category,
      disabled: isSupplierCategoryDisabled(String(category.value)),
    }));
  });

  const getSelectedPlaceOperations = () => {
    return availablePlaceOperations.value.filter((item) =>
      formState.placeOperationIds.includes(item.key)
    );
  };

  const getSelectedSupplierCategories = () => {
    return supplierCategories.value.filter((item: any) =>
      formState.supplierCategoryCodes.includes(String(item.value))
    );
  };

  const getBehaviorMatrixKey = (currentKey: string, currentCode: string): string => {
    return `${currentKey}-${currentCode}`;
  };

  const setBehaviorMode = (
    currentKey: string,
    currentCode: string,
    mode: ProductSupplierBehaviorMode
  ) => {
    const key = getBehaviorMatrixKey(currentKey, currentCode);
    formState.behaviorMatrix[key] = mode;
  };

  const getBehaviorMode = (
    currentKey: string,
    currentCode: string
  ): ProductSupplierBehaviorMode | null => {
    if (formState.isGeneral) {
      return formState.type === '1'
        ? ProductSupplierBehaviorModeEnum.SIMPLE
        : ProductSupplierBehaviorModeEnum.COMPONENT;
    }
    const key = getBehaviorMatrixKey(currentKey, currentCode);
    return formState.behaviorMatrix[key] || null;
  };

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

  const getModeForCombination = (
    currentKey: string,
    currentCode: string
  ): ProductSupplierBehaviorMode | null => {
    if (formState.isGeneral) {
      return formState.type === '1'
        ? ProductSupplierBehaviorModeEnum.SIMPLE
        : ProductSupplierBehaviorModeEnum.COMPONENT;
    }

    return getBehaviorMode(currentKey, currentCode);
  };

  /**
   * Construye el payload en el nuevo formato con configurations[]
   */
  const buildCreateConfigurationRequest = (): CreateConfigurationRequest => {
    const selectedPlaceOperations = getSelectedPlaceOperations();
    const selectedCategories = getSelectedSupplierCategories();
    const configurations: ConfigurationPayload[] = [];

    for (const location of selectedPlaceOperations) {
      const operatingLocation = buildOperatingLocation(location);
      const behaviorSettings = [];

      const isGeneralForLocation = formState.isGeneral;

      for (const category of selectedCategories) {
        const mode = getModeForCombination(location.key, String(category.value));

        if (!mode) continue;

        behaviorSettings.push({
          supplierCategoryCode: String(category.value),
          mode,
        });
      }

      configurations.push({
        operatingLocation,
        applyGeneralBehavior: isGeneralForLocation,
        behaviorSettings,
      });
    }

    return {
      configurations,
    };
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;
      const request = buildCreateConfigurationRequest();

      const { success } = await createConfigurationService.createConfiguration(
        props.productSupplierId,
        request
      );

      if (success) {
        await configurationStore.loadConfiguration();
        clearFormState();
        handleClose();
      }
    } catch (error) {
      console.error('Error saving configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleGoNext = (): void => {
    stepNumber.value += 1;
  };

  const loadAvailablePlaceOperations = async () => {
    if (!props.supplierOriginalId) {
      return;
    }

    try {
      isLoading.value = true;

      const { data } = await fetchPlaceOperations(props.supplierOriginalId);

      if (data) {
        // Obtener las keys de las ubicaciones que ya están en el store
        const existingLocationKeys = new Set(items.value.map((item: any) => item.key));

        // Filtrar solo las ubicaciones que NO están en el store
        availablePlaceOperations.value = data.filter(
          (placeOperation) => !existingLocationKeys.has(placeOperation.key)
        );

        // Mapear a SelectOption para el componente
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

  const handleGoToConfiguration = async (): Promise<void> => {
    if (stepNumber.value === 1) {
      handleGoNext();
    } else if (stepNumber.value === 2) {
      await handleSave();
    }
  };

  const selectedPlaceOperations = computed(() => getSelectedPlaceOperations());
  const selectedSupplierCategories = computed(() => getSelectedSupplierCategories());

  const isAllCombinationsSelected = computed(() => {
    if (formState.isGeneral) {
      return true;
    }

    const locations = getSelectedPlaceOperations();
    const categories = getSelectedSupplierCategories();

    for (const location of locations) {
      for (const category of categories) {
        const mode = getBehaviorMode(location.key, String(category.value));
        if (!mode) {
          return false;
        }
      }
    }

    return true;
  });

  const isNextButtonDisabled = computed(() => {
    if (stepNumber.value === 1) {
      const hasNoLocations = formState.placeOperationIds.length === 0;
      const hasNoCategories = formState.supplierCategoryCodes.length === 0;
      return hasNoLocations || hasNoCategories;
    }

    if (stepNumber.value === 2) {
      return !isAllCombinationsSelected.value;
    }

    return false;
  });

  const hasNoMorePlaceOperations = computed(() => {
    return placeOperations.value.length === 0;
  });

  const getLocationDisplayName = (location: SupplierPlaceOperation): string => {
    const parts = [location.state.name].filter(Boolean);
    return parts.join(', ').toUpperCase();
  };

  const getCurrentMode = (currentKey: string, currentCode: string): string | null => {
    const mode = getBehaviorMode(currentKey, currentCode);
    return mode === ProductSupplierBehaviorModeEnum.SIMPLE
      ? 'simple'
      : mode === ProductSupplierBehaviorModeEnum.COMPONENT
        ? 'component'
        : null;
  };

  const handleCancelGoBack = (): void => {
    if (stepNumber.value === 1) {
      resetData();
      emit('update:showDrawerForm', false);
    } else {
      handleGoBack();
    }
  };

  const clearFormState = () => {
    formState.placeOperationIds = [];
    formState.supplierCategoryCodes = [];
  };

  watch(
    () => formState.isGeneral,
    (newVal) => {
      if (newVal && stepNumber.value === 2) {
        const selectedPlaceOperations = getSelectedPlaceOperations();
        const selectedCategories = getSelectedSupplierCategories();
        const mode =
          formState.type === '1'
            ? ProductSupplierBehaviorModeEnum.SIMPLE
            : ProductSupplierBehaviorModeEnum.COMPONENT;

        for (const location of selectedPlaceOperations) {
          for (const category of selectedCategories) {
            setBehaviorMode(location.key, String(category.value), mode);
          }
        }
      }
    }
  );

  watch(
    () => formState.type,
    (newVal) => {
      if (formState.isGeneral && stepNumber.value === 2) {
        const selectedPlaceOperations = getSelectedPlaceOperations();
        const selectedCategories = getSelectedSupplierCategories();
        const mode =
          newVal === '1'
            ? ProductSupplierBehaviorModeEnum.SIMPLE
            : ProductSupplierBehaviorModeEnum.COMPONENT;

        for (const location of selectedPlaceOperations) {
          for (const category of selectedCategories) {
            setBehaviorMode(location.key, String(category.value), mode);
          }
        }
      }
    }
  );

  // Cargar datos cuando se abre el drawer
  watch(
    () => props.showDrawerForm,
    async (isOpen) => {
      if (isOpen) {
        await loadAvailablePlaceOperations();
      }
    },
    { immediate: true }
  );

  return {
    isLoading,
    formState,
    placeOperations,
    supplierCategories,
    supplierCategoriesWithDisabled,
    stepNumber,
    selectedPlaceOperations,
    selectedSupplierCategories,
    cancelButtonText,
    nextButtonText,
    isAllCombinationsSelected,
    isNextButtonDisabled,
    hasNoMorePlaceOperations,
    handleClose,
    handleGoBack,
    isSelectedPlaceOperation,
    isSelectedSupplierCategory,
    isSupplierCategoryDisabled,
    handleSupplierCategoryChange,
    handleGoToConfiguration,
    setBehaviorMode,
    getLocationDisplayName,
    getCurrentMode,
    handleCancelGoBack,
  };
}
