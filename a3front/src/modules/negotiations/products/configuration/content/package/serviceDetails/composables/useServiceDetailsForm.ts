import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { FormInstance } from 'ant-design-vue';
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
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export const useServiceDetailsForm = (props: UseServiceDetailsFormProps) => {
  const navigationStore = useNavigationStore();
  const configurationStore = useConfigurationStore();
  const supportResourcesStore = useSupportResourcesStore();
  const packageConfigurationStore = usePackageConfigurationStore();

  const { productSummary } = storeToRefs(configurationStore);

  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const {
    profiles: supplierProfiles,
    pointTypes: supplierPointTypes,
    subTypes: serviceSubTypes,
  } = storeToRefs(supportResourcesStore);

  const formRef = ref<FormInstance>();

  const { formState, resetFormState } = useServiceDetailsFormState();

  const { activities, addActivity, removeActivity, updateActivity } = useServiceDetailsActivities();

  const serviceDetail = computed(() => {
    if (props.currentKey && props.currentCode) {
      return packageConfigurationStore.getServiceDetail(props.currentKey, props.currentCode);
    }
    return null;
  });

  const scheduleData = computed(() => {
    if (props.currentKey && props.currentCode) {
      return packageConfigurationStore.getServiceDetailsSchedule(
        props.currentKey,
        props.currentCode
      );
    }
    return null;
  });

  // Lógica de edición/lectura
  const { isEditingContent, showEditModal, handleEditMode, handleConfirmEdit, handleCancelEdit } =
    useServiceDetailsEditMode(getSectionsItemActive);

  // Opciones de selects
  const {
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
  } = useServiceDetailsFormOptions(supplierProfiles, supplierPointTypes);

  const totalDuration = computed(() => {
    return calculateTotalDuration(formState.value.duration, activities.value);
  });

  const isAutomaticService = computed(() => {
    const serviceTypeCode = productSummary.value?.serviceType?.code;
    const automaticCodes = ['EX', 'TF', 'AS'];
    return serviceTypeCode ? automaticCodes.includes(serviceTypeCode) : false;
  });

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

  // Validación del formulario
  const { formRules, getIsFormValid, completedFields, totalFields, validateForm } =
    useServiceDetailsFormValidation(formState, formRef, scheduleData);

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

  // Carga de datos
  const { loadServiceDetailData } = useServiceDetailsDataLoader(
    serviceDetail,
    formState,
    resetFormState,
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

  const { isLoadingButton, handleSave, handleSaveAndAdvance } = useServiceDetailsFormActions(
    validateForm,
    buildServiceDetailsRequest,
    setCompletedItem,
    {
      get currentKey() {
        return props.currentKey ?? '';
      },
      get currentCode() {
        return props.currentCode ?? '';
      },
      get currentItemId() {
        return getSectionsItemActive.value?.id ?? '';
      },
    }
  );

  watch(
    () => [props.currentKey, props.currentCode],
    () => {
      loadServiceDetailData();
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
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
  };
};
