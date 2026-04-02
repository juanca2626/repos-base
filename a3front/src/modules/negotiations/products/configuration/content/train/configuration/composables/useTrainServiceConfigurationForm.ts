import { ref, computed, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import {
  createCapacityConfiguration,
  updateCapacityConfiguration,
} from '@/modules/negotiations/products/configuration/content/shared/services/configuration/capacityConfiguration.service';
import type { CapacityConfigurationRequest } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';
import type { UseServiceConfigurationFormParams } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/service-configuration-form.interface';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';

export const useTrainServiceConfigurationForm = ({
  formState,
  measurementUnitOptions,
  props,
}: UseServiceConfigurationFormParams) => {
  const route = useRoute();

  const navigationStore = useNavigationStore();
  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const trainStore = useTrainConfigurationStore();

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const isLoadingButton = ref(false);
  const showEditModal = ref(false);

  const completedFields = computed(() => {
    let count = 0;
    if (formState.value.measurementUnit) count++;
    const minCapacityFilled =
      formState.value.minCapacity !== null && formState.value.minCapacity !== undefined;
    const maxCapacityFilled =
      formState.value.maxCapacity !== null && formState.value.maxCapacity !== undefined;
    if (minCapacityFilled && maxCapacityFilled) count++;
    return count;
  });

  const isFormValid = computed(() => {
    return (
      formState.value.measurementUnit !== undefined &&
      formState.value.minCapacity !== null &&
      formState.value.minCapacity !== undefined &&
      formState.value.maxCapacity !== null &&
      formState.value.maxCapacity !== undefined
    );
  });

  const getMeasurementUnitLabel = (value: any) => {
    return measurementUnitOptions.value.find((opt: any) => opt.value === value)?.label || '';
  };

  const originalHandleEditMode = () => {
    if (getSectionsItemActive.value) {
      getSectionsItemActive.value.editing = true;
    }
  };

  const handleEditMode = () => {
    showEditModal.value = true;
  };

  const handleConfirmEdit = () => {
    originalHandleEditMode();
    showEditModal.value = false;
  };

  const handleCancelEdit = () => {
    showEditModal.value = false;
  };

  const buildCapacityConfigurationRequest = (): CapacityConfigurationRequest => {
    const serviceDetailId = trainStore.getServiceDetailId(props.currentKey, props.currentCode);

    if (!serviceDetailId) {
      throw new Error('ServiceDetailId no encontrado para la ubicación y categoría especificadas');
    }

    return {
      serviceDetailId,
      unitOfMeasureCode: formState.value.measurementUnit || '',
      capacity: {
        min: formState.value.minCapacity || 0,
        max: formState.value.maxCapacity || 0,
      },
      inclusions: {
        children: formState.value.includesChildren || false,
        infants: formState.value.includesInfants || false,
      },
    };
  };

  const updateStoreAfterSave = (savedConfig: any) => {
    if (!savedConfig.groupingKeys) {
      savedConfig.groupingKeys = {
        operatingLocationKey: props.currentKey,
        trainTypeCode: props.currentCode,
      };
    }

    // Actualizar el store con la respuesta
    trainStore.updateCapacityConfiguration(savedConfig);

    if (props.currentKey && props.currentCode) {
      setCompletedItem(props.currentKey, props.currentCode, getSectionsItemActive.value?.id || '');
    }
  };

  const handleSave = async () => {
    try {
      isLoadingButton.value = true;

      const productSupplierId = route.params.id as string;
      if (!productSupplierId) {
        throw new Error('Product supplier ID no encontrado en la ruta');
      }

      const request = buildCapacityConfigurationRequest();
      const existingConfig = trainStore.getCapacityConfiguration(
        props.currentKey,
        props.currentCode
      );

      let savedConfig;
      if (existingConfig?.id) {
        // Actualizar existente
        savedConfig = await updateCapacityConfiguration(
          productSupplierId,
          existingConfig.id,
          request
        );
      } else {
        // Crear nuevo
        savedConfig = await createCapacityConfiguration(productSupplierId, request);
      }

      updateStoreAfterSave(savedConfig);
    } catch (error: any) {
      console.error('Error al guardar:', error);
      throw error;
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleSaveAndAdvance = async () => {
    try {
      await handleSave();
    } catch (error) {
      console.error('Error al guardar y avanzar:', error);
    }
  };

  const loadCapacityConfigurationData = () => {
    clearForm();

    const existingConfig = trainStore.getCapacityConfiguration(props.currentKey, props.currentCode);

    if (existingConfig) {
      formState.value.measurementUnit = existingConfig.unitOfMeasureCode;
      formState.value.minCapacity = existingConfig.capacity.min;
      formState.value.maxCapacity = existingConfig.capacity.max;
      formState.value.includesChildren = existingConfig.inclusions.children;
      formState.value.includesInfants = existingConfig.inclusions.infants;
    }
  };

  const clearForm = () => {
    formState.value.measurementUnit = undefined;
    formState.value.minCapacity = undefined;
    formState.value.maxCapacity = undefined;
    formState.value.includesChildren = false;
    formState.value.includesInfants = false;
  };

  watch(
    () => [props.currentKey, props.currentCode],
    () => {
      loadCapacityConfigurationData();
    },
    { immediate: true }
  );

  return {
    // Estado de edición
    isEditingContent,
    // isItemCompleted,

    // Estado de carga y modal
    isLoadingButton,
    showEditModal,

    // Validaciones
    completedFields,
    isFormValid,

    // Helpers
    getMeasurementUnitLabel,

    // Handlers de modo edición
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,

    // Handlers de guardado
    handleSave,
    handleSaveAndAdvance,
  };
};
