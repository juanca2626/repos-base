import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { FormInstance } from 'ant-design-vue';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useServiceDetailsFormValidation } from './useServiceDetailsFormValidation';
import { useServiceDetailsFormState } from './useServiceDetailsFormState';
import { useServiceDetailsEditMode } from './useServiceDetailsEditMode';
import { useServiceDetailsFormOptions } from './useServiceDetailsFormOptions';
import { useServiceDetailsActivities } from './useServiceDetailsActivities';
import { useServiceDetailsDataLoader } from './useServiceDetailsDataLoader';
import { useServiceDetailsRequestBuilder } from './useServiceDetailsRequestBuilder';
import { useServiceDetailsFormActions } from './useServiceDetailsFormActions';
import type { UseServiceDetailsFormProps } from '@/modules/negotiations/products/configuration/content/shared/interfaces/serviceDetails';
import { useServiceDetailsSchedule } from './useServiceDetailsSchedule';
import { calculateTotalDuration } from '@/modules/negotiations/products/configuration/content/shared/utils/duration.utils';
import { ServiceStatusForm } from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';
import {
  isAutomaticServiceType,
  isMultiPointServiceType,
} from '@/modules/negotiations/products/configuration/constants/service-type-multi-point.constant';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export const useServiceDetailsForm = (props: UseServiceDetailsFormProps) => {
  const navigationStore = useNavigationStore();
  const configurationStore = useConfigurationStore();
  const supportResourcesStore = useSupportResourcesStore();
  const genericStore = useGenericConfigurationStore();

  const { productSummary } = storeToRefs(configurationStore);

  const { profiles, pointTypes, subTypes: serviceSubTypes } = storeToRefs(supportResourcesStore);

  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const formRef = ref<FormInstance>();

  const { formState, resetFormState } = useServiceDetailsFormState();

  const { activities, addActivity, removeActivity, updateActivity, resetActivities } =
    useServiceDetailsActivities();

  const serviceDetail = computed(() => {
    if (props.currentKey && props.currentCode) {
      return genericStore.getServiceDetail(props.currentKey, props.currentCode);
    }
    return null;
  });

  const scheduleData = computed(() => {
    if (props.currentKey && props.currentCode) {
      return genericStore.getServiceDetailsSchedule(props.currentKey, props.currentCode);
    }
    return null;
  });

  const { isEditingContent, showEditModal, handleEditMode, handleConfirmEdit, handleCancelEdit } =
    useServiceDetailsEditMode(getSectionsItemActive);

  // Opciones de selects
  const {
    subtipoOptions,
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,
    getSubtipoLabel,
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
  } = useServiceDetailsFormOptions(serviceSubTypes, profiles, pointTypes);

  const totalDuration = computed(() => {
    return calculateTotalDuration(formState.value.duration, activities.value);
  });

  const isAutomaticService = computed(() =>
    isAutomaticServiceType(productSummary.value?.serviceType?.code)
  );

  const isMultiPointSelect = computed(() =>
    isMultiPointServiceType(productSummary.value?.serviceType?.code)
  );

  const shouldShowOperabilityAlert = computed(() => {
    return isAutomaticService.value;
  });

  const shouldShowReason = computed(() => {
    const status = formState.value.status;
    const hasReason = !!formState.value.reason?.trim();

    if (status === ServiceStatusForm.SUSPENDIDO || status === ServiceStatusForm.INACTIVO) {
      return true;
    }

    if (status === ServiceStatusForm.ACTIVO && hasReason) {
      return true;
    }

    return false;
  });

  // Lógica de schedules
  const {
    scheduleType,
    schedules,
    formattedOperatingRanges,
    formattedAttentionDays,
    selectSchedule,
    getApiSchedules,
    getOperabilityFlags,
  } = useServiceDetailsSchedule(scheduleData, serviceDetail);

  const { formRules, getIsFormValid, completedFields, totalFields, validateForm } =
    useServiceDetailsFormValidation(formState, formRef, subtipoOptions, scheduleData);

  // Carga de datos
  const { loadServiceDetailData } = useServiceDetailsDataLoader(
    serviceDetail,
    formState,
    resetFormState,
    serviceSubTypes,
    props
  );

  // Construcción de request
  const { buildServiceDetailsRequest } = useServiceDetailsRequestBuilder(
    serviceDetail,
    formState,
    totalDuration,
    scheduleType,
    getApiSchedules,
    getOperabilityFlags,
    getIsFormValid,
    serviceSubTypes,
    props
  );

  // Acciones de guardar
  const { isLoadingButton, handleSave, handleSaveAndAdvance } = useServiceDetailsFormActions(
    validateForm,
    buildServiceDetailsRequest,
    setCompletedItem,
    props
  );

  // Recargar datos cuando cambie la sección (currentKey) o el ítem (currentCode).
  // immediate: true ejecuta la carga al montar; no hace falta onMounted.
  watch(
    () => [props.currentKey, props.currentCode],
    () => {
      loadServiceDetailData();
      resetActivities();
    },
    { immediate: true }
  );

  return {
    // Estado del formulario
    formState,
    formRef,
    formRules,
    isLoadingButton,

    // Opciones de selects
    subtipoOptions,
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,

    // Computed values
    getIsFormValid,
    totalDuration,
    completedFields,
    totalFields,
    isAutomaticService,
    isMultiPointSelect,
    shouldShowOperabilityAlert,
    shouldShowReason,

    // Schedules
    schedules,
    formattedOperatingRanges,
    formattedAttentionDays,

    // Activities
    activities,

    // Modo edición
    isEditingContent,
    showEditModal,

    // Funciones
    handleSave,
    handleSaveAndAdvance,
    selectSchedule,
    addActivity,
    removeActivity,
    updateActivity,
    getSubtipoLabel,
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
  };
};
