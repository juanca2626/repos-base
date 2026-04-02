import { computed, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import type {
  ProductSupplierOperatingLocation,
  ProductSupplierBehaviorMode,
} from '@/modules/negotiations/products/configuration/interfaces';
import { ProductSupplierBehaviorModeEnum } from '@/modules/negotiations/products/configuration/drawers/generic/enums/product-supplier-behavior-mode.enum';
import { updateBehaviorService } from '../services/updateBehaviorService';
import type { UpdateBehaviorRequest } from '../services/updateBehaviorService';
import type { BaseDrawerProps } from '../../base/interfaces';
import { useStepDrawer } from './useStepDrawer';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
interface EditCategoryFormState {
  locationKey: string;
  supplierCategoryCodes: string[];
  isGeneral: boolean;
  type: string;
  behaviorMatrix: Record<string, ProductSupplierBehaviorMode>;
}

export function useEditCategoryDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const { stepNumber, cancelButtonText, nextButtonText, handleGoBack, resetSteps } =
    useStepDrawer(emit);

  const configurationStore = useConfigurationStore();
  const { items } = storeToRefs(configurationStore);

  const navigationStore = useNavigationStore();
  const { activeTabKey, getSectionsCodeByTabKeyActive } = storeToRefs(navigationStore);

  const supportResourcesStore = useSupportResourcesStore();
  const { supplierCategories } = storeToRefs(supportResourcesStore);

  const isLoading = ref<boolean>(false);

  // Usar categorías del store en lugar de hacer llamada al API
  const allSupplierCategories = computed(() => {
    return supplierCategories.value.map((item: any) => ({
      label: item.name,
      value: item?.code,
    }));
  });

  const availableSupplierCategories = ref<SelectOption[]>([]);
  const selectedLocation = ref<ProductSupplierOperatingLocation | null>(null);

  const formState = reactive<EditCategoryFormState>({
    locationKey: '',
    supplierCategoryCodes: [],
    isGeneral: false,
    type: '1',
    behaviorMatrix: {},
  });

  const resetData = () => {
    resetSteps();
    formState.supplierCategoryCodes = [];
    formState.isGeneral = false;
    formState.type = '1';
    formState.behaviorMatrix = {};
  };

  const handleClose = (): void => {
    resetData();
    emit('update:showDrawerForm', false);
  };

  const isSelectedSupplierCategory = (value: string): boolean => {
    return formState.supplierCategoryCodes.includes(value);
  };

  const handleSupplierCategoryChange = (values: string[]): void => {
    formState.supplierCategoryCodes = values;
  };

  const supplierCategoriesWithDisabled = computed(() => {
    // Filtrar la opción "Ninguna" por su value "none"
    return availableSupplierCategories.value.filter(
      (category) => String(category.value)?.toLowerCase() !== 'none'
    );
  });

  const getSelectedSupplierCategories = () => {
    return allSupplierCategories.value.filter((item: any) =>
      formState.supplierCategoryCodes.includes(String(item.value))
    );
  };

  const getBehaviorMatrixKey = (currentCode: string): string => {
    return `${formState.locationKey}-${currentCode}`;
  };

  const setBehaviorMode = (currentCode: string, mode: ProductSupplierBehaviorMode) => {
    const key = getBehaviorMatrixKey(currentCode);
    formState.behaviorMatrix[key] = mode;
  };

  const getBehaviorMode = (currentCode: string): ProductSupplierBehaviorMode | null => {
    if (formState.isGeneral) {
      return formState.type === '1'
        ? ProductSupplierBehaviorModeEnum.SIMPLE
        : ProductSupplierBehaviorModeEnum.COMPONENT;
    }
    const key = getBehaviorMatrixKey(currentCode);
    return formState.behaviorMatrix[key] || null;
  };

  const getModeForCombination = (currentCode: string): ProductSupplierBehaviorMode | null => {
    if (formState.isGeneral) {
      return formState.type === '1'
        ? ProductSupplierBehaviorModeEnum.SIMPLE
        : ProductSupplierBehaviorModeEnum.COMPONENT;
    }

    return getBehaviorMode(currentCode);
  };

  const getBehaviorId = (): string | null => {
    if (!formState.locationKey || !items.value || items.value.length === 0) {
      return null;
    }

    const item = items.value.find((item: any) => item.key === formState.locationKey);
    return item?.id || null;
  };

  /**
   * Construye el request para actualizar el comportamiento
   * Incluye las categorías existentes + las nuevas seleccionadas
   */
  const buildUpdateBehaviorRequest = (): UpdateBehaviorRequest => {
    const selectedCategories = getSelectedSupplierCategories();
    const behaviorSettings = [];

    const existingItem = items.value.find((item: any) => item.key === formState.locationKey);

    // Si hay configuración existente, incluir sus behaviorSettings
    if (existingItem) {
      for (const existingBehavior of (existingItem.raw as any)?.behaviorSettings) {
        // Solo incluir si no está en las nuevas categorías seleccionadas
        if (!formState.supplierCategoryCodes.includes(existingBehavior.supplierCategoryCode)) {
          behaviorSettings.push({
            supplierCategoryCode: existingBehavior.supplierCategoryCode,
            mode: existingBehavior.mode as string,
          });
        }
      }
    }

    // Agregar las nuevas categorías seleccionadas
    for (const category of selectedCategories) {
      const mode = getModeForCombination(String(category.value));

      if (!mode) continue;

      behaviorSettings.push({
        supplierCategoryCode: String(category.value),
        mode: mode as string,
      });
    }

    // Usar applyGeneralBehavior de la configuración existente si existe, sino usar el del formState
    const applyGeneralBehavior =
      (existingItem?.raw as any)?.applyGeneralBehavior ?? formState.isGeneral;

    return {
      applyGeneralBehavior,
      behaviorSettings,
    };
  };

  const handleSave = async (): Promise<void> => {
    const behaviorId = getBehaviorId();

    console.log('behaviorId', behaviorId);

    if (!behaviorId) {
      console.error('No se encontró behaviorId para la configuración');
      return;
    }

    try {
      isLoading.value = true;
      const request = buildUpdateBehaviorRequest();

      const { success } = await updateBehaviorService.updateBehavior(behaviorId, request);

      if (success) {
        await configurationStore.loadConfiguration();
        clearFormState();
        handleClose();
      }
    } catch (error) {
      console.error('Error saving category configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const handleGoNext = (): void => {
    stepNumber.value += 1;
  };

  const loadAvailableCategories = () => {
    if (!activeTabKey.value) {
      availableSupplierCategories.value = [];
      return;
    }

    // Obtener las categorías ya configuradas para esta ciudad
    const configuredCategoryCodes = new Set(
      getSectionsCodeByTabKeyActive.value?.map((item: any) => item.code)
    );

    // Filtrar solo las categorías que NO están configuradas
    availableSupplierCategories.value = allSupplierCategories.value.filter(
      (category: any) => !configuredCategoryCodes.has(String(category.value))
    );
  };

  /**
   * Inicializa la ciudad activa y carga las categorías disponibles
   */
  const initializeLocation = () => {
    if (!activeTabKey.value) {
      selectedLocation.value = null;
      formState.locationKey = '';
      return;
    }

    // Buscar la ubicación en el store
    const location = items.value.find((item: any) => item.key === activeTabKey.value);

    if (location && location.raw) {
      // Verificar que raw es un objeto y tiene la propiedad operatingLocation
      const rawData = location.raw as Record<string, unknown>;
      if (rawData && typeof rawData === 'object' && 'operatingLocation' in rawData) {
        selectedLocation.value = rawData.operatingLocation as ProductSupplierOperatingLocation;
      }
      formState.locationKey = activeTabKey.value;
    }

    // Cargar categorías disponibles
    loadAvailableCategories();
  };

  const handleGoToConfiguration = async (): Promise<void> => {
    if (stepNumber.value === 1) {
      handleGoNext();
    } else if (stepNumber.value === 2) {
      await handleSave();
    }
  };

  const selectedSupplierCategories = computed(() => getSelectedSupplierCategories());

  const isAllCombinationsSelected = computed(() => {
    if (formState.isGeneral) {
      return true;
    }

    const categories = getSelectedSupplierCategories();

    for (const category of categories) {
      const mode = getBehaviorMode(String(category.value));
      if (!mode) {
        return false;
      }
    }

    return true;
  });

  const isNextButtonDisabled = computed(() => {
    if (stepNumber.value === 1) {
      const hasNoCategories = formState.supplierCategoryCodes.length === 0;
      return hasNoCategories;
    }

    if (stepNumber.value === 2) {
      return !isAllCombinationsSelected.value;
    }

    return false;
  });

  const getLocationDisplayName = (): string => {
    if (!selectedLocation.value) return '';
    return selectedLocation.value.state.name.toUpperCase();
  };

  const getCurrentMode = (categoryCode: string): string | null => {
    const mode = getBehaviorMode(categoryCode);
    return mode === ProductSupplierBehaviorModeEnum.SIMPLE
      ? 'simple'
      : mode === ProductSupplierBehaviorModeEnum.COMPONENT
        ? 'component'
        : null;
  };

  const handleCancelGoBack = (): void => {
    if (stepNumber.value === 1) {
      handleClose();
    } else {
      handleGoBack();
    }
  };

  const clearFormState = () => {
    formState.locationKey = '';
    formState.supplierCategoryCodes = [];
  };

  watch(
    () => formState.isGeneral,
    (newVal) => {
      if (newVal && stepNumber.value === 2) {
        const selectedCategories = getSelectedSupplierCategories();
        const mode =
          formState.type === '1'
            ? ProductSupplierBehaviorModeEnum.SIMPLE
            : ProductSupplierBehaviorModeEnum.COMPONENT;

        for (const category of selectedCategories) {
          setBehaviorMode(String(category.value), mode);
        }
      }
    }
  );

  watch(
    () => formState.type,
    (newVal) => {
      if (formState.isGeneral && stepNumber.value === 2) {
        const selectedCategories = getSelectedSupplierCategories();
        const mode =
          newVal === '1'
            ? ProductSupplierBehaviorModeEnum.SIMPLE
            : ProductSupplierBehaviorModeEnum.COMPONENT;

        for (const category of selectedCategories) {
          setBehaviorMode(String(category.value), mode);
        }
      }
    }
  );

  // Cargar datos cuando se abre el drawer
  watch(
    () => props.showDrawerForm,
    (isOpen) => {
      if (isOpen) {
        initializeLocation();
      }
    },
    { immediate: true }
  );

  return {
    isLoading,
    formState,
    supplierCategoriesWithDisabled,
    stepNumber,
    selectedLocation,
    selectedSupplierCategories,
    cancelButtonText,
    nextButtonText,
    isAllCombinationsSelected,
    isNextButtonDisabled,
    handleClose,
    isSelectedSupplierCategory,
    handleSupplierCategoryChange,
    handleGoToConfiguration,
    setBehaviorMode,
    getLocationDisplayName,
    getCurrentMode,
    handleCancelGoBack,
  };
}
