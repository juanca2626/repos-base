import { computed, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { productConfigurationResourceService } from '@/modules/negotiations/products/shared/services/productConfigurationResourceService';
import type { SupplierPlaceOperation } from '@/modules/negotiations/products/general/interfaces/resources';
import type {
  ProductSupplierOperatingLocation,
  ProductSupplierBehaviorMode,
} from '@/modules/negotiations/products/configuration/interfaces';
import { ProductSupplierBehaviorModeEnum } from '@/modules/negotiations/products/configuration/drawers/generic/enums/product-supplier-behavior-mode.enum';
import type { BaseDrawerProps } from '../../base/interfaces';
import { useStepDrawer } from './useStepDrawer';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import type {
  Configuration,
  OperatingLocation,
} from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';
interface CreateFormState {
  placeOperationIds: string[];
  supplierCategoryCodes: string[];
  isGeneral: boolean;
  type: string;
  behaviorMatrix: Record<string, ProductSupplierBehaviorMode>;
}

export function useCreateDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const router = useRouter();
  const { fetchPlaceOperations } = productConfigurationResourceService;

  const configurationStore = useConfigurationStore();

  const supportResourcesStore = useSupportResourcesStore();
  const { supplierCategories: supplierCategoriesStore } = storeToRefs(supportResourcesStore);

  const { productSupplierType } = useSelectedServiceType();

  const { stepNumber, cancelButtonText, nextButtonText, handleGoBack, resetSteps } =
    useStepDrawer(emit);

  const isLoading = ref<boolean>(false);
  const placeOperations = ref<SelectOption[]>([]);
  const placeOperationsData = ref<SupplierPlaceOperation[]>([]);

  const formState = reactive<CreateFormState>({
    placeOperationIds: [],
    supplierCategoryCodes: [],
    isGeneral: false,
    type: '1',
    behaviorMatrix: {},
  });

  const supplierCategories = computed(() =>
    supplierCategoriesStore.value.map((category) => ({
      label: category.name,
      value: category.code,
    }))
  );

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
    return supplierCategories.value.map((category) => ({
      ...category,
      disabled: isSupplierCategoryDisabled(String(category.value)),
    }));
  });

  const getSelectedPlaceOperations = () => {
    return placeOperationsData.value.filter((item) =>
      formState.placeOperationIds.includes(item.key)
    );
  };

  const getSelectedSupplierCategories = () => {
    return supplierCategories.value.filter((item) =>
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

  const buildCreateConfigurationRequest = (): Configuration => {
    const selectedPlaceOperations = getSelectedPlaceOperations();
    const selectedCategories = getSelectedSupplierCategories();
    const configurations: OperatingLocation[] = [];

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
        location: operatingLocation,
        applyGeneralBehavior: isGeneralForLocation,
        behaviorSettings,
      });
    }

    return {
      operatingLocations: configurations,
    };
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;
      const request = buildCreateConfigurationRequest();

      const { items } = await configurationStore.saveConfiguration(
        productSupplierType.value,
        props.productSupplierId,
        request
      );

      if (items.length > 0) {
        await navigateToConfiguration();
      }
    } catch (error) {
      console.error('Error saving configuration:', error);
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

  const handleGoNext = (): void => {
    stepNumber.value += 1;
  };

  const navigateToConfiguration = () => {
    const serviceId = props.productSupplierId;

    if (!serviceId) return;

    router.push({
      name: 'serviceConfiguration',
      params: { id: serviceId },
    });
  };

  const getSupportResources = async () => {
    try {
      isLoading.value = true;

      await supportResourcesStore.loadResources({
        serviceType: productSupplierType.value,
        keys: ['supplierCategories'],
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
      handleClose();
    } else {
      handleGoBack();
    }
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

  watch(
    () => props.showDrawerForm,
    async (newVal) => {
      if (newVal) {
        await getPlaceOperations();
        await getSupportResources();
      }
    }
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
